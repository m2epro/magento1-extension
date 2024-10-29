<?php

class Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType
    extends Ess_M2ePro_Model_Resource_Abstract
{
    const COLUMN_ID = 'id';
    const COLUMN_MARKETPLACE_ID = 'marketplace_id';
    const COLUMN_NICK = 'nick';
    const COLUMN_TITLE = 'title';
    const COLUMN_ATTRIBUTES = 'attributes';
    const COLUMN_VARIATION_ATTRIBUTES = 'variation_attributes';
    const COLUMN_INVALID = 'invalid';
    
    public function _construct()
    {
        $this->_init('M2ePro/Walmart_Dictionary_ProductType', self::COLUMN_ID);
    }
}