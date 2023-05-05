<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Order_Log_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order_Log');
    }

    //########################################

    /**
     * GroupBy fix
     */
    public function getSelectCountSql()
    {
        $sql = parent::getSelectCountSql();
        $sql->reset(Zend_Db_Select::GROUP);
        return $sql;
    }

    //########################################
}
