<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_Cron
    extends Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('controlPanelInspectionCron');
        $this->setTemplate('M2ePro/controlPanel/inspection/cron.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $moduleConfig = Mage::helper('M2ePro/Module')->getConfig();

        $this->cronLastRunTime = 'N/A';
        $this->cronIsNotWorking = false;
        $this->cronCurrentRunner = ucfirst(Mage::helper('M2ePro/Module_Cron')->getRunner());
        $this->cronServiceAuthKey = $moduleConfig->getGroupValue('/cron/service/', 'auth_key');

        $cronLastRunTime = Mage::helper('M2ePro/Module_Cron')->getLastRun();
        if ($cronLastRunTime !== null) {
            $this->cronLastRunTime = $cronLastRunTime;
            $this->cronIsNotWorking = Mage::helper('M2ePro/Module_Cron')->isLastRunMoreThan(1, true);
        }

        $this->isMagentoCronDisabled = (bool)(int)$moduleConfig->getGroupValue('/cron/magento/', 'disabled');
        $this->isServiceCronDisabled = (bool)(int)$moduleConfig->getGroupValue('/cron/service/', 'disabled');

        return parent::_beforeToHtml();
    }

    //########################################

    public function isShownServiceDescriptionMessage()
    {
        if (!$this->getData('is_support_mode')) {
            return false;
        }

        if (Mage::helper('M2ePro/Module_Cron')->isRunnerService() && !$this->cronIsNotWorking) {
            return true;
        }

        return false;
    }

    //########################################
}
