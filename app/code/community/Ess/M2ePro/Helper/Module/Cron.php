<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Module_Cron extends Mage_Core_Helper_Abstract
{
    const TYPE_MAGENTO = 'magento';
    const TYPE_SERVICE = 'service';

    const STATE_IN_PROGRESS = 0;
    const STATE_COMPLETED   = 1;
    const STATE_NOT_FOUND   = 2;

    // ########################################

    public function isModeEnabled()
    {
        return (bool)$this->getConfigValue('mode');
    }

    public function isReadyToRun()
    {
        return Mage::helper('M2ePro/Module')->isMigrationWizardFinished() &&
               (
                   Mage::helper('M2ePro/View_Ebay')->isInstallationWizardFinished() ||
                   Mage::helper('M2ePro/View_Common')->isInstallationWizardFinished()
               );
    }

    // ########################################

    public function getType()
    {
        return $this->getConfigValue('type');
    }

    public function setType($value)
    {
        return $this->setConfigValue('type', $value);
    }

    // ----------------------------------------

    public function isTypeMagento()
    {
        return $this->getType() == self::TYPE_MAGENTO;
    }

    public function isTypeService()
    {
        return $this->getType() == self::TYPE_SERVICE;
    }

    // ########################################

    public function getLastTypeChange()
    {
        return $this->getConfigValue('last_type_change');
    }

    public function setLastTypeChange($value)
    {
        $this->setConfigValue('last_type_change', $value);
    }

    // ----------------------------------------

    public function isLastTypeChangeMoreThan($interval, $isHours = false)
    {
        $isHours && $interval *= 3600;
        $lastTypeChange = $this->getLastTypeChange();

        if (is_null($lastTypeChange)) {

            $tempTimeCacheKey = 'cron_start_time_of_checking_last_type_change';
            $lastTypeChange = Mage::helper('M2ePro/Data_Cache_Permanent')->getValue($tempTimeCacheKey);

            if (empty($lastTypeChange)) {
                $lastTypeChange = Mage::helper('M2ePro')->getCurrentGmtDate();
                Mage::helper('M2ePro/Data_Cache_Permanent')->setValue($tempTimeCacheKey,$lastTypeChange,array('cron'));
            }
        }

        return Mage::helper('M2ePro')->getCurrentGmtDate(true) > strtotime($lastTypeChange) + $interval;
    }

    // ########################################

    public function getLastAccess()
    {
        return $this->getConfigValue('last_access');
    }

    public function setLastAccess($value)
    {
        return $this->setConfigValue('last_access',$value);
    }

    // ----------------------------------------

    public function isLastAccessMoreThan($interval, $isHours = false)
    {
        $isHours && $interval *= 3600;
        $lastAccess = $this->getLastAccess();

        if (is_null($lastAccess)) {

            $tempTimeCacheKey = 'cron_start_time_of_checking_last_access';
            $lastAccess = Mage::helper('M2ePro/Data_Cache_Permanent')->getValue($tempTimeCacheKey);

            if (empty($lastAccess)) {
                $lastAccess = Mage::helper('M2ePro')->getCurrentGmtDate();
                Mage::helper('M2ePro/Data_Cache_Permanent')->setValue($tempTimeCacheKey,$lastAccess,array('cron'));
            }
        }

        return Mage::helper('M2ePro')->getCurrentGmtDate(true) > strtotime($lastAccess) + $interval;
    }

    // ########################################

    public function getLastRun()
    {
        return $this->getConfigValue('last_run');
    }

    public function setLastRun($value)
    {
        return $this->setConfigValue('last_run',$value);
    }

    // ----------------------------------------

    public function isLastRunMoreThan($interval, $isHours = false)
    {
        $isHours && $interval *= 3600;
        $lastRun = $this->getLastRun();

        if (is_null($lastRun)) {

            $tempTimeCacheKey = 'cron_start_time_of_checking_last_run';
            $lastRun = Mage::helper('M2ePro/Data_Cache_Permanent')->getValue($tempTimeCacheKey);

            if (empty($lastRun)) {
                $lastRun = Mage::helper('M2ePro')->getCurrentGmtDate();
                Mage::helper('M2ePro/Data_Cache_Permanent')->setValue($tempTimeCacheKey,$lastRun,array('cron'));
            }
        }

        return Mage::helper('M2ePro')->getCurrentGmtDate(true) > strtotime($lastRun) + $interval;
    }

    // ########################################

    private function getConfig()
    {
        return Mage::helper('M2ePro/Module')->getConfig();
    }

    // ----------------------------------------

    private function getConfigValue($key)
    {
        return $this->getConfig()->getGroupValue('/cron/', $key);
    }

    private function setConfigValue($key, $value)
    {
        return $this->getConfig()->setGroupValue('/cron/', $key, $value);
    }

    // ########################################
}