<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    //########################################

    abstract public function getAttributeCode();

    abstract public function getLabel();

    abstract public function getValueByProductInstance(Mage_Catalog_Model_Product $product);

    //########################################

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'string';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return array();
    }

    //########################################
}