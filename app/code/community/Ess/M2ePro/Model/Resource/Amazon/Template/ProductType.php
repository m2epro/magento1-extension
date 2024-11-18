<?php

class Ess_M2ePro_Model_Resource_Amazon_Template_ProductType
    extends Ess_M2ePro_Model_Resource_Abstract
{
    const COLUMN_ID = 'id';
    const COLUMN_TITLE = 'title';
    const COLUMN_VIEW_MODE = 'view_mode';
    const COLUMN_DICTIONARY_PRODUCT_TYPE_ID = 'dictionary_product_type_id';
    const COLUMN_SETTINGS = 'settings';
    const COLUMN_UPDATED_AT = 'updated_at';
    const COLUMN_CREATED_AT = 'created_at';
    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Template_ProductType', self::COLUMN_ID);
    }
}