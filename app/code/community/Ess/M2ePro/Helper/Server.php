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
        if ($this->getCurrentBaseUrlIndex() != $this->getDefaultBaseUrlIndex()) {

            $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);

            $interval = self::MAX_INTERVAL_OF_RETURNING_TO_DEFAULT_BASEURL;
            $switchingDateTime = Mage::helper('M2ePro/Module')->getCacheConfig()
                                        ->getGroupValue('/server/baseurl/','datetime_of_last_switching');

            if (is_null($switchingDateTime) || strtotime($switchingDateTime) + $interval <= $currentTimeStamp) {
                $this->setCurrentBaseUrlIndex($this->getDefaultBaseUrlIndex());
            }
        }

        return $this->getCurrentBaseUrl().'index.php';
    }

    public function switchEndpoint()
    {
        $previousIndex = $this->getCurrentBaseUrlIndex();
        $nextIndex = $previousIndex + 1;

        if (is_null($this->getBaseUrlByIndex($nextIndex))) {
            $nextIndex = 1;
        }

        if ($nextIndex == $previousIndex) {
            return false;
        }

        $this->setCurrentBaseUrlIndex($nextIndex);

        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $cacheConfig->setGroupValue('/server/baseurl/','datetime_of_last_switching',
                                        Mage::helper('M2ePro')->getCurrentGmtDate());

        return true;
    }

    //########################################

    public function getAdminKey()
    {
        return (string)Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue('/server/','admin_key');
    }

    public function getApplicationKey()
    {
        $moduleName = Mage::helper('M2ePro/Module')->getName();
        return (string)Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.$moduleName.'/server/','application_key'
        );
    }

    //########################################

    public function getCurrentBaseUrl()
    {
        return $this->getBaseUrlByIndex($this->getCurrentBaseUrlIndex());
    }

    public function getCurrentHostName()
    {
        return $this->getHostNameByIndex($this->getCurrentBaseUrlIndex());
    }

    // ---------------------------------------

    private function getDefaultBaseUrlIndex()
    {
        $index = (int)Mage::helper('M2ePro/Primary')->getConfig()
                        ->getGroupValue('/server/','default_baseurl_index');

        if ($index <= 0 || $index > $this->getMaxBaseUrlIndex()) {
            $this->setDefaultBaseUrlIndex($index = 1);
        }

        return $index;
    }

    private function getCurrentBaseUrlIndex()
    {
        $index = (int)Mage::helper('M2ePro/Module')->getCacheConfig()
                        ->getGroupValue('/server/baseurl/','current_index');

        if ($index <= 0 || $index > $this->getMaxBaseUrlIndex()) {
            $this->setCurrentBaseUrlIndex($index = $this->getDefaultBaseUrlIndex());
        }

        return $index;
    }

    // ---------------------------------------

    private function setDefaultBaseUrlIndex($index)
    {
        Mage::helper('M2ePro/Primary')->getConfig()
                ->setGroupValue('/server/','default_baseurl_index',$index);
    }

    private function setCurrentBaseUrlIndex($index)
    {
        Mage::helper('M2ePro/Module')->getCacheConfig()
                ->setGroupValue('/server/baseurl/','current_index',$index);
    }

    //########################################

    private function getMaxBaseUrlIndex()
    {
        $index = 1;

        for ($tempIndex=2; $tempIndex<100; $tempIndex++) {

            $tempBaseUrl = $this->getBaseUrlByIndex($tempIndex);

            if (!is_null($tempBaseUrl)) {
                $index = $tempIndex;
            } else {
                break;
            }
        }

        return $index;
    }

    private function getBaseUrlByIndex($index)
    {
        return Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue('/server/','baseurl_'.$index);
    }

    private function getHostNameByIndex($index)
    {
        return Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue('/server/','hostname_'.$index);
    }

    //########################################
}