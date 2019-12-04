<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Server extends Mage_Core_Helper_Abstract
{
    const MAX_INTERVAL_OF_RETURNING_TO_DEFAULT_BASEURL = 86400;

    //########################################

    public function getEndpoint()
    {
        if ($this->getCurrentIndex() != $this->getDefaultIndex()) {
            $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);

            $interval = self::MAX_INTERVAL_OF_RETURNING_TO_DEFAULT_BASEURL;
            $switchingDateTime = Mage::helper('M2ePro/Module')->getCacheConfig()->getGroupValue(
                '/server/location/', 'datetime_of_last_switching'
            );

            if ($switchingDateTime === null || strtotime($switchingDateTime) + $interval <= $currentTimeStamp) {
                $this->setCurrentIndex($this->getDefaultIndex());
            }
        }

        return $this->getCurrentBaseUrl().'index.php';
    }

    public function switchEndpoint()
    {
        $previousIndex = $this->getCurrentIndex();
        $nextIndex = $previousIndex + 1;

        if ($this->getBaseUrlByIndex($nextIndex) === null) {
            $nextIndex = 1;
        }

        if ($nextIndex == $previousIndex) {
            return false;
        }

        $this->setCurrentIndex($nextIndex);

        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $cacheConfig->setGroupValue(
            '/server/location/',
            'datetime_of_last_switching',
            Mage::helper('M2ePro')->getCurrentGmtDate()
        );

        return true;
    }

    //########################################

    public function getAdminKey()
    {
        return (string)Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue('/server/', 'admin_key');
    }

    public function getApplicationKey()
    {
        return (string)Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue('/server/', 'application_key');
    }

    //########################################

    public function getCurrentBaseUrl()
    {
        return $this->getBaseUrlByIndex($this->getCurrentIndex());
    }

    public function getCurrentHostName()
    {
        return $this->getHostNameByIndex($this->getCurrentIndex());
    }

    // ---------------------------------------

    protected function getDefaultIndex()
    {
        $index = (int)Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/server/location/', 'default_index'
        );

        if ($index <= 0 || $index > $this->getMaxBaseUrlIndex()) {
            $this->setDefaultBaseUrlIndex($index = 1);
        }

        return $index;
    }

    protected function getCurrentIndex()
    {
        $index = (int)Mage::helper('M2ePro/Module')->getCacheConfig()->getGroupValue(
            '/server/location/', 'current_index'
        );

        if ($index <= 0 || $index > $this->getMaxBaseUrlIndex()) {
            $this->setCurrentIndex($index = $this->getDefaultIndex());
        }

        return $index;
    }

    // ---------------------------------------

    protected function setDefaultBaseUrlIndex($index)
    {
        Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue('/server/location/', 'default_index', $index);
    }

    protected function setCurrentIndex($index)
    {
        Mage::helper('M2ePro/Module')->getCacheConfig()->setGroupValue('/server/location/', 'current_index', $index);
    }

    //########################################

    protected function getMaxBaseUrlIndex()
    {
        $index = 1;

        for ($tempIndex=2; $tempIndex<100; $tempIndex++) {
            $tempBaseUrl = $this->getBaseUrlByIndex($tempIndex);

            if ($tempBaseUrl !== null) {
                $index = $tempIndex;
            } else {
                break;
            }
        }

        return $index;
    }

    protected function getBaseUrlByIndex($index)
    {
        return Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue('/server/location/'.$index.'/', 'baseurl');
    }

    protected function getHostNameByIndex($index)
    {
        return Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue('/server/location/'.$index.'/', 'hostname');
    }

    //########################################
}
