<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Amazon_Order_Action_Processing
    extends Ess_M2ePro_Model_Resource_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Order_Action_Processing', 'id');
    }

    //########################################

    public function markAsInProgress(array $actionIds, Ess_M2ePro_Model_Request_Pending_Single $requestPendingSingle)
    {
        $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array(
                'request_pending_single_id' => $requestPendingSingle->getId(),
            ),
            array('id IN (?)' => $actionIds)
        );
    }

    public function getUniqueRequestPendingSingleIds()
    {
        $select = $this->_getReadAdapter()
            ->select()
            ->from($this->getMainTable(), new Zend_Db_Expr('DISTINCT `request_pending_single_id`'))
            ->where('request_pending_single_id IS NOT NULL')
            ->distinct(true);

        return $this->_getReadAdapter()->fetchCol($select);
    }

    //########################################
}
