<?php

class Ess_M2ePro_Model_Resource_Amazon_ProductType_AttributeMapping
    extends Ess_M2ePro_Model_Resource_Abstract
{
    const COLUMN_ID = 'id';
    const COLUMN_PRODUCT_TYPE_ATTRIBUTE_CODE = 'product_type_attribute_code';
    const COLUMN_PRODUCT_TYPE_ATTRIBUTE_NAME = 'product_type_attribute_name';
    const COLUMN_MAGENTO_ATTRIBUTE_CODE = 'magento_attribute_code';

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_ProductType_AttributeMapping', self::COLUMN_ID);
    }
}
