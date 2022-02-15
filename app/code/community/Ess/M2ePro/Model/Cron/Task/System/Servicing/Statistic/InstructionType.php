<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * Class Ess_M2ePro_Model_Cron_Task_System_Servicing_Statistic_InstructionType
 */
class Ess_M2ePro_Model_Cron_Task_System_Servicing_Statistic_InstructionType extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'system/servicing/statistic/instruction_type';

    const REGISTRY_KEY_DATA = '/system/servicing/statistic/instruction_type/type_timing/';
    const REGISTRY_KEY_LAST_LAUNCH = '/system/servicing/statistic/instruction_type/last_launch/';

    const DEFAULT_TIME_LIMIT = 300;

    /** @var Ess_M2ePro_Helper_Data $helperData */
    private $helperData;

    /** @var Ess_M2ePro_Model_Registry_Manager $registryManager */
    private $registryManager;

    /** @var Ess_M2ePro_Model_Servicing_Task_Statistic_Manager $statisticManager */
    private $statisticManager;

    /** @var DateTime */
    private $currentDateTime;

    //########################################

    public function __construct()
    {
        $this->helperData = Mage::Helper('M2ePro/Data');
        $this->registryManager = Mage::getModel('M2ePro/Registry_Manager');
        $this->statisticManager = Mage::getModel('M2ePro/Servicing_Task_Statistic_Manager');
        $this->currentDateTime = $this->helperData->createCurrentGmtDateTime();
    }

    //########################################

    protected function performActions()
    {
        if (!$this->statisticManager->isTaskEnabled(
            Ess_M2ePro_Model_Servicing_Task_Statistic_Manager::TASK_LISTING_PRODUCT_INSTRUCTION_TYPE
        )) {
            return;
        }

        $lastLaunch = $this->registryManager->getValue(self::REGISTRY_KEY_LAST_LAUNCH);
        $lastLaunchDateTime = $lastLaunch === null ? null : $this->helperData->createGmtDateTime($lastLaunch);

        $currentTime = $this->currentDateTime->format('Y-m-d H:i:s');

        // Set a new launch time value
        $this->registryManager->setValue(self::REGISTRY_KEY_LAST_LAUNCH, $currentTime);

        // If the previous launch was more than 5 minutes ago, we get instructions only for last 5 minutes
        // Set the start time of the selection to 5 minutes ago
        if (
            $lastLaunchDateTime === null
            || $this->currentDateTime->getTimestamp() - $lastLaunchDateTime->getTimestamp() > self::DEFAULT_TIME_LIMIT
        ) {
            $lastLaunchDateTime = clone $this->currentDateTime;
            $lastLaunchDateTime->modify('-' . self::DEFAULT_TIME_LIMIT . ' seconds');
        }

        $lastLaunchTime = $lastLaunchDateTime->format('Y-m-d H:i:s');
        $lastLaunchHour = $lastLaunchDateTime->format('H');
        $currentHour = $this->currentDateTime->format('H');

        $typeTiming = $this->registryManager->getValueFromJson(self::REGISTRY_KEY_DATA);

        if ($lastLaunchHour != $currentHour) {
            $this->addNewTypeTimings(
                $lastLaunchHour,
                $lastLaunchTime,
                $lastLaunchDateTime->format('Y-m-d H:59:59'),
                $typeTiming
            );

            $this->addNewTypeTimings(
                $currentHour,
                $this->currentDateTime->format('Y-m-d H:00:00'),
                $currentTime,
                $typeTiming
            );
        } else {
            $this->addNewTypeTimings(
                $currentHour,
                $lastLaunchTime,
                $currentTime,
                $typeTiming
            );
        }

        $this->registryManager->setValue(self::REGISTRY_KEY_DATA, $typeTiming);
    }

    //########################################

    /**
     * @param string $hour
     * @param string $startTime
     * @param string $endTime
     * @param array $typeTiming
     */
    private function addNewTypeTimings($hour, $startTime, $endTime, &$typeTiming)
    {
        $hour .= '-00';
        $date = $this->currentDateTime->format('Y-m-d');

        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName = Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->getMainTable();

        $query = "
            SELECT `type`, COUNT(*) as `qty`
            FROM `{$tableName}`
            WHERE `create_date` BETWEEN '{$startTime}' AND '{$endTime}'
            GROUP BY `type`
        ";

        $typeQty = $connection->fetchAll($query);

        foreach ($typeQty as $item) {
            $type = $item['type'];
            $qty = (int)$item['qty'];

            if (!isset($typeTiming[$date][$hour][$type])) {
                $typeTiming[$date][$hour][$type] = $qty;
            } else {
                $typeTiming[$date][$hour][$type] += $qty;
            }
        }
    }

    //########################################
}
