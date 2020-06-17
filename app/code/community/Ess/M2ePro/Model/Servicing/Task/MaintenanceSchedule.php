<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Servicing_Task_MaintenanceSchedule extends Ess_M2ePro_Model_Servicing_Task
{
    //########################################

    /**
     * @return string
     */
    public function getPublicNick()
    {
        return 'maintenance_schedule';
    }

    //########################################

    /**
     * @return array
     */
    public function getRequestData()
    {
        return array();
    }

    public function processResponseData(array $data)
    {
        $dateEnabledFrom = false;
        $dateEnabledTo = false;

        if (!empty($data['date_enabled_from']) && !empty($data['date_enabled_to'])) {
            $dateEnabledFrom = $data['date_enabled_from'];
            $dateEnabledTo = $data['date_enabled_to'];
        }

        $helper = Mage::helper('M2ePro/Server_Maintenance');

        if ($helper->getDateEnabledFrom() != $dateEnabledFrom) {
            $helper->setDateEnabledFrom($dateEnabledFrom);
        }

        if ($helper->getDateEnabledTo() != $dateEnabledTo) {
            $helper->setDateEnabledTo($dateEnabledTo);
        }
    }

    //########################################
}
