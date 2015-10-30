<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Development_Info_Actual extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('developmentSummaryInfo');
        // ---------------------------------------

        $this->setTemplate('M2ePro/development/info/actual.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->magentoInfo = Mage::helper('M2ePro')->__(ucwords(Mage::helper('M2ePro/Magento')->getEditionName())) .
            ' (' . Mage::helper('M2ePro/Magento')->getVersion() . ')';

        // ---------------------------------------
        $this->moduleVersion = Mage::helper('M2ePro/Module')->getVersion();
        // ---------------------------------------

        // ---------------------------------------
        $this->phpVersion = Mage::helper('M2ePro/Client')->getPhpVersion();
        $this->phpApi = Mage::helper('M2ePro/Client')->getPhpApiName();
        // ---------------------------------------

        // ---------------------------------------
        $this->memoryLimit = Mage::helper('M2ePro/Client')->getMemoryLimit(true);
        $this->maxExecutionTime = @ini_get('max_execution_time');
        // ---------------------------------------

        // ---------------------------------------
        $this->mySqlVersion = Mage::helper('M2ePro/Client')->getMysqlVersion();
        $this->mySqlDatabaseName = Mage::helper('M2ePro/Magento')->getDatabaseName();
        // ---------------------------------------

        // ---------------------------------------
        $this->cronLastRunTime = 'N/A';
        $this->cronIsNotWorking = false;
        $this->cronCurrentRunner = ucfirst(Mage::helper('M2ePro/Module_Cron')->getRunner());

        $cronLastRunTime = Mage::helper('M2ePro/Module_Cron')->getLastRun();

        if (!is_null($cronLastRunTime)) {
            $this->cronLastRunTime = $cronLastRunTime;
            $this->cronIsNotWorking = Mage::helper('M2ePro/Module_Cron')->isLastRunMoreThan(12,true);
        }
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}