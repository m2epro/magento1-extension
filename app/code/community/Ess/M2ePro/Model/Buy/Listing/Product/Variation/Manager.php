<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Variation_Manager
{
    //########################################

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProduct = NULL;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->listingProduct = $listingProduct;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        return $this->listingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Listing_Product
     */
    public function getBuyListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    //########################################

    /**
     * @return bool
     */
    public function isVariationProduct()
    {
        return (bool)(int)$this->getBuyListingProduct()->getData('is_variation_product');
    }

    /**
     * @return bool
     */
    public function isVariationProductMatched()
    {
        return (bool)(int)$this->getBuyListingProduct()->getData('is_variation_product_matched');
    }

    //########################################

    /**
     * @return bool
     */
    public function isActualProductAttributes()
    {
        $productAttributes = array_map('strtolower', array_keys($this->getProductOptions()));
        $magentoAttributes = array_map('strtolower', $this->getCurrentMagentoAttributes());

        sort($productAttributes);
        sort($magentoAttributes);

        return $productAttributes == $magentoAttributes;
    }

    /**
     * @return bool
     */
    public function isActualProductVariation()
    {
        $currentOptions = $this->getProductOptions();

        $currentOptions = array_change_key_case(array_map('strtolower',$currentOptions), CASE_LOWER);
        $magentoVariations = $this->getListingProduct()->getMagentoProduct()
            ->getVariationInstance()
            ->getVariationsTypeStandard();

        foreach ($magentoVariations['variations'] as $magentoVariation) {

            $magentoOptions = array();

            foreach ($magentoVariation as $magentoOption) {
                $magentoOptions[strtolower($magentoOption['attribute'])] = strtolower($magentoOption['option']);
            }

            if (empty($magentoOptions)) {
                continue;
            }

            if ($currentOptions == $magentoOptions) {
                return true;
            }
        }

        return false;
    }

    // ---------------------------------------

    /**
     * @param array $variation
     */
    public function setProductVariation(array $variation)
    {
        $this->unsetProductVariation();

        $this->createStructure($variation);

        $options = array();
        foreach ($variation as $option) {
            $options[$option['attribute']] = $option['option'];
        }

        $this->setProductOptions($options, false);

        $this->getListingProduct()->setData('is_variation_product_matched',1);

        if ($this->getListingProduct()->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            $this->createChannelItem($options);
        }

        $this->getListingProduct()->save();
    }

    public function resetProductVariation()
    {
        if ($this->isVariationProductMatched()) {
            $this->unsetProductVariation();
        } else {
            $this->resetProductOptions();
        }
    }

    public function unsetProductVariation()
    {
        if (!$this->isVariationProductMatched()) {
            return;
        }

        $this->removeStructure();
        $this->resetProductOptions(false);

        $this->getListingProduct()->setData('is_variation_product_matched', 0);

        if ($this->getListingProduct()->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            $this->removeChannelItems();
        }

        $this->getListingProduct()->save();
    }

    //########################################

    public function getProductOptions()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        return !empty($additionalData['variation_product_options']) ?
                      $additionalData['variation_product_options'] : NULL;
    }

    // ---------------------------------------

    private function setProductOptions(array $options, $save = true)
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        $additionalData['variation_product_options'] = $options;

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
        $save && $this->getListingProduct()->save();
    }

    private function resetProductOptions($save = true)
    {
        $options = array_fill_keys($this->getCurrentMagentoAttributes(), null);
        $this->setProductOptions($options,$save);
    }

    //########################################

    public function clearVariationData()
    {
        $this->unsetProductVariation();

        $additionalData = $this->getListingProduct()->getAdditionalData();
        unset($additionalData['variation_product_options']);
        $this->getListingProduct()->setSettings('additional_data', $additionalData);

        $this->getListingProduct()->save();
    }

    //########################################

    private function removeStructure()
    {
        foreach ($this->getListingProduct()->getVariations(true) as $variation) {
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation->deleteInstance();
        }
    }

    private function createStructure(array $variation)
    {
        $variationId = Mage::helper('M2ePro/Component_Buy')
                                ->getModel('Listing_Product_Variation')
                                ->addData(array(
                                    'listing_product_id' => $this->getListingProduct()->getId()
                                ))->save()->getId();

        foreach ($variation as $option) {

            $tempData = array(
                'listing_product_variation_id' => $variationId,
                'product_id' => $option['product_id'],
                'product_type' => $option['product_type'],
                'attribute' => $option['attribute'],
                'option' => $option['option']
            );

            Mage::helper('M2ePro/Component_Buy')->getModel('Listing_Product_Variation_Option')
                                                ->addData($tempData)->save();
        }
    }

    // ---------------------------------------

    private function removeChannelItems()
    {
        $items = Mage::getModel('M2ePro/Buy_Item')->getCollection()
                    ->addFieldToFilter('account_id',$this->getListingProduct()->getListing()->getAccountId())
                    ->addFieldToFilter('marketplace_id',$this->getListingProduct()->getListing()->getMarketplaceId())
                    ->addFieldToFilter('sku',$this->getBuyListingProduct()->getSku())
                    ->addFieldToFilter('product_id',$this->getListingProduct()->getProductId())
                    ->addFieldToFilter('store_id',$this->getListingProduct()->getListing()->getStoreId())
                    ->getItems();

        foreach ($items as $item) {
            /* @var $item Ess_M2ePro_Model_Buy_Item */
            $item->deleteInstance();
        }
    }

    private function createChannelItem(array $options)
    {
        $data = array(
            'account_id' => (int)$this->getListingProduct()->getListing()->getAccountId(),
            'marketplace_id' => (int)$this->getListingProduct()->getListing()->getMarketplaceId(),
            'sku' => $this->getBuyListingProduct()->getSku(),
            'product_id' => (int)$this->getListingProduct()->getProductId(),
            'store_id' => (int)$this->getListingProduct()->getListing()->getStoreId(),
            'variation_product_options' => json_encode($options),
        );

        Mage::getModel('M2ePro/Buy_Item')->setData($data)->save();
    }

    //########################################

    private function getCurrentMagentoAttributes()
    {
        $magentoVariations = $this->getListingProduct()->getMagentoProduct()
            ->getVariationInstance()
            ->getVariationsTypeStandard();

        return array_keys($magentoVariations['set']);
    }

    //########################################
}