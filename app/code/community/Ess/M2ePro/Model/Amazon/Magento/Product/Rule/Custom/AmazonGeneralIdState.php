<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Magento_Product_Rule_Custom_AmazonGeneralIdState
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    // #################################################

    public function getAttributeCode()
    {
        return 'amazon_general_id_state';
    }

    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('ASIN/ISBN Status');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        $generalId = $product->getData('general_id');

        if (!empty($generalId)) {
            return Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_STATE_SET;
        }

        if ($product->getData('is_general_id_owner') == 1) {
            return Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_STATE_READY_FOR_NEW_ASIN;
        }

        $searchStatusActionRequired = Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_ACTION_REQUIRED;
        $searchStatusNotFound = Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_NOT_FOUND;

        if ($product->getData('search_settings_status') == $searchStatusActionRequired ||
            $product->getData('search_settings_status') == $searchStatusNotFound) {
            return Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_STATE_ACTION_REQUIRED;
        }

        return Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_STATE_NOT_SET;
    }

    public function getInputType()
    {
        return 'select';
    }

    public function getValueElementType()
    {
        return 'select';
    }

    public function getOptions()
    {
        return array(
            array(
                'value' => Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_STATE_SET,
                'label' => Mage::helper('M2ePro')->__('Set'),
            ),
            array(
                'value' => Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_STATE_NOT_SET,
                'label' => Mage::helper('M2ePro')->__('Not Set'),
            ),
            array(
                'value' => Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_STATE_ACTION_REQUIRED,
                'label' => Mage::helper('M2ePro')->__('Action Required'),
            ),
            array(
                'value' => Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_STATE_READY_FOR_NEW_ASIN,
                'label' => Mage::helper('M2ePro')->__('Ready for New ASIN/ISBN Creation'),
            ),
        );
    }

    // #################################################
}