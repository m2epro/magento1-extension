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
             ->_title(Mage::helper('M2ePro')->__('Support'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/SupportHandler.js')
            ->addJs('M2ePro/Development/ControlPanelHandler.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addCss('M2ePro/css/Plugin/DropDown.css');

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

    // ---------------------------------------

    public function documentationAction()
    {
        $url = Mage::helper('M2ePro/Module_Support')->getDocumentationUrl();

        $html = '<iframe src="' .$url . '" width="100%" height="650"></iframe>';
        $this->getResponse()->setBody($html);
    }

    public function knowledgeBaseAction()
    {
        $url = $this->getRequest()->getParam('url');
        if (is_null($url)) {
            $url = Mage::helper('M2ePro/Module_Support')->getKnowledgeBaseUrl();
        } else {
            $url = base64_decode($url);
        }

        $html = '<iframe src="' . $url . '" width="100%" height="650"></iframe>';
        $this->getResponse()->setBody($html);
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

        $components = Mage::helper('M2ePro/Component')->getActiveComponents();
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

        Mage::helper('M2ePro/Module_Support_Form')->send($data['component'],
                                                         $data['contact_mail'],
                                                         $data['contact_name'],
                                                         $data['subject'],
                                                         $data['description'],
                                                         $severity);

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Your message has been successfully sent.'));
        $this->_redirect('*/*/index');
    }

    //########################################
}