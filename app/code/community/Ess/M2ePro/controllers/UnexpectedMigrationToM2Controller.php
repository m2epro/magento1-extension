<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_UnexpectedMigrationToM2Controller extends Mage_Core_Controller_Varien_Action
{
    //########################################

    public function preDispatch()
    {
        $this->getLayout()->setArea(Mage_Core_Model_App_Area::AREA_FRONTEND);
        parent::preDispatch();
    }

    //########################################

    public function prepareAction()
    {
        $licenseKey      = $this->getRequest()->getPost('license_key', false);
        $isNeedToDisable = (bool)$this->getRequest()->getPost('disable_module', false);

        if (Mage::helper('M2ePro/Module_License')->getKey() !== $licenseKey) {
            $this->getResponse()->setHttpResponseCode(401);
            return $this->getResponse();
        }

        try {
            /** @var Ess_M2ePro_Model_Upgrade_MigrationToMagento2_Runner $migrationRunner */
            $migrationRunner = Mage::getModel('M2ePro/Upgrade_MigrationToMagento2_Runner');
            $migrationRunner->createMagentoMap();

            if ($isNeedToDisable) {
                Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/cron/', 'mode', 0);
                Mage::helper('M2ePro/Magento')->clearCache();
            }

            $createdTablesInfo = $migrationRunner->getMappingTablesRecordsCount();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            $response = array('error_message' => $exception->getMessage());

            $this->getResponse()->setHttpResponseCode(500);
            $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($response));
            return $this->getResponse();
        }

        $this->getResponse()->setHttpResponseCode(200);
        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($createdTablesInfo));
        return $this->getResponse();
    }

    public function getMappingTableDataAction()
    {
        $licenseKey       = $this->getRequest()->getPost('license_key', false);
        $mappingTableName = $this->getRequest()->getPost('mapping_table', false);
        $offset           = (int)$this->getRequest()->getPost('offset', false);
        $limit            = (int)$this->getRequest()->getPost('limit', false);

        if (Mage::helper('M2ePro/Module_License')->getKey() !== $licenseKey) {
            $this->getResponse()->setHttpResponseCode(401);
            return $this->getResponse();
        }

        try {
            /** @var Ess_M2ePro_Model_Upgrade_MigrationToMagento2_Runner $migrationRunner */
            $migrationRunner = Mage::getModel('M2ePro/Upgrade_MigrationToMagento2_Runner');
            $mappingData = $migrationRunner->selectDataFromMappingTable($mappingTableName, $limit, $offset);
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            $response = array('error_message' => $exception->getMessage());

            $this->getResponse()->setHttpResponseCode(500);
            $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($response));
            return $this->getResponse();
        }

        $this->getResponse()->setHttpResponseCode(200);
        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($mappingData));
        return $this->getResponse();
    }

    public function checkConnectionAction()
    {
        $this->getResponse()->setHttpResponseCode(200);
        $this->getResponse()->setBody('success');
        return $this->getResponse();
    }

    //########################################
}