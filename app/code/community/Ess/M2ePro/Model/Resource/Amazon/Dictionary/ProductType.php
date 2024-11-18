<?php

class Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType
    extends Ess_M2ePro_Model_Resource_Abstract
{
    const COLUMN_ID = 'id';
    const COLUMN_MARKETPLACE_ID = 'marketplace_id';
    const COLUMN_NICK = 'nick';
    const COLUMN_TITLE = 'title';
    const COLUMN_SCHEME = 'scheme';
    const COLUMN_VARIATION_THEMES = 'variation_themes';
    const COLUMN_ATTRIBUTES_GROUP = 'attributes_groups';
    const COLUMN_CLIENT_DETAILS_LAST_UPDATE_DATE = 'client_details_last_update_date';
    const COLUMN_SERVER_DETAILS_LAST_UPDATE_DATE = 'server_details_last_update_date';
    const COLUMN_INVALID = 'invalid';

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Dictionary_ProductType', self::COLUMN_ID);
    }
}