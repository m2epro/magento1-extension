<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_ControlPanel_InspectionController
    extends Ess_M2ePro_Controller_Adminhtml_ControlPanel_CommandController
{
    //########################################

    public function phpInfoAction()
    {
        phpinfo();
    }

    public function cacheSettingsAction()
    {
        return $this->getResponse()->setBody('<pre>' . print_r(Mage::app()->getCache(), true) . '</pre>');
    }

    public function resourcesSettingsAction()
    {
        $resourcesConfig = Mage::getConfig()->getNode('global/resources');
        $resourcesConfig = Mage::helper('M2ePro')->jsonDecode(
            Mage::helper('M2ePro')->jsonEncode((array)$resourcesConfig)
        );

        $secureKeys = array('host', 'username', 'password');
        foreach ($resourcesConfig as &$configItem) {
            if (!isset($configItem['connection']) || !is_array($configItem['connection'])) {
                continue;
            }

            foreach ($secureKeys as $key) {
                if (!isset($configItem['connection'][$key])) {
                    continue;
                }

                $configItem['connection'][$key] = str_repeat('*', strlen($configItem['connection'][$key]));
            }
        }

        return $this->getResponse()->setBody('<pre>' . print_r($resourcesConfig, true) . '</pre>');
    }

    //########################################

    public function cronScheduleTableAction()
    {
        $this->loadLayout();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ControlPanel_inspection_cronScheduleTable_grid');

            return $this->getResponse()->setBody($block->toHtml());
        }

        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ControlPanel_inspection_cronScheduleTable');

        $this->_addContent($block);

        return $this->renderLayout();
    }

    public function cronScheduleTableShowMessagesAction()
    {
        $id = $this->getRequest()->getParam('id');
        if (empty($id)) {
            return $this->_redirect('*/*/cronScheduleTable');
        }

        return $this->getResponse()->setBody(Mage::getModel('cron/schedule')->load($id)->getMessages());
    }

    //########################################

    public function changeMaintenanceModeAction()
    {
        if (Mage::helper('M2ePro/Module_Maintenance')->isEnabled()) {
            Mage::helper('M2ePro/Module_Maintenance')->disable();
        } else {
            Mage::helper('M2ePro/Module_Maintenance')->enable();
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Changed.'));

        return $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageUrl());
    }

    //########################################

    public function setMagentoCoreSetupValueAction()
    {
        $version = $this->getRequest()->getParam('version');
        if (!$version) {
            $this->_getSession()->addWarning('Version is not provided.');

            return $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageUrl());
        }

        $version = str_replace(',', '.', $version);
        if (!version_compare(Ess_M2ePro_Model_Upgrade_MySqlSetup::MIN_SUPPORTED_VERSION_FOR_UPGRADE, $version, '<=')) {
            $this->_getSession()->addError(
                sprintf(
                    'Extension upgrade can work only from %s version.',
                    Ess_M2ePro_Model_Upgrade_MySqlSetup::MIN_SUPPORTED_VERSION_FOR_UPGRADE
                )
            );

            return $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageUrl());
        }

        Mage::getSingleton('core/resource')->getConnection('core_write')->update(
            Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('core_resource'),
            array(
                'version'      => $version,
                'data_version' => $version
            ),
            array('code = ?' => Ess_M2ePro_Model_Upgrade_MySqlSetup::MODULE_IDENTIFIER)
        );

        Mage::helper('M2ePro/Magento')->clearCache();

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Extension upgrade was completed.'));

        return $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageUrl());
    }

    //########################################

    public function getInspectionsGridAction()
    {
        $grid = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_controlPanel_inspection_grid');

        return $this->getResponse()->setBody($grid->toHtml());
    }

    public function checkInspectionAction()
    {
        $inspectionName = $this->getRequest()->getParam('name');

        /** @var Ess_M2ePro_Model_ControlPanel_Inspection_Repository $repository */
        $repository = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Repository');
        $definition = $repository->getDefinition($inspectionName);

        /** @var Ess_M2ePro_Model_ControlPanel_Inspection_Processor $processor */
        $processor = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Processor');

        $result = $processor->process($definition);

        $isSuccess = true;
        $metadata = '';
        $message = $this->__('Success');

        if ($result->isSuccess()) {
            $issues = $result->getIssues();

            if (!empty($issues)) {
                $isSuccess = false;
                $lastIssue = end($issues);

                $metadata = $lastIssue->getMetadata();
                $message = $lastIssue->getMessage();
            }
        } else {
            $message = $result->getErrorMessage();
            $isSuccess = false;
        }

        $this->_addJsonContent(
            array(
                'result'   => $isSuccess,
                'metadata' => $metadata,
                'message'  => $message
            )
        );
    }

    //########################################
}
