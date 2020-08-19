<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Registry_Manager
{
    //########################################

    public function setValue($key, $value)
    {
        is_array($value) && $value = Mage::helper('M2ePro')->jsonEncode($value);

        $registryModel = $this->loadByKey($key);
        $registryModel->setData('value', $value);
        $registryModel->save();

        return true;
    }

    public function getValue($key)
    {
        return $this->loadByKey($key)->getData('value');
    }

    public function getValueFromJson($key)
    {
        $registryModel = Mage::getModel('M2ePro/Registry')->load($key, 'key');

        return !$registryModel->getId()
            ? array()
            : Mage::helper('M2ePro')->jsonDecode($registryModel->getData('value'));
    }

    public function deleteValue($key)
    {
        $registryModel = Mage::getModel('M2ePro/Registry');
        $registryModel->load($key, 'key');

        if ($registryModel->getId()) {
            $registryModel->delete();
        }
    }

    //########################################

    protected function loadByKey($key)
    {
        $registryModel = Mage::getModel('M2ePro/Registry');
        $registryModel->load($key, 'key');

        if (!$registryModel->getId()) {
            $registryModel->setData('key', $key);
        }

        return $registryModel;
    }

    //########################################
}
