<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Servicing_Task_License extends Ess_M2ePro_Model_Servicing_Task
{
    const NAME = 'license';

    /**
     * @return string
     */
    public function getPublicNick()
    {
        return self::NAME;
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
    }

    //########################################

    protected function updateInfoData(array $infoData)
    {
        $config = Mage::helper('M2ePro/Module')->getConfig();

        if (array_key_exists('email', $infoData)) {
            $config->setGroupValue('/license/info/', 'email', $infoData['email']);
        }
    }

    protected function updateValidationMainData(array $validationData)
    {
        $config = Mage::helper('M2ePro/Module')->getConfig();

        if (array_key_exists('domain', $validationData)) {
            $config->setGroupValue('/license/domain/', 'valid', $validationData['domain']);
        }

        if (array_key_exists('ip', $validationData)) {
            $config->setGroupValue('/license/ip/', 'valid', $validationData['ip']);
        }
    }

    protected function updateValidationValidData(array $isValidData)
    {
        $config = Mage::helper('M2ePro/Module')->getConfig();
        if (array_key_exists('domain', $isValidData)) {
            $config->setGroupValue('/license/domain/', 'is_valid', (int)$isValidData['domain']);
        }

        if (array_key_exists('ip', $isValidData)) {
            $config->setGroupValue('/license/ip/', 'is_valid', (int)$isValidData['ip']);
        }
    }

    protected function updateConnectionData(array $data)
    {
        $config = Mage::helper('M2ePro/Module')->getConfig();

        if (array_key_exists('domain', $data)) {
            $config->setGroupValue('/license/domain/', 'real', $data['domain']);
        }

        if (array_key_exists('ip', $data)) {
            $config->setGroupValue('/license/ip/', 'real', $data['ip']);
        }
    }
}
