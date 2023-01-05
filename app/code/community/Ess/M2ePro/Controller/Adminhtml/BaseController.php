<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Controller_Adminhtml_BaseController
    extends Mage_Adminhtml_Controller_Action
{
    protected $_generalBlockWasAppended = false;

    protected $_pageHelpLink = null;

    protected $_isUnAuthorized = false;

    //########################################

    public function indexAction()
    {
        $this->_redirect(Mage::helper('M2ePro/Module_Support')->getPageRoute());
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isLoggedIn();
    }

    //########################################

    protected function setPageHelpLink($component = null, $article = null, $tinyLink = null)
    {
        $this->_pageHelpLink = Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(
            $component, $article, $tinyLink
        );
    }

    protected function getPageHelpLink()
    {
        if ($this->_pageHelpLink === null) {
            return Mage::helper('M2ePro/Module_Support')->getDocumentationUrl();
        }

        return $this->_pageHelpLink;
    }

    //########################################

    final public function preDispatch()
    {
        parent::preDispatch();

        /**
         * Custom implementation of APPSEC-1034 (SUPEE-6788) [see additional information below].
         * M2E Pro prevents redirect to Magento Admin Panel login page.
         *
         * This PHP class is the base PHP class of all M2E Pro controllers.
         * Thus, it protects any action of any controller of M2E Pro extension.
         *
         * The code below is the logical extension of the method \Ess_M2ePro_Controller_Router::addModule.
         */
        if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
            $this->_isUnAuthorized = true;

            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);
            $this->setFlag('', self::FLAG_NO_PRE_DISPATCH, true);

            if ($this->getRequest()->isXmlHttpRequest()) {
                return $this->getResponse()->setBody(
                    json_encode(
                        array(
                        'ajaxExpired'  => 1,
                        'ajaxRedirect' => Mage::getBaseUrl()
                        )
                    )
                );
            }

            return $this->getResponse()->setRedirect(Mage::getBaseUrl());
        }

        Mage::helper('M2ePro/Module_Exception')->setFatalErrorHandler();

        if (Mage::helper('M2ePro/Data_Global')->getValue('is_base_controller_loaded') === null) {
            Mage::helper('M2ePro/Data_Global')->setValue('is_base_controller_loaded', true);
        }

        $this->_preDispatch();

        return $this;
    }

    final public function dispatch($action)
    {
        try {
            parent::dispatch($action);
        } catch (Exception $exception) {
            if ($this->_isUnAuthorized) {
                throw $exception;
            }

            if ($this->getRequest()->getControllerName() ==
                Mage::helper('M2ePro/Module_Support')->getPageControllerName()) {
                return $this->getResponse()->setBody($exception->getMessage());
            } else {
                if (Mage::helper('M2ePro/Module')->isDevelopmentEnvironment()) {
                    throw $exception;
                } else {
                    Mage::helper('M2ePro/Module_Exception')->process($exception);

                    if (($this->getRequest()->isGet() || $this->getRequest()->isPost()) &&
                        !$this->getRequest()->isXmlHttpRequest()) {
                        $this->_getSession()->addError(
                            Mage::helper('M2ePro/Module_Exception')->getUserMessage($exception)
                        );

                        $params = array(
                            'error' => 'true'
                        );

                        if (Mage::helper('M2ePro/View')->getCurrentView() !== null) {
                            $params['referrer'] = Mage::helper('M2ePro/View')->getCurrentView();
                        }

                        $this->_redirect(Mage::helper('M2ePro/Module_Support')->getPageRoute(), $params);
                    } else {
                        return $this->getResponse()->setBody($exception->getMessage());
                    }
                }
            }
        }
    }

    final public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_isUnAuthorized) {
            return;
        }

        $this->_postDispatch();
    }

    //########################################

    protected function _preDispatch()
    {
        if (Mage::helper('M2ePro/Module_Maintenance')->isEnabled()) {
            return $this->_redirect('*/adminhtml_maintenance');
        }

        if (Mage::helper('M2ePro/Module')->isDisabled()) {
            return $this->_redirect('adminhtml/dashboard');
        }

        if (Mage::helper('M2ePro/Component')->getEnabledComponents() === array()) {
            return $this->_redirect('adminhtml/dashboard');
        }

        return null;
    }

    protected function _postDispatch()
    {
        // Removes garbage from the response's body
        ob_get_clean();
    }

    //########################################

    public function loadLayout($ids=null, $generateBlocks=true, $generateXml=true)
    {
        $customLayout = Ess_M2ePro_Helper_View::LAYOUT_NICK;
        is_array($ids) ? $ids[] = $customLayout : $ids = array('default',$customLayout);

        $layout = parent::loadLayout($ids, $generateBlocks, $generateXml);

        /** Messages must be added after layout was initialized */
        if (Mage::helper('M2ePro/Module')->isDisabled()) {
            $message = Mage::helper('M2ePro')->__(
                <<<HTML
                M2E Pro is disabled. Inventory and Order synchronization is not running. 
                The Module interface is unavailable.<br>
                You can enable the Module under <i>System > Configuration > M2E Pro > Module & Channels > Module
                 > Module Interface and Automatic Synchronization</i>.
HTML
            );
            $this->_getSession()->addError($message);
        }

        if (Mage::helper('M2ePro/Component')->getEnabledComponents() === array()) {
            $message = Mage::helper('M2ePro')->__(
                <<<HTML
                Channel Integrations are disabled. To start working with M2E Pro, go to 
                <i>System > Configuration > M2E Pro > Module & Channels > Channels</i>
                 and enable at least one Channel Integration.
HTML
            );
            $this->_getSession()->addError($message);
        }

        return $layout;
    }

    // ---------------------------------------

    protected function _addLeft(Mage_Core_Block_Abstract $block)
    {
        $this->appendGeneralBlock($this->getLayout()->getBlock('left'));
        $this->beforeAddLeftEvent();
        return $this->addLeft($block);
    }

    protected function _addContent(Mage_Core_Block_Abstract $block)
    {
        $this->appendGeneralBlock($this->getLayout()->getBlock('content'));
        $this->beforeAddContentEvent();
        return $this->addContent($block);
    }

    protected function _addAjaxContent($content)
    {
        $blockGeneral = Mage::helper('M2ePro/View')->getGeneralBlock();
        $blockGeneral->setPageHelpLink($this->getPageHelpLink());

        return $this->getResponse()->setBody($blockGeneral->toHtml() . $content);
    }

    protected function _addJsonContent(array $content)
    {
        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($content));
    }

    protected function _addRawContent($content)
    {
        return $this->getResponse()->setBody($content);
    }

    // ---------------------------------------

    protected function beforeAddLeftEvent()
    {
        return null;
    }

    protected function beforeAddContentEvent()
    {
        return null;
    }

    //########################################

    public function getSession()
    {
        return $this->_getSession();
    }

    protected function getRequestIds()
    {
        $id = $this->getRequest()->getParam('id');
        $ids = $this->getRequest()->getParam('ids');

        if ($id === null && $ids === null) {
            return array();
        }

        $requestIds = array();

        if ($ids !== null) {
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }

            $requestIds = (array)$ids;
        }

        if ($id !== null) {
            $requestIds[] = $id;
        }

        return array_filter($requestIds);
    }

    // ---------------------------------------

    protected function _initPopUp()
    {
        $themeFileName = 'prototype/windows/themes/magento.css';
        $themeLibFileName = 'lib/'.$themeFileName;
        $themeFileFound = false;
        $skinBaseDir = Mage::getDesign()->getSkinBaseDir(
            array(
                '_package' => Mage_Core_Model_Design_Package::DEFAULT_PACKAGE,
                '_theme' => Mage_Core_Model_Design_Package::DEFAULT_THEME,
            )
        );

        if (!$themeFileFound && is_file($skinBaseDir .'/'.$themeLibFileName)) {
            $themeFileFound = true;
            $this->getLayout()->getBlock('head')->addCss($themeLibFileName);
        }

        if (!$themeFileFound && is_file(Mage::getBaseDir().'/js/'.$themeFileName)) {
            $themeFileFound = true;
            $this->getLayout()->getBlock('head')->addItem('js_css', $themeFileName);
        }

        if (!$themeFileFound) {
            $this->getLayout()->getBlock('head')->addCss($themeLibFileName);
            $this->getLayout()->getBlock('head')->addItem('js_css', $themeFileName);
        }

        $this->getLayout()->getBlock('head')
            ->addJs('prototype/window.js')
            ->addItem('js_css', 'prototype/windows/themes/default.css');

        return $this;
    }

    //########################################

    protected function appendGeneralBlock(Mage_Core_Block_Abstract $block)
    {
        if ($this->_generalBlockWasAppended) {
            return;
        }

        $blockGeneral = Mage::helper('M2ePro/View')->getGeneralBlock();
        $blockGeneral->setPageHelpLink($this->getPageHelpLink());

        $block->append($blockGeneral);
        $this->_generalBlockWasAppended = true;
    }

    protected function addLeft(Mage_Core_Block_Abstract $block)
    {
        return parent::_addLeft($block);
    }

    protected function addContent(Mage_Core_Block_Abstract $block)
    {
        return parent::_addContent($block);
    }

    //########################################
}
