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

        $tempValue = Mage::helper('M2ePro/Module')->getCacheConfig()->getGroupValue(
            '/default_baseurl_index/',
            'given_by_server_at'
        );
        if ($tempValue) {
            $primaryConfig = Mage::helper('M2ePro/Primary')->getConfig();
            $requestData['current_default_server_baseurl_index'] = $primaryConfig->getGroupValue(
                '/server/', 'default_baseurl_index'
            );
        }

        return $requestData;
    }

    public function processResponseData(array $data)
    {
        $this->updateServersBaseUrls($data);
        $this->updateDefaultServerBaseUrlIndex($data);
        $this->updateCronHosts($data);
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

        $config = Mage::helper('M2ePro/Primary')->getConfig();

        foreach ($data['servers_baseurls'] as $newHostName => $newBaseUrl) {
            $oldHostName = $config->getGroupValue('/server/', 'hostname_'.$index);
            $oldBaseUrl  = $config->getGroupValue('/server/', 'baseurl_'.$index);

            if ($oldHostName != $newHostName || $oldBaseUrl != $newBaseUrl) {
                $configUpdates[$index] = array(
                    'hostname' => $newHostName,
                    'baseurl' => $newBaseUrl
                );
            }

            $index++;
        }

        for ($deletedIndex = $index; $deletedIndex < 100; $deletedIndex++) {
            $deletedHostName = $config->getGroupValue('/server/', 'hostname_'.$deletedIndex);
            $deletedBaseUrl  = $config->getGroupValue('/server/', 'baseurl_'.$deletedIndex);

            if ($deletedHostName === null && $deletedBaseUrl === null) {
                break;
            }

            $config->deleteGroupValue('/server/', 'hostname_'.$deletedIndex);
            $config->deleteGroupValue('/server/', 'baseurl_'.$deletedIndex);
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
            $config->setGroupValue('/server/', 'hostname_'.$index, $change['hostname']);
            $config->setGroupValue('/server/', 'baseurl_'.$index, $change['baseurl']);
        }
    }

    protected function updateDefaultServerBaseUrlIndex(array $data)
    {
        if (!isset($data['default_server_baseurl_index']) || (int)$data['default_server_baseurl_index'] <= 0) {
            return;
        }

        Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
            '/server/', 'default_baseurl_index', (int)$data['default_server_baseurl_index']
        );

        Mage::helper('M2ePro/Module')->getCacheConfig()->setGroupValue(
            '/default_baseurl_index/', 'given_by_server_at', Mage::helper('M2ePro')->getCurrentGmtDate()
        );
    }

    protected function updateCronHosts(array $data)
    {
        if (!isset($data['cron_domains'])) {
            return;
        }

        $index = 1;
        $config = Mage::helper('M2ePro/Module')->getConfig();

        foreach ($data['cron_domains'] as $newCronHost) {
            $oldGroupValue = $config->getGroupValue('/cron/service/', 'hostname_'.$index);

            if ($oldGroupValue != $newCronHost) {
                $config->setGroupValue('/cron/service/', 'hostname_'.$index, $newCronHost);
            }

            $index++;
        }

        for ($i = $index; $i < 100; $i++) {
            $oldGroupValue = $config->getGroupValue('/cron/service/', 'hostname_'.$i);

            if ($oldGroupValue === null) {
                break;
            }

            $config->deleteGroupValue('/server/', 'hostname_'.$i);
        }
    }

    protected function updateLastVersion(array $data)
    {
        if (empty($data['last_version'])) {
            return;
        }

        Mage::helper('M2ePro/Module')->getCacheConfig()->setGroupValue(
            '/installation/', 'public_last_version', $data['last_version']['magento_1']['public']
        );

        Mage::helper('M2ePro/Module')->getCacheConfig()->setGroupValue(
            '/installation/', 'build_last_version', $data['last_version']['magento_1']['build']
        );
    }

    protected function updateSendLogs(array $data)
    {
        if (!isset($data['send_logs'])) {
            return;
        }

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/debug/logging/', 'send_to_server', (int)$data['send_logs']
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
