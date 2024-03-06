<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Variation_Updater
    extends Ess_M2ePro_Model_Listing_Product_Variation_Updater
{
    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     */
    public function process(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->getMagentoProduct()->isProductWithVariations()) {
            return;
        }

        $rawMagentoVariations = $listingProduct->getMagentoProduct()
                                               ->getVariationInstance()
                                               ->getVariationsTypeStandard();

        if (empty($rawMagentoVariations['set']) || !is_array($rawMagentoVariations['set']) ||
            empty($rawMagentoVariations['variations']) || !is_array($rawMagentoVariations['variations'])) {
            $rawMagentoVariations = array(
                'set'        => array(),
                'variations' => array()
            );
        }

        $rawMagentoVariations = Mage::helper('M2ePro/Component_Ebay')
                                            ->prepareOptionsForVariations($rawMagentoVariations);

        $magentoVariations = $this->prepareMagentoVariations($rawMagentoVariations);

        if (!$listingProduct->getMagentoProduct()->isSimpleType() &&
            !$listingProduct->getMagentoProduct()->isDownloadableType()
        ) {
            $this->inspectAndFixProductOptionsIds($listingProduct, $magentoVariations);
        }

        $currentVariations = $this->prepareCurrentVariations($listingProduct->getVariations(true));

        $addedVariations = $this->getAddedVariations($magentoVariations, $currentVariations);
        $deletedVariations = $this->getDeletedVariations($magentoVariations, $currentVariations);

        $this->addNewVariations($listingProduct, $addedVariations);
        $this->markAsDeletedVariations($deletedVariations);

        $this->saveVariationsData($listingProduct, $rawMagentoVariations);
    }

    //########################################

    protected function saveVariationsData(Ess_M2ePro_Model_Listing_Product $listingProduct, $variationsData)
    {
        $additionalData = $listingProduct->getData('additional_data');
        $additionalData = $additionalData === null ? array()
                                                   : (array)Mage::helper('M2ePro')->jsonDecode($additionalData);

        if (isset($variationsData['set'])) {
            $additionalData['variations_sets'] = $variationsData['set'];
        }

        if (isset($variationsData['additional']['attributes'])) {
            $additionalData['configurable_attributes'] = $variationsData['additional']['attributes'];
        }

        $listingProduct->setData('additional_data', Mage::helper('M2ePro')->jsonEncode($additionalData))
                       ->save();
    }

    //########################################

    protected function inspectAndFixProductOptionsIds(
        Ess_M2ePro_Model_Listing_Product $listingProduct,
        $magentoVariations
    ) {
        /** @var Ess_M2ePro_Model_Listing_Product_Variation[] $listingProductVariations */
        $listingProductVariations = $listingProduct->getVariations(true);

        if (empty($listingProductVariations)) {
            return;
        }

        foreach ($listingProductVariations as $listingProductVariation) {
            $listingProductVariationOptions = $listingProductVariation->getOptions();

            foreach ($magentoVariations as $magentoVariation) {
                $magentoVariationOptions = $magentoVariation['options'];

                if (!$this->isEqualVariations($magentoVariationOptions, $listingProductVariationOptions)) {
                    continue;
                }

                foreach ($listingProductVariationOptions as $listingProductVariationOption) {
                    foreach ($magentoVariationOptions as $magentoVariationOption) {
                        if ($listingProductVariationOption['attribute'] !== $magentoVariationOption['attribute'] ||
                            $listingProductVariationOption['option'] !== $magentoVariationOption['option']) {
                            continue;
                        }

                        if ($listingProductVariationOption['product_id'] == $magentoVariationOption['product_id']) {
                            continue;
                        }

                        $listingProductVariationOption['product_id'] = $magentoVariationOption['product_id'];

                        Mage::helper('M2ePro/Component_Ebay')->getModel('Listing_Product_Variation_Option')
                            ->setData($listingProductVariationOption)->save();
                    }
                }
            }
        }
    }

    protected function getAddedVariations($magentoVariations, $currentVariations)
    {
        $result = array();

        foreach ($magentoVariations as $mVariation) {
            $isExistVariation = false;
            $cVariationExist = null;

            foreach ($currentVariations as $cVariation) {
                if ($this->isEqualVariations($mVariation['options'], $cVariation['options'])) {
                    $isExistVariation = true;
                    $cVariationExist = $cVariation;
                    break;
                }
            }

            if (!$isExistVariation) {
                $result[] = $mVariation;
            } else {
                if ((bool)$cVariationExist['variation']['delete']) {
                    $result[] = $cVariationExist;
                }
            }
        }

        return $result;
    }

    protected function getDeletedVariations($magentoVariations, $currentVariations)
    {
        $result = array();
        $foundedVariations = array();

        foreach ($currentVariations as $cVariation) {
            if ((bool)$cVariation['variation']['delete']) {
                continue;
            }

            $isExistVariation = false;
            $variationHash = $this->getVariationHash($cVariation);

            foreach ($magentoVariations as $mVariation) {
                if ($this->isEqualVariations($mVariation['options'], $cVariation['options'])) {
                    // so it is a duplicated variation. have to be deleted
                    if (in_array($variationHash, $foundedVariations)) {
                        $result[] = $cVariation;
                        continue 2;
                    }

                    $foundedVariations[] = $variationHash;
                    $isExistVariation = true;
                    break;
                }
            }

            if (!$isExistVariation) {
                $result[] = $cVariation;
            }
        }

        return $result;
    }

    // ---------------------------------------

    protected function addNewVariations(Ess_M2ePro_Model_Listing_Product $listingProduct, $addedVariations)
    {
        foreach ($addedVariations as $aVariation) {
            if (isset($aVariation['variation']['id'])) {
                $status = $aVariation['variation']['status'];

                $dataForUpdate = array(
                    'add'    => $status == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED ? 1 : 0,
                    'delete' => 0
                );

                Mage::helper('M2ePro/Component')->getComponentObject(
                    Ess_M2ePro_Helper_Component_Ebay::NICK,
                    'Listing_Product_Variation',
                    $aVariation['variation']['id']
                )->addData($dataForUpdate)->save();

                continue;
            }

            $dataForAdd = array(
                'listing_product_id' => $listingProduct->getId(),
                'add' => 1,
                'delete' => 0,
                'status' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED
            );

            $newVariationId = Mage::helper('M2ePro/Component')->getComponentModel(
                Ess_M2ePro_Helper_Component_Ebay::NICK, 'Listing_Product_Variation'
            )->addData($dataForAdd)->save()->getId();

            foreach ($aVariation['options'] as $aOption) {
                $dataForAdd = array(
                    'listing_product_variation_id' => $newVariationId,
                    'product_id' => $aOption['product_id'],
                    'product_type' => $aOption['product_type'],
                    'attribute' => $aOption['attribute'],
                    'option' => $aOption['option']
                );

                Mage::helper('M2ePro/Component')->getComponentModel(
                    Ess_M2ePro_Helper_Component_Ebay::NICK, 'Listing_Product_Variation_Option'
                )->addData($dataForAdd)->save();
            }
        }
    }

    protected function markAsDeletedVariations($deletedVariations)
    {
        foreach ($deletedVariations as $dVariation) {
            if ($dVariation['variation']['status'] == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED ||
                $dVariation['variation']['status'] == Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE) {
                Mage::helper('M2ePro/Component')->getComponentObject(
                    Ess_M2ePro_Helper_Component_Ebay::NICK,
                    'Listing_Product_Variation',
                    $dVariation['variation']['id']
                )->deleteInstance();
            } else {
                $dataForUpdate = array(
                    'add' => 0,
                    'delete' => 1
                );

                Mage::helper('M2ePro/Component')->getComponentObject(
                    Ess_M2ePro_Helper_Component_Ebay::NICK,
                    'Listing_Product_Variation',
                    $dVariation['variation']['id']
                )->addData($dataForUpdate)->save();
            }
        }
    }

    //########################################

    protected function prepareMagentoVariations($variations)
    {
        $result = array();

        if (isset($variations['variations'])) {
            $variations = $variations['variations'];
        }

        foreach ($variations as $variation) {
            $result[] = array(
                'variation' => array(),
                'options' => $variation
            );
        }

        return $result;
    }

    protected function prepareCurrentVariations($variations)
    {
        $result = array();

        foreach ($variations as $variation) {

            /** @var Ess_M2ePro_Model_Listing_Product_Variation $variation */

            $temp = array(
                'variation' => $variation->getData(),
                'options' => array()
            );

            foreach ($variation->getOptions(false) as $option) {
                $temp['options'][] = $option;
            }

            $result[] = $temp;
        }

        return $result;
    }

    // ---------------------------------------

    protected function isEqualVariations($magentoVariation, $currentVariation)
    {
        if (count($magentoVariation) != count($currentVariation)) {
            return false;
        }

        foreach ($magentoVariation as $mOption) {
            $haveOption = false;

            foreach ($currentVariation as $cOption) {
                if (trim($mOption['attribute']) == trim($cOption['attribute']) &&
                    trim($mOption['option']) == trim($cOption['option'])) {
                    $haveOption = true;
                    break;
                }
            }

            if (!$haveOption) {
                return false;
            }
        }

        return true;
    }

    protected function getVariationHash($variation)
    {
        $hash = array();

        foreach ($variation['options'] as $option) {
            $hash[] = trim($option['attribute']) .'-'. trim($option['option']);
        }

        return implode('##', $hash);
    }

    //########################################
}
