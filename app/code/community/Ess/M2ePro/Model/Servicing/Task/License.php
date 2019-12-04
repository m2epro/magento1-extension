<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Servicing_Task_License extends Ess_M2ePro_Model_Servicing_Task
{
    //########################################

    /**
     * @return string
     */
    public function getPublicNick()
    {
        return 'license';
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
        if (isset($data['info']) && is_array($data['info'])) {
            $this->updateInfoData($data['info']);
        }

        if (isset($data['validation']) && is_array($data['validation'])) {
            $this->updateValidationMainData($data['validation']);

            if (isset($data['validation']['validation']) && is_array($data['validation']['validation'])) {
                $this->updateValidationValidData($data['validation']['validation']);
            }
        }

        if (isset($data['connection']) && is_array($data['connection'])) {
            $this->updateConnectionData($data['connection']);
        }

        if (isset($data['status'])) {
            $this->updateStatus($data['status']);
        }
    }

    //########################################

    protected function updateInfoData(array $infoData)
    {
        if (array_key_exists('email', $infoData)) {
            Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
                '/license/info/', 'email', $infoData['email']
            );
        }
    }

    protected function updateValidationMainData(array $validationData)
    {

        if (array_key_exists('domain', $validationData)) {
            Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
                '/license/', 'domain', $validationData['domain']
            );
        }

        if (array_key_exists('ip', $validationData)) {
            Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
                '/license/', 'ip', $validationData['ip']
            );
        }
    }

    protected function updateValidationValidData(array $isValidData)
    {
        if (array_key_exists('domain', $isValidData)) {
            Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
                '/license/valid/', 'domain', (int)$isValidData['domain']
            );
        }

        if (array_key_exists('ip', $isValidData)) {
            Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
                '/license/valid/', 'ip', (int)$isValidData['ip']
            );
        }
    }

    protected function updateConnectionData(array $data)
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();

        if (array_key_exists('domain', $data)) {
            $cacheConfig->setGroupValue('/license/connection/', 'domain', $data['domain']);
        }

        if (array_key_exists('ip', $data)) {
            $cacheConfig->setGroupValue('/license/connection/', 'ip', $data['ip']);
        }

        if (array_key_exists('directory', $data)) {
            $cacheConfig->setGroupValue('/license/connection/', 'directory', $data['directory']);
        }
    }

    protected function updateStatus($status)
    {
        Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue('/license/', 'status', (int)$status);
    }

    //########################################
}
