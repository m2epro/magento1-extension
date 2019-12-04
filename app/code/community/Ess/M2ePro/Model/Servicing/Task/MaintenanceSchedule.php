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
        if (empty($data['date_enabled_from']) ||
            empty($data['date_enabled_to']) ||
            empty($data['date_real_from']) ||
            empty($data['date_real_to'])
        ) {
            $dateEnabledFrom = null;
            $dateEnabledTo = null;
            $dateRealFrom = null;
            $dateRealTo = null;
        } else {
            $dateEnabledFrom = $data['date_enabled_from'];
            $dateEnabledTo = $data['date_enabled_to'];
            $dateRealFrom = $data['date_real_from'];
            $dateRealTo = $data['date_real_to'];
        }

        /**  @var $enabledFrom Ess_M2ePro_Model_Registry */
        $enabledFrom = Mage::getModel('M2ePro/Registry');
        $enabledFrom->loadByKey('/server/maintenance/schedule/date/enabled/from/');

        if ($enabledFrom->getValue() != $dateEnabledFrom) {
            $enabledFrom->setValue($dateEnabledFrom)->save();
        }

        /**  @var $realFrom Ess_M2ePro_Model_Registry */
        $realFrom = Mage::getModel('M2ePro/Registry');
        $realFrom->loadByKey('/server/maintenance/schedule/date/real/from/');

        if ($realFrom->getValue() != $dateRealFrom) {
            $realFrom->setValue($dateRealFrom)->save();
        }

        /**  @var $realTo Ess_M2ePro_Model_Registry */
        $realTo = Mage::getModel('M2ePro/Registry');
        $realTo->loadByKey('/server/maintenance/schedule/date/real/to/');

        /**  @var $enabledTo Ess_M2ePro_Model_Registry */
        $enabledTo = Mage::getModel('M2ePro/Registry');
        $enabledTo->loadByKey('/server/maintenance/schedule/date/enabled/to/');

        if ($realTo->getValue() != $dateRealTo) {
            $realTo->setValue($dateRealTo)->save();
            $enabledTo->setValue($dateEnabledTo)->save();
        }
    }

    //########################################
}
