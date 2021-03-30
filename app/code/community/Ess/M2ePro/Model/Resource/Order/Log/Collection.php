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

    public function getLogsByDescription(
        $text,
        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
        $lastFromDate = null
    ) {
        $this->addFieldToFilter('main_table.initiator', $initiator);
        $this->addFieldToFilter('main_table.description', array('like' => '%' . $text . '%'));

        if (!empty($lastFromDate)) {
            $lastFromDate = new DateTime($lastFromDate, new DateTimeZone('UTC'));
            $this->addFieldToFilter('main_table.create_date', array('gteq' => $lastFromDate->format('Y-m-d')));
        }

        return $this;
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
