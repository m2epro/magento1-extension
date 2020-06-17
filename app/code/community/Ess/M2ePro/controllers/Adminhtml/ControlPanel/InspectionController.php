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
        return $this->getResponse()->setBody('<pre>'.print_r(Mage::app()->getCache(), true).'</pre>');
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

        return $this->getResponse()->setBody('<pre>'.print_r($resourcesConfig, true).'</pre>');
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

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Changed successfully.'));
        return $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageUrl());
    }

    public function changeMaintenanceCanBeIgnoredAction()
    {
        if (Mage::helper('M2ePro/Module_Maintenance')->isMaintenanceCanBeIgnored()) {
            Mage::helper('M2ePro/Module_Maintenance')->setMaintenanceCanBeIgnored(0);
        } else {
            Mage::helper('M2ePro/Module_Maintenance')->setMaintenanceCanBeIgnored(1);
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Changed successfully.'));
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

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Extension upgrade was successfully completed.'));
        return $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageUrl());
    }

    public function dropMagentoCoreSetupValueAction()
    {
        Mage::getSingleton('core/resource')->getConnection('core_write')->delete(
            Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('core_resource'),
            array('code = ?' => Ess_M2ePro_Model_Upgrade_MySqlSetup::MODULE_IDENTIFIER)
        );

        Mage::helper('M2ePro/Magento')->clearCache();

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Extension install was successfully completed.'));
        return $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageUrl());
    }

    //########################################
}
