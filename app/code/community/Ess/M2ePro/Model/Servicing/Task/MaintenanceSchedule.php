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
            $dateEnabledFrom = NULL;
            $dateEnabledTo = NULL;
            $dateRealFrom = NULL;
            $dateRealTo = NULL;
        } else {
            $dateEnabledFrom = $data['date_enabled_from'];
            $dateEnabledTo = $data['date_enabled_to'];
            $dateRealFrom = $data['date_real_from'];
            $dateRealTo = $data['date_real_to'];
        }

        /**  @var $enabledFrom Ess_M2ePro_Model_Registry */
        $enabledFrom = Mage::getModel('M2ePro/Registry')->load(
            '/server/maintenance/schedule/date/enabled/from/', 'key'
        );
        if ($enabledFrom->getValue() != $dateEnabledFrom) {
            $enabledFrom->addData(
                array(
                'key' => '/server/maintenance/schedule/date/enabled/from/',
                'value' => $dateEnabledFrom
                )
            )->save();
        }

        /**  @var $realFrom Ess_M2ePro_Model_Registry */
        $realFrom = Mage::getModel('M2ePro/Registry')->load('/server/maintenance/schedule/date/real/from/', 'key');
        if ($realFrom->getValue() != $dateRealFrom) {
            $realFrom->addData(
                array(
                'key' => '/server/maintenance/schedule/date/real/from/',
                'value' => $dateRealFrom
                )
            )->save();
        }

        /**  @var $realTo Ess_M2ePro_Model_Registry */
        $realTo = Mage::getModel('M2ePro/Registry')->load('/server/maintenance/schedule/date/real/to/', 'key');
        /**  @var $enabledTo Ess_M2ePro_Model_Registry */
        $enabledTo = Mage::getModel('M2ePro/Registry')->load('/server/maintenance/schedule/date/enabled/to/', 'key');
        if ($realTo->getValue() != $dateRealTo) {
            $realTo->addData(
                array(
                'key' => '/server/maintenance/schedule/date/real/to/',
                'value' => $dateRealTo
                )
            )->save();

            $enabledTo->addData(
                array(
                'key' => '/server/maintenance/schedule/date/enabled/to/',
                'value' => $dateEnabledTo
                )
            )->save();
        }
    }

    //########################################
}
