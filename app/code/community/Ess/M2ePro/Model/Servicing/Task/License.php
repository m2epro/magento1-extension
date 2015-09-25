<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Servicing_Task_License extends Ess_M2ePro_Model_Servicing_Task
{
    // ########################################

    public function getPublicNick()
    {
        return 'license';
    }

    // ########################################

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

        if (isset($data['components']) && is_array($data['components'])) {
            $this->updateComponentsData($data['components']);
        }

        if (isset($data['connection']) && is_array($data['connection'])) {
            $this->updateConnectionData($data['connection']);
        }
    }

    // ########################################

    private function updateInfoData(array $infoData)
    {
        $moduleName = Mage::helper('M2ePro/Module')->getName();
        $primaryConfig = Mage::helper('M2ePro/Primary')->getConfig();

        if (array_key_exists('email', $infoData)) {
            $primaryConfig->setGroupValue('/'.$moduleName.'/license/info/','email', $infoData['email']);
        }
    }

    private function updateValidationMainData(array $validationData)
    {
        $moduleName = Mage::helper('M2ePro/Module')->getName();
        $primaryConfig = Mage::helper('M2ePro/Primary')->getConfig();

        if (array_key_exists('domain', $validationData)) {
            $primaryConfig->setGroupValue('/'.$moduleName.'/license/','domain', $validationData['domain']);
        }

        if (array_key_exists('ip', $validationData)) {
            $primaryConfig->setGroupValue('/'.$moduleName.'/license/','ip', $validationData['ip']);
        }

        if (array_key_exists('directory', $validationData)) {
            $primaryConfig->setGroupValue('/'.$moduleName.'/license/', 'directory', $validationData['directory']);
        }
    }

    private function updateValidationValidData(array $isValidData)
    {
        $moduleName = Mage::helper('M2ePro/Module')->getName();
        $primaryConfig = Mage::helper('M2ePro/Primary')->getConfig();

        if (array_key_exists('domain', $isValidData)) {
            $primaryConfig->setGroupValue('/'.$moduleName.'/license/valid/','domain',(int)$isValidData['domain']);
        }

        if (array_key_exists('ip', $isValidData)) {
            $primaryConfig->setGroupValue('/'.$moduleName.'/license/valid/','ip',(int)$isValidData['ip']);
        }

        if (array_key_exists('directory', $isValidData)) {
            $primaryConfig->setGroupValue('/'.$moduleName.'/license/valid/','directory',(int)$isValidData['directory']);
        }
    }

    private function updateComponentsData(array $componentsData)
    {
        $primaryConfig = Mage::helper('M2ePro/Primary')->getConfig();

        foreach (Mage::helper('M2ePro/Component')->getComponents() as $component) {

            if (!isset($componentsData[$component]) || !is_array($componentsData[$component])) {
                continue;
            }

            $componentData  = $componentsData[$component];
            $componentGroup = '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/';

            if (array_key_exists('mode', $componentData)) {
                $primaryConfig->setGroupValue($componentGroup, 'mode', (int)$componentData['mode']);
            }

            if (array_key_exists('status', $componentData)) {
                $primaryConfig->setGroupValue($componentGroup, 'status', (int)$componentData['status']);
            }

            if (array_key_exists('expiration_date', $componentData)) {
                $primaryConfig->setGroupValue($componentGroup, 'expiration_date', $componentData['expiration_date']);
            }

            if (array_key_exists('is_free', $componentData)) {
                $primaryConfig->setGroupValue($componentGroup, 'is_free', (int)$componentData['is_free']);
            }
        }
    }

    private function updateConnectionData(array $data)
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

    // ########################################
}