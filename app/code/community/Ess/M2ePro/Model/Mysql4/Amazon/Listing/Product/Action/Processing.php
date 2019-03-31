<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Amazon_Listing_Product_Action_Processing
    extends Ess_M2ePro_Model_Mysql4_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Listing_Product_Action_Processing', 'id');
    }

    // ########################################

    public function getFullyPreparedGroupHashes()
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('lpap' => $this->getMainTable()), 'group_hash')
            ->joinLeft(
                array('temp' => $this->getMainTable()),
                'lpap.id = temp.id AND temp.is_prepared = 0',
                array()
            )
            ->group('lpap.group_hash')
            ->having(new Zend_Db_Expr('count(temp.id) = 0'));

        return $this->_getReadAdapter()->fetchCol($select);
    }

    // ########################################

    public function markAsInProgress(array $itemIds, Ess_M2ePro_Model_Request_Pending_Single $requestPendingSingle)
    {
        $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array(
                'request_pending_single_id' => $requestPendingSingle->getId(),
            ),
            array('id IN (?)' => $itemIds)
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

    // ########################################
}