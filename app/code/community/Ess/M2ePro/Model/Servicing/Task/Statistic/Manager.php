<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * Class Ess_M2ePro_Model_Servicing_Task_Statistic_Manager
 */
class Ess_M2ePro_Model_Servicing_Task_Statistic_Manager
{
    const STORAGE_KEY = 'servicing/statistic';
    const TASK_LISTING_PRODUCT_INSTRUCTION_TYPE = 'listing_product_instruction_type_statistic';

    /** @var Ess_M2ePro_Model_Registry_Manager $registryManager */
    private $registryManager;

    public function __construct()
    {
        $this->registryManager = Mage::getModel('M2ePro/Registry_Manager');
    }

    //########################################

    /**
     * @param array $data
     */
    public function setTasksStates($data)
    {
        $regData = $this->getStoredData();
        $regData['tasks'] = $data;

        $this->registryManager->setValue(self::STORAGE_KEY, $regData);
    }

    //########################################

    /**
     * @param string $taskKey
     *
     * @return bool
     */
    public function isTaskEnabled($taskKey)
    {
        $regData = $this->getStoredData();

        return isset($regData['tasks'][$taskKey]) ? (bool)$regData['tasks'][$taskKey] : false;
    }

    //########################################

    /**
     * @return array
     */
    protected function getStoredData()
    {
        return $this->registryManager->getValueFromJson(self::STORAGE_KEY);
    }

    //########################################
}
