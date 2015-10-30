<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Request_NewProduct
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Request_Abstract
{
    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        if (!$this->getConfigurator()->isNewProductAllowed()) {
            return array();
        }

        $newProductTemplate = $this->getBuyListingProduct()->getNewProductTemplate();
        if (empty($newProductTemplate) || !$newProductTemplate->getId()) {
            return array();
        }

        $data = array();

        // ---------------------------------------
        $coreSource = $newProductTemplate->getCoreTemplate()->getSource(
            $this->getBuyListingProduct()->getActualMagentoProduct()
        );

        $msrpPrice = $this->getBuyListingProduct()->getPrice();

        $coreData = array(
            'seller_sku'        => $coreSource->getSellerSku(),
            'gtin'              => $coreSource->getGtin(),
            'isbn'              => $coreSource->getIsbn(),
            'mfg_name'          => $coreSource->getMfgName(),
            'mfg_part_number'   => $coreSource->getMfgPartNumber(),
            'product_set_id'    => $coreSource->getProductSetId(),
            'weight'            => $coreSource->getWeight(),
            'listing_price'     => $msrpPrice,
            'msrp'              => $msrpPrice,

            'category_id'       => $newProductTemplate->getCategoryId(),
        );

        $this->searchNotFoundAttributes();
        $coreData['title'] = $coreSource->getTitle();
        $this->processNotFoundAttributes('Title');

        $this->searchNotFoundAttributes();
        $coreData['description'] = $coreSource->getDescription();
        $this->processNotFoundAttributes('Description');

        $this->searchNotFoundAttributes();
        $coreData['main_image'] = $coreSource->getMainImage();
        $this->processNotFoundAttributes('Main Image');

        $this->searchNotFoundAttributes();
        $coreData['additional_messages'] = $coreSource->getAdditionalImages();
        $this->processNotFoundAttributes('Additional Messages');

        $this->searchNotFoundAttributes();
        $coreData['keywords'] = $coreSource->getKeywords();
        $this->processNotFoundAttributes('Keywords');

        $this->searchNotFoundAttributes();
        $coreData['features'] = $coreSource->getFeatures();
        $this->processNotFoundAttributes('Features');

        if (Ess_M2ePro_Model_Buy_Template_NewProduct::isAllowedUpcExemption() && is_null($coreData['gtin'])) {
            unset($coreData['gtin']);
            $coreData['upc_exemption'] = '1';
        }
        $data['core'] = $coreData;
        // ---------------------------------------

        // ---------------------------------------
        $attributesData = array();

        $this->searchNotFoundAttributes();

        foreach ($newProductTemplate->getAttributes(true) as $attribute) {

            $tempValue = $attribute->getSource(
                $this->getBuyListingProduct()->getActualMagentoProduct()
            )->getValue();

            $attributesData = array_merge($attributesData, $tempValue);
        }

        $this->processNotFoundAttributes('Attributes');

        $data['attributes'] = $attributesData;
        // ---------------------------------------

        return $data;
    }

    //########################################
}