<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Info_Actual extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('controlPanelSummaryInfo');
        $this->setTemplate('M2ePro/controlPanel/info/actual.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $this->systemName = Mage::helper('M2ePro/Client')->getSystem();
        $this->systemTime = Mage::helper('M2ePro')->getCurrentGmtDate();
        // ---------------------------------------

        $this->magentoInfo = Mage::helper('M2ePro')->__(ucwords(Mage::helper('M2ePro/Magento')->getEditionName())) .
            ' (' . Mage::helper('M2ePro/Magento')->getVersion() . ')';

        // ---------------------------------------
        $this->publicVersion = Mage::helper('M2ePro/Module')->getPublicVersion();
        $this->setupVersion  = Mage::helper('M2ePro/Module')->getSetupVersion();
        $this->moduleEnvironment = Mage::helper('M2ePro/Module')->getEnvironment();
        // ---------------------------------------

        // ---------------------------------------
        $this->maintenanceMode = Mage::helper('M2ePro/Module_Maintenance')->isEnabled();
        $this->maintenanceCanBeIgnored = Mage::helper('M2ePro/Module_Maintenance')->isMaintenanceCanBeIgnored();;
        // ---------------------------------------

        // ---------------------------------------
        $this->coreResourceVersion = Mage::getResourceSingleton('core/resource')->getDbVersion('M2ePro_setup');
        $this->coreResourceDataVersion = Mage::getResourceSingleton('core/resource')->getDataVersion('M2ePro_setup');
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
        $this->mySqlPrefix = Mage::helper('M2ePro/Magento')->getDatabaseTablesPrefix();
        empty($this->mySqlPrefix) && $this->mySqlPrefix = Mage::helper('M2ePro')->__('disabled');
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}
