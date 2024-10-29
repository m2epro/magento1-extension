<?php

class Ess_M2ePro_Model_Resource_Walmart_Dictionary_Marketplace
    extends Ess_M2ePro_Model_Resource_Abstract
{
    const COLUMN_ID = 'id';
    const COLUMN_MARKETPLACE_ID = 'marketplace_id';
    const COLUMN_CLIENT_DETAILS_LAST_UPDATE_DATE = 'client_details_last_update_date';
    const COLUMN_SERVER_DETAILS_LAST_UPDATE_DATE = 'server_details_last_update_date';
    const COLUMN_PRODUCT_TYPES = 'product_types';

    public function _construct()
    {
        $this->_init('M2ePro/Walmart_Dictionary_Marketplace', self::COLUMN_ID);
    }
}
