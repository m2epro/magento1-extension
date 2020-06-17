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
        $requestData = array();

        $tempValue = Mage::helper('M2ePro/Module')->getRegistryValue(
            '/server/location/default_index_given_by_server_at/'
        );
        if ($tempValue) {
            $config = Mage::helper('M2ePro/Module')->getConfig();
            $requestData['current_default_server_baseurl_index'] = $config->getGroupValue(
                '/server/location/',
                'default_index'
            );
        }

        return $requestData;
    }

    public function processResponseData(array $data)
    {
        $this->updateServersBaseUrls($data);
        $this->updateDefaultServerBaseUrlIndex($data);
        $this->updateLastVersion($data);
        $this->updateSendLogs($data);
        $this->updateAnalytics($data);
    }

    //########################################

    protected function updateServersBaseUrls(array $data)
    {
        if (!is_array($data['servers_baseurls']) || empty($data['servers_baseurls'])) {
            return;
        }

        $index = 1;
        $configUpdates = array();

        $config = Mage::helper('M2ePro/Module')->getConfig();

        foreach ($data['servers_baseurls'] as $newHostName => $newBaseUrl) {
            $oldHostName = $config->getGroupValue('/server/location/'.$index.'/', 'hostname');
            $oldBaseUrl  = $config->getGroupValue('/server/location/'.$index.'/', 'baseurl');

            if ($oldHostName != $newHostName || $oldBaseUrl != $newBaseUrl) {
                $configUpdates[$index] = array(
                    'hostname' => $newHostName,
                    'baseurl' => $newBaseUrl
                );
            }

            $index++;
        }

        for ($deletedIndex = $index; $deletedIndex < 100; $deletedIndex++) {
            $deletedHostName = $config->getGroupValue('/server/location/'.$deletedIndex.'/', 'hostname');
            $deletedBaseUrl  = $config->getGroupValue('/server/location/'.$deletedIndex.'/', 'baseurl');

            if ($deletedHostName === null && $deletedBaseUrl === null) {
                break;
            }

            $config->deleteGroupValue('/server/location/'.$deletedIndex.'/', 'hostname');
            $config->deleteGroupValue('/server/location/'.$deletedIndex.'/', 'baseurl');
        }

        if (empty($configUpdates)) {
            return;
        }

        try {
            foreach ($configUpdates as $index => $change) {

                /** @var $dispatcherObject Ess_M2ePro_Model_M2ePro_Connector_Dispatcher */
                $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
                $connectorObj = $dispatcherObject->getConnector(
                    'server', 'check', 'state',
                    array(
                       'base_url' => $change['baseurl'].'index.php',
                       'hostname' => $change['hostname'],
                    )
                );
                $dispatcherObject->process($connectorObj);
                $response = $connectorObj->getResponseData();

                if (empty($response['state'])) {
                    return;
                }
            }
        } catch (Exception $e) {
            return;
        }

        foreach ($configUpdates as $index => $change) {
            $config->setGroupValue('/server/location/'.$index.'/', 'hostname', $change['hostname']);
            $config->setGroupValue('/server/location/'.$index.'/', 'baseurl', $change['baseurl']);
        }
    }

    protected function updateDefaultServerBaseUrlIndex(array $data)
    {
        if (!isset($data['default_server_baseurl_index']) || (int)$data['default_server_baseurl_index'] <= 0) {
            return;
        }

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/server/location/',
            'default_index',
            (int)$data['default_server_baseurl_index']
        );

        Mage::helper('M2ePro/Module')->setRegistryValue(
            '/server/location/default_index_given_by_server_at/',
            Mage::helper('M2ePro')->getCurrentGmtDate()
        );
    }

    protected function updateLastVersion(array $data)
    {
        if (empty($data['last_version'])) {
            return;
        }

        Mage::helper('M2ePro/Module')->setRegistryValue(
            '/installation/public_last_version/',
            $data['last_version']['magento_1']['public']
        );
        Mage::helper('M2ePro/Module')->setRegistryValue(
            '/installation/build_last_version/',
            $data['last_version']['magento_1']['build']
        );
    }

    protected function updateSendLogs(array $data)
    {
        if (!isset($data['send_logs'])) {
            return;
        }

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/server/logging/', 'send', (int)$data['send_logs']
        );
    }

    protected function updateAnalytics(array $data)
    {
        if (empty($data['analytics'])) {
            return;
        }

        $registry = Mage::getSingleton('M2ePro/Servicing_Task_Analytics_Registry');

        if (isset($data['analytics']['planned_at']) && $data['analytics']['planned_at'] !== $registry->getPlannedAt()) {
            $registry->markPlannedAt($data['analytics']['planned_at']);
        }
    }

    //########################################
}
