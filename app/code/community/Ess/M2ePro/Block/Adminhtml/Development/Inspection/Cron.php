<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Inspection_Cron
    extends Ess_M2ePro_Block_Adminhtml_Development_Inspection_Abstract
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentInspectionCron');
        //------------------------------

        $this->setTemplate('M2ePro/development/inspection/cron.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $moduleConfig = Mage::helper('M2ePro/Module')->getConfig();

        $this->cronLastRunTime = 'N/A';
        $this->cronIsNotWorking = false;
        $this->cronCurrentType = ucfirst(Mage::helper('M2ePro/Module_Cron')->getType());
        $this->cronServiceAuthKey = $moduleConfig->getGroupValue('/cron/service/', 'auth_key');

        $baseDir = Mage::helper('M2ePro/Client')->getBaseDirectory();
        $this->cronPhp = 'php -q '.$baseDir.DIRECTORY_SEPARATOR.'cron.php -mdefault 1';

        $baseUrl = Mage::helper('M2ePro/Magento')->getBaseUrl();
        $this->cronGet = 'GET '.$baseUrl.'cron.php';

        $cronLastRunTime = Mage::helper('M2ePro/Module_Cron')->getLastRun();
        if (!is_null($cronLastRunTime)) {
            $this->cronLastRunTime = $cronLastRunTime;
            $this->cronIsNotWorking = Mage::helper('M2ePro/Module_Cron')->isLastRunMoreThan(12,true);
        }

        $serviceHostName = $moduleConfig->getGroupValue('/cron/service/', 'hostname');
        $this->cronServiceIp = gethostbyname($serviceHostName);

        $this->isMagentoCronDisabled = (bool)(int)$moduleConfig->getGroupValue('/cron/magento/','disabled');
        $this->isServiceCronDisabled = (bool)(int)$moduleConfig->getGroupValue('/cron/service/','disabled');

        return parent::_beforeToHtml();
    }

    // ########################################

    public function isShownRecommendationsMessage()
    {
        if (!$this->getData('is_support_mode')) {
            return false;
        }

        if (Mage::helper('M2ePro/Module_Cron')->isTypeMagento()) {
            return true;
        }

        if (Mage::helper('M2ePro/Module_Cron')->isTypeService() && $this->cronIsNotWorking) {
            return true;
        }

        return false;
    }

    public function isShownServiceDescriptionMessage()
    {
        if (!$this->getData('is_support_mode')) {
            return false;
        }

        if (Mage::helper('M2ePro/Module_Cron')->isTypeService() && !$this->cronIsNotWorking) {
            return true;
        }

        return false;
    }

    // ########################################
}