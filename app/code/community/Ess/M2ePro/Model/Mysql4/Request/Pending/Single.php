<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Request_Pending_Single
    extends Ess_M2ePro_Model_Mysql4_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Request_Pending_Single', 'id');
    }

    // ########################################

    public function getComponentsInProgress()
    {
        $select = $this->_getReadAdapter()
            ->select()
            ->from($this->getMainTable(), new Zend_Db_Expr('DISTINCT `component`'))
            ->where('is_completed = ?', 0)
            ->distinct(true);

        return $this->_getReadAdapter()->fetchCol($select);
    }

    // ########################################
}