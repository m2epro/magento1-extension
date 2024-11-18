<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Amazon_Dictionary_Marketplace
    extends Ess_M2ePro_Model_Resource_Abstract
{
    //########################################

    const COLUMN_ID = 'id';
    const COLUMN_MARKETPLACE_ID = 'marketplace_id';
    const COLUMN_PRODUCT_TYPES = 'product_types';

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Dictionary_Marketplace', self::COLUMN_ID);
    }

    //########################################
}
