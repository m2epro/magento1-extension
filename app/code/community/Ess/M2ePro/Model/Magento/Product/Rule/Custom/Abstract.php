<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    // #################################################

    abstract public function getAttributeCode();

    abstract public function getLabel();

    abstract public function getValueByProductInstance(Mage_Catalog_Model_Product $product);

    // #################################################

    public function getInputType()
    {
        return 'string';
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function getOptions()
    {
        return array();
    }

    // #################################################
}