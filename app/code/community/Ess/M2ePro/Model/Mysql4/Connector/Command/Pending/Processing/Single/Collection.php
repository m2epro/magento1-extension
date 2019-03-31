<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Connector_Command_Pending_Processing_Single_Collection
    extends Ess_M2ePro_Model_Mysql4_Collection_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Connector_Command_Pending_Processing_Single');
    }

    // ########################################

    public function setCompletedRequestPendingSingleFilter()
    {
        $this->getSelect()->joinLeft(
            array('mpsr' => Mage::getResourceModel('M2ePro/Request_Pending_Single')->getMainTable()),
            'main_table.request_pending_single_id = mpsr.id', array()
        );

        $this->addFieldToFilter('mpsr.is_completed', 1);
    }

    public function setNotCompletedProcessingFilter()
    {
        $this->getSelect()->joinLeft(
            array('mp' => Mage::getResourceModel('M2ePro/Processing')->getMainTable()),
            'main_table.processing_id = mp.id', array()
        );

        $this->addFieldToFilter('mp.is_completed', 0);
    }

    // ########################################
}