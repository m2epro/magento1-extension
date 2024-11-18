<?php

class Ess_M2ePro_Model_Resource_Amazon_Template_Shipping
    extends Ess_M2ePro_Model_Resource_Abstract
{
    const COLUMN_ID = 'id';
    const COLUMN_TITLE = 'title';
    const COLUMN_ACCOUNT_ID = 'account_id';
    const COLUMN_MARKETPLACE_ID = 'marketplace_id';
    const COLUMN_TEMPLATE_ID = 'template_id';
    const COLUMN_UPDATE_DATE = 'update_date';
    const COLUMN_CREATE_DATE = 'create_date';

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Template_Shipping', 'id');
    }
}
