<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Configuration_SettingsController
    extends Ess_M2ePro_Controller_Adminhtml_Configuration_MainController
{
    //#############################################

    public function saveAction()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/view/','show_products_thumbnails',
            (int)$this->getRequest()->getParam('products_show_thumbnails')
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/view/', 'show_block_notices',
            (int)$this->getRequest()->getParam('block_notices_show')
        );

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/product/force_qty/', 'mode',
            (int)$this->getRequest()->getParam('force_qty_mode')
        );

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/product/force_qty/', 'value',
            (int)$this->getRequest()->getParam('force_qty_value')
        );

        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
            '/defaults/inspector/', 'mode',
            (int)$this->getRequest()->getParam('inspector_mode')
        );

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('The global Settings have been successfully saved.')
        );

        $this->_redirectUrl($this->_getRefererUrl());
    }

    //#############################################

    public function restoreBlockNoticesAction()
    {
        foreach ($_COOKIE as $name => $value) {
            strpos($name,'m2e_bn_') !== false && setcookie($name, '', 0, '/');
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('All Help Blocks were restored.'));
        $this->_redirectUrl($this->_getRefererUrl());
    }

    //#############################################
}