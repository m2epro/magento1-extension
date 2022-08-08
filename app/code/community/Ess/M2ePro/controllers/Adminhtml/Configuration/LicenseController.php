<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Configuration_LicenseController
    extends Ess_M2ePro_Controller_Adminhtml_Configuration_MainController
{
    //########################################

    public function confirmKeyAction()
    {
        if (!$this->getRequest()->isAjax() || !$this->getRequest()->isPost()) {
            $this->_getSession()->addSuccess(
                Mage::helper('M2ePro')->__('Configurations saved.')
            );
            return $this->_redirectUrl($this->_getRefererUrl());
        }

        $post = $this->getRequest()->getPost();
        $config = Mage::helper('M2ePro/Module')->getConfig();

        $key = strip_tags($post['key']);
        $config->setGroupValue('/license/', 'key', (string)$key);

        try {
            Mage::getModel('M2ePro/Servicing_Dispatcher')->processTask(
                \Ess_M2ePro_Model_Servicing_Task_License::NAME
            );
        } catch (Exception $e) {
            return $this->_getSession()->addError(
                Mage::helper('M2ePro')->__($e->getMessage())
            );
        }

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('Extension Key updated.')
        );

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('success' => true)));
    }

    //########################################

    public function refreshStatusAction()
    {
        try {
            Mage::getModel('M2ePro/Servicing_Dispatcher')->processTask(
                \Ess_M2ePro_Model_Servicing_Task_License::NAME
            );
        } catch (Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__($e->getMessage())
            );

            return $this->_redirectUrl($this->_getRefererUrl());
        }

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('Extension Key refreshed.')
        );

        $this->_redirectUrl($this->_getRefererUrl());
    }

    //########################################
}
