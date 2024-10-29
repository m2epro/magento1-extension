<?php

class Ess_M2ePro_Model_Resource_Walmart_ProductType
    extends Ess_M2ePro_Model_Resource_Abstract
{
    const COLUMN_ID = 'id';
    const COLUMN_TITLE = 'title';
    const COLUMN_DICTIONARY_PRODUCT_TYPE_ID = 'dictionary_product_type_id';
    const COLUMN_ATTRIBUTES_SETTINGS = 'attributes_settings';
    const COLUMN_UPDATE_DATE = 'update_date';
    const COLUMN_CREATE_DATE = 'create_date';

    public function _construct()
    {
        $this->_init('M2ePro/Walmart_ProductType', self::COLUMN_ID);
    }
}
