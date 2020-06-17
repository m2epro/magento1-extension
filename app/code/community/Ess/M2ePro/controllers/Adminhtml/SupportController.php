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

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Support.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addCss('M2ePro/css/Plugin/DropDown.css');

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

    public function getResultsHtmlAction()
    {
        $query = $this->getRequest()->getParam('query');
        $blockData = Mage::helper('M2ePro/Module_Support_Search')->process($query);

        $blockHtml = $this->loadLayout()
                          ->getLayout()
                          ->createBlock('M2ePro/adminhtml_support_results', '', array('results_data' => $blockData))
                          ->toHtml();

        $this->getResponse()->setBody($blockHtml);
    }

    //########################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->_redirect('*/*/index');
        }

        $keys = array(
            'component',
            'contact_mail',
            'contact_name',
            'subject',
            'description'
        );

        $components = Mage::helper('M2ePro/Component')->getEnabledComponents();
        count($components) == 1 && $post['component'] = array_pop($components);

        $data = array();
        foreach ($keys as $key) {
            if (!isset($post[$key])) {
                $this->_getSession()->addError(Mage::helper('M2ePro')->__('You should fill in all required fields.'));
                return $this->_redirect('*/*/index');
            }

            $data[$key] = $post[$key];
        }

        $severity = isset($post['severity']) ? $post['severity'] : null;

        Mage::helper('M2ePro/Module_Support_Form')->send(
            $data['component'],
            $data['contact_mail'],
            $data['contact_name'],
            $data['subject'],
            $data['description'],
            $severity
        );

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Your message has been successfully sent.'));
        $this->_redirect('*/*/index');
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

    //----------------------------------------

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
