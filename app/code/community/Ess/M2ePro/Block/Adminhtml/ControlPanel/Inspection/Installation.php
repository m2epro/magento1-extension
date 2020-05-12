<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_Installation
    extends Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_Abstract
{
    public $installationVersionHistory = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('controlPanelInspectionInstallation');
        $this->setTemplate('M2ePro/controlPanel/inspection/installation.phtml');

        $this->prepareInfo();
    }

    //########################################

    protected function prepareInfo()
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $this->latestVersion = $cacheConfig->getGroupValue('/installation/', 'public_last_version');
        $this->buildLatestVersion = $cacheConfig->getGroupValue('/installation/', 'build_last_version');

        $setupModel = Mage::getModel('M2ePro/Setup');
        $structureHelper = Mage::helper('M2ePro/Module_Database_Structure');

        if ($structureHelper->isTableExists($setupModel->getResource()->getMainTable())) {

            $collection = $setupModel->getCollection();
            $collection->setOrder('create_date', $collection::SORT_ORDER_DESC);

            $this->installationVersionHistory = $collection->toArray();
            $this->installationVersionHistory = $this->installationVersionHistory['items'];
        }

        $this->latestUpgradeDate        = false;
        $this->latestUpgradeFromVersion = '--';
        $this->latestUpgradeToVersion   = '--';

        $lastVersion = array_pop($this->installationVersionHistory);
        if (!empty($lastVersion)) {
            $this->latestUpgradeDate        = $lastVersion['create_date'];
            $this->latestUpgradeFromVersion = $lastVersion['version_from'];
            $this->latestUpgradeToVersion   = $lastVersion['version_to'];
        }
    }

    //########################################
}
