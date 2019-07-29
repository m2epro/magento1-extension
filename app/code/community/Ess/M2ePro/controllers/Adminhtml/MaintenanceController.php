<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_MaintenanceController extends Mage_Adminhtml_Controller_Action
{
    //########################################

    public function indexAction()
    {
        if (!Mage::helper('M2ePro/Module_Maintenance')->isEnabled()) {
            return $this->_redirect('adminhtml/dashboard');
        }

        $isPreparedForMigration = (bool)Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/migrationtomagento2/source/', 'is_prepared_for_migration'
        );

        /** @var Mage_Adminhtml_Block_Template $block */
        $block = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Template');
        $block->setData('is_prepared_for_migration', $isPreparedForMigration);
        $block->setTemplate('M2ePro/maintenance.phtml');

        $this->loadLayout()
            ->_addContent($block)
            ->_setActiveMenu('m2epro')
            ->_title($this->__('M2E Pro is currently in a maintenance mode (Module is not working now)'));

        $this->renderLayout();
    }

    //########################################
}