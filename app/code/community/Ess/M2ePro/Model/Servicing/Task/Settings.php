<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Servicing_Task_Settings extends Ess_M2ePro_Model_Servicing_Task
{
    //########################################

    /**
     * @return string
     */
    public function getPublicNick()
    {
        return 'settings';
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
        $this->updateLastVersion($data);
        $this->updateAnalytics($data);
        $this->updateStatistic($data);
    }

    //########################################

    protected function updateLastVersion(array $data)
    {
        if (empty($data['last_version'])) {
            return;
        }

        Mage::helper('M2ePro/Module')->getRegistry()->setValue(
            '/installation/public_last_version/',
            $data['last_version']['magento_1']['public']
        );
        Mage::helper('M2ePro/Module')->getRegistry()->setValue(
            '/installation/build_last_version/',
            $data['last_version']['magento_1']['build']
        );
    }

    protected function updateAnalytics(array $data)
    {
        if (empty($data['analytics'])) {
            return;
        }

        /** @var Ess_M2ePro_Model_Servicing_Task_Analytics_Registry $registry */
        $registry = Mage::getSingleton('M2ePro/Servicing_Task_Analytics_Registry');

        if (isset($data['analytics']['planned_at']) && $data['analytics']['planned_at'] !== $registry->getPlannedAt()) {
            $registry->markPlannedAt($data['analytics']['planned_at']);
        }
    }

    protected function updateStatistic(array $data)
    {
        // A list of tasks to be enabled/disabled from the server
        $tasks = array(
            Ess_M2ePro_Model_Servicing_Task_Statistic_Manager::TASK_LISTING_PRODUCT_INSTRUCTION_TYPE => false
        );

        if (isset($data['statistic']['tasks'])) {
            foreach ($data['statistic']['tasks'] as $key => $value) {
                if (isset($tasks[$key])) {
                    $tasks[$key] = (bool)$value;
                }
            }
        }

        Mage::getModel('M2ePro/Servicing_Task_Statistic_Manager')->setTasksStates($tasks);
    }

    //########################################
}
