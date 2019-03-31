<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Request_Pending_Partial
    extends Ess_M2ePro_Model_Mysql4_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Request_Pending_Partial', 'id');
    }

    // ########################################

    public function getResultData(Ess_M2ePro_Model_Request_Pending_Partial $requestPendingPartial, $partNumber)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('m2epro_request_pending_partial_data'),
                'data'
            )
            ->where('request_pending_partial_id = ?', $requestPendingPartial->getId())
            ->where('part_number = ?', $partNumber);

        $resultData = $this->_getReadAdapter()->fetchCol($select);
        $resultData = reset($resultData);

        return !empty($resultData) ? Mage::helper('M2ePro')->jsonDecode($resultData) : NULL;
    }

    public function addResultData(Ess_M2ePro_Model_Request_Pending_Partial $requestPendingPartial,
                                  $partNumber,
                                  array $data)
    {
        $this->_getWriteAdapter()->insert(
            Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_request_pending_partial_data'),
            array(
                'request_pending_partial_id' => $requestPendingPartial->getId(),
                'part_number' => $partNumber,
                'data'    => Mage::helper('M2ePro')->jsonEncode($data),
            )
        );
    }

    public function deleteResultData(Ess_M2ePro_Model_Request_Pending_Partial $requestPendingPartial)
    {
        $this->_getWriteAdapter()->delete(
            Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_request_pending_partial_data'),
            array('request_pending_partial_id = ?' => $requestPendingPartial->getId())
        );
    }

    // ########################################
}