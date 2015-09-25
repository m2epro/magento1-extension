<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Magento_Product_Rule_Custom_AmazonIsAfnChanel
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    // #################################################

    public function getAttributeCode()
    {
        return 'amazon_is_afn_chanel';
    }

    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('Fulfillment');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        return $product->getData('is_afn_channel');
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
                'value' => 0,
                'label' => Mage::helper('M2ePro')->__('Merchant'),
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('M2ePro')->__('Amazon'),
            ),
        );
    }

    // #################################################
}