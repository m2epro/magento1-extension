<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Servicing_Task_Settings extends Ess_M2ePro_Model_Servicing_Task
{
    // ########################################

    public function getPublicNick()
    {
        return 'settings';
    }

    // ########################################

    public function getRequestData()
    {
        $requestData = array();

        $tempValue = Mage::helper('M2ePro/Module')->getCacheConfig()->getGroupValue('/default_baseurl_index/',
                                                                                    'given_by_server_at');
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
        $this->updateLockData($data);
        $this->updateServersBaseUrls($data);
        $this->updateDefaultServerBaseUrlIndex($data);
        $this->updateLastVersion($data);
        $this->updateSendLogs($data);
    }

    // ########################################

    private function updateLockData(array $data)
    {
        if (!isset($data['lock'])) {
            return;
        }

        $validValues = array(
            Ess_M2ePro_Helper_Module::SERVER_LOCK_NO,
            Ess_M2ePro_Helper_Module::SERVER_LOCK_YES
        );

        if (!in_array((int)$data['lock'],$validValues)) {
            return;
        }

        Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/server/','lock',(int)$data['lock']
        );
    }

    private function updateServersBaseUrls(array $data)
    {
        if (!isset($data['servers_baseurls']) || !is_array($data['servers_baseurls'])) {
            return;
        }

        $config = Mage::helper('M2ePro/Primary')->getConfig();

        $index = 1;
        foreach ($data['servers_baseurls'] as $newServerBaseUrl) {

            $oldServerBaseUrl = $config->getGroupValue('/server/','baseurl_'.$index);

            if ($oldServerBaseUrl != $newServerBaseUrl) {
                $config->setGroupValue('/server/', 'baseurl_'.$index, $newServerBaseUrl);
            }

            $index++;
        }

        for ($deletedIndex=$index; $deletedIndex<100; $deletedIndex++) {

            $deletedBaseUrl = $config->getGroupValue('/server/','baseurl_'.$deletedIndex);

            if (is_null($deletedBaseUrl)) {
                break;
            }

            $config->deleteGroupValue('/server/','baseurl_'.$deletedIndex);
        }
    }

    private function updateDefaultServerBaseUrlIndex(array $data)
    {
        if (!isset($data['default_server_baseurl_index']) || (int)$data['default_server_baseurl_index'] <= 0) {
            return;
        }

        Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
            '/server/','default_baseurl_index',(int)$data['default_server_baseurl_index']
        );

        Mage::helper('M2ePro/Module')->getCacheConfig()->setGroupValue(
            '/default_baseurl_index/', 'given_by_server_at', Mage::helper('M2ePro')->getCurrentGmtDate()
        );
    }

    private function updateLastVersion(array $data)
    {
        if (empty($data['last_version'])) {
            return;
        }

        Mage::helper('M2ePro/Module')->getCacheConfig()->setGroupValue(
            '/installation/', 'last_version', $data['last_version']
        );
    }

    private function updateSendLogs(array $data)
    {
        if (!isset($data['send_logs'])) {
            return;
        }

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/debug/logging/', 'send_to_server', (int)$data['send_logs']
        );
    }

    // ########################################
}