<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_Installation
    extends Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_Abstract
{
    public $lastVersion;
    public $installationVersionHistory = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelInspectionInstallation');
        // ---------------------------------------

        $this->setTemplate('M2ePro/controlPanel/inspection/installation.phtml');

        $this->prepareInfo();
    }

    //########################################

    protected function prepareInfo()
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $this->latestVersion = $cacheConfig->getGroupValue('/installation/', 'public_last_version');
        $this->buildLatestVersion = $cacheConfig->getGroupValue('/installation/', 'build_last_version');

        $registryModel = Mage::getModel('M2ePro/Registry');
        $structureHelper = Mage::helper('M2ePro/Module_Database_Structure');

        if ($structureHelper->isTableExists($registryModel->getResource()->getMainTable())) {
            $this->installationVersionHistory = $registryModel
                    ->load('/installation/versions_history/', 'key')
                    ->getValueFromJson();
        }

        $this->latestUpgradeDate        = false;
        $this->latestUpgradeFromVersion = '--';
        $this->latestUpgradeToVersion   = '--';

        $lastVersion = array_pop($this->installationVersionHistory);
        if (!empty($lastVersion)) {
            $this->latestUpgradeDate        = $lastVersion['date'];
            $this->latestUpgradeFromVersion = $lastVersion['from'];
            $this->latestUpgradeToVersion   = $lastVersion['to'];
        }
    }

    protected function isShown()
    {
        if ($this->latestVersion === null) {
            return false;
        }

        $compareResult = version_compare(Mage::helper('M2ePro/Module')->getVersion(), $this->latestVersion);
        if ($compareResult >= 0 && !$this->latestUpgradeDate) {
            return false;
        }

        $daysLeftFromLastUpgrade = (Mage::helper('M2ePro')->getCurrentGmtDate(true) -
                                    Mage::helper('M2ePro')->getDate($this->latestUpgradeDate, true)) / 60 / 60 / 24;

        if ($compareResult >= 0 && $daysLeftFromLastUpgrade >= 7) {
            return false;
        }

        return true;
    }

    //########################################
}
