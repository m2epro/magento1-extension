<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

// @codingStandardsIgnoreFile

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion630
{
    const BACKUP_TABLE_PREFIX = '__backup_v630';

    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    protected $_installer = null;

    protected $_forceAllSteps = false;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Upgrade_MySqlSetup
     */
    public function getInstaller()
    {
        return $this->_installer;
    }

    /**
     * @param Ess_M2ePro_Model_Upgrade_MySqlSetup $installer
     */
    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->_installer = $installer;
    }

    // ---------------------------------------

    public function setForceAllSteps($value = true)
    {
        $this->_forceAllSteps = $value;
    }

    //########################################

    public function migrate()
    {
        $this->processGeneral();
        $this->processMarketplace();
        $this->processDescriptionTemplate();
        $this->processListingProduct();
        $this->processAutoActions();
        $this->processProcessing();
        $this->processListing();
    }

    //########################################

    protected function processGeneral()
    {
        /** @var Ess_M2ePro_Model_Upgrade_Migration_ToVersion630_General $model */
        $model = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion630_General');
        $model->setInstaller($this->_installer);
        $model->setForceAllSteps($this->_forceAllSteps);
        $model->process();
    }

    protected function processMarketplace()
    {
        /** @var Ess_M2ePro_Model_Upgrade_Migration_ToVersion630_Marketplace $model */
        $model = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion630_Marketplace');
        $model->setInstaller($this->_installer);
        $model->setForceAllSteps($this->_forceAllSteps);
        $model->process();
    }

    protected function processDescriptionTemplate()
    {
        /** @var Ess_M2ePro_Model_Upgrade_Migration_ToVersion630_DescriptionTemplate $model */
        $model = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion630_DescriptionTemplate');
        $model->setInstaller($this->_installer);
        $model->setForceAllSteps($this->_forceAllSteps);
        $model->process();
    }

    protected function processListingProduct()
    {
        /** @var Ess_M2ePro_Model_Upgrade_Migration_ToVersion630_ListingProduct $model */
        $model = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion630_ListingProduct');
        $model->setInstaller($this->_installer);
        $model->setForceAllSteps($this->_forceAllSteps);
        $model->process();
    }

    protected function processAutoActions()
    {
        /** @var Ess_M2ePro_Model_Upgrade_Migration_ToVersion630_AutoAction $model */
        $model = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion630_AutoAction');
        $model->setInstaller($this->_installer);
        $model->setForceAllSteps($this->_forceAllSteps);
        $model->process();
    }

    protected function processProcessing()
    {
        /** @var Ess_M2ePro_Model_Upgrade_Migration_ToVersion630_Processing $model */
        $model = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion630_Processing');
        $model->setInstaller($this->_installer);
        $model->process();
    }

    protected function processListing()
    {
        /** @var Ess_M2ePro_Model_Upgrade_Migration_ToVersion630_Listing $model */
        $model = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion630_Listing');
        $model->setInstaller($this->_installer);
        $model->process();
    }

    //########################################
}
