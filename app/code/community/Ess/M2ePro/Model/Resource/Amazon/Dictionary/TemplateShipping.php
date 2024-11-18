<?php

class Ess_M2ePro_Model_Resource_Amazon_Dictionary_TemplateShipping
    extends Ess_M2ePro_Model_Resource_Abstract
{
    const COLUMN_ID = 'id';
    const COLUMN_ACCOUNT_ID = 'account_id';
    const COLUMN_TEMPLATE_ID = 'template_id';
    const COLUMN_TITLE = 'title';

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Dictionary_TemplateShipping', self::COLUMN_ID);
    }
}