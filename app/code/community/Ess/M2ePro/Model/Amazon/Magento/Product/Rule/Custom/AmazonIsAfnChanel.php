<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Magento_Product_Rule_Custom_AmazonIsAfnChanel
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'amazon_is_afn_chanel';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('Fulfillment');
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return int
     */
    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        return $product->getData('is_afn_channel');
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'select';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * @return array
     */
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

    //########################################
}