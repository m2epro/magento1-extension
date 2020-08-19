<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_SupportController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Help Center'));

        $this->_initPopUp();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/help');
    }

    //########################################

    public function loadLayout($ids=null, $generateBlocks=true, $generateXml=true)
    {
        $tempResult = parent::loadLayout($ids, $generateBlocks, $generateXml);
        $tempResult->_setActiveMenu(Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK);
        return $tempResult;
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction();

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_support'))
             ->renderLayout();
    }

    //########################################

    public function testExecutionTimeAction()
    {
        Mage::helper('M2ePro/Client')->testExecutionTime((int)$this->getRequest()->getParam('seconds'));
        return $this->_addJsonContent(array('result' => true));
    }

    public function testExecutionTimeResultAction()
    {
        return $this->_addJsonContent(
            array(
                'result' => Mage::helper('M2ePro/Client')->getTestedExecutionTime()
            )
        );
    }

    public function testMemoryLimitAction()
    {
        Mage::helper('M2ePro/Client')->testMemoryLimit(null);
        return $this->_addJsonContent(array('result' => true));
    }

    public function testMemoryLimitResultAction()
    {
        return $this->_addJsonContent(
            array(
                'result' => (int)(Mage::helper('M2ePro/Client')->getTestedMemoryLimit() / 1024 / 1024)
            )
        );
    }

    //########################################
}
