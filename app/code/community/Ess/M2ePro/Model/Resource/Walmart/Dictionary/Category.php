<?php

class Ess_M2ePro_Model_Resource_Walmart_Dictionary_Category
    extends Ess_M2ePro_Model_Resource_Abstract
{
    const COLUMN_ID = 'id';
    const COLUMN_MARKETPLACE_ID = 'marketplace_id';
    const COLUMN_CATEGORY_ID = 'category_id';
    const COLUMN_PARENT_CATEGORY_ID = 'parent_category_id';
    const COLUMN_TITLE = 'title';
    const COLUMN_IS_LEAF = 'is_leaf';
    const COLUMN_PRODUCT_TYPE_NICK = 'product_type_nick';
    const COLUMN_PRODUCT_TYPE_TITLE = 'product_type_title';

    public function _construct()
    {
        $this->_init('M2ePro/Walmart_Dictionary_Category', self::COLUMN_ID);
    }
}
