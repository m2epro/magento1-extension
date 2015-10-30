<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
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
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/help') ||
               Mage::getSingleton('admin/session')->isAllowed('m2epro_common/help');
    }

    public function loadLayout($ids=null, $generateBlocks=true, $generateXml=true)
    {
        $tempResult = parent::loadLayout($ids, $generateBlocks, $generateXml);

        if ($this->getRequest()->getParam('referrer') == Ess_M2ePro_Helper_View_Ebay::NICK) {
            $tempResult->_setActiveMenu(Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK);
            $tempResult->_title(Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel());
        }

        if ($this->getRequest()->getParam('referrer') == Ess_M2ePro_Helper_View_Common::NICK) {
            $tempResult->_setActiveMenu(Ess_M2ePro_Helper_View_Common::MENU_ROOT_NODE_NICK);
            $tempResult->_title(Mage::helper('M2ePro/View_Common')->getMenuRootNodeLabel());
        }

        return $tempResult;
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction();

        $referrer = $this->getRequest()->getParam('referrer');

        if ($referrer == Ess_M2ePro_Helper_Component_Ebay::NICK) {

            $this->setPageHelpLink(Ess_M2ePro_Helper_View_Ebay::NICK);

        } elseif ($referrer == Ess_M2ePro_Helper_View_Common::NICK) {

            $components = Mage::helper('M2ePro/View_Common_Component')->getActiveComponents();
            if (count($components) == 1) {
                $this->setPageHelpLink(array_shift($components));
            }
        }

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_support'))
             ->renderLayout();
    }

    //########################################

    public function getResultsHtmlAction()
    {
        $query = $this->getRequest()->getParam('query');
        $blockData = Mage::helper('M2ePro/Module_Support_Uservoice')->search($query);

        $blockHtml = $this->loadLayout()
                          ->getLayout()
                          ->createBlock('M2ePro/adminhtml_support_results', '', array('user_voice_data' => $blockData))
                          ->toHtml();

        $this->getResponse()->setBody($blockHtml);
    }

    // ---------------------------------------

    public function documentationAction()
    {
        $referrer = $this->getRequest()->getParam('referrer');

        $url = Mage::helper('M2ePro/Module_Support')->getDocumentationUrl();

        if ($referrer == Ess_M2ePro_Helper_View_Ebay::NICK) {

            $url = Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(
                Ess_M2ePro_Helper_Component_Ebay::NICK
            );

        } elseif ($referrer == Ess_M2ePro_Helper_View_Common::NICK) {

            $activeComponents = Mage::helper('M2ePro/View_Common_Component')->getActiveComponents();

            if (count($activeComponents) == 1) {
                $url = Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(array_shift($activeComponents));
            }
        }

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

    public function migrationNotesAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_wizard_migrationToV6_notes'))
             ->renderLayout();
    }

    //########################################
}