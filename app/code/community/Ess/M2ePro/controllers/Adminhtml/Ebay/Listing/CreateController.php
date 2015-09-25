<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_Listing_CreateController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    protected $sessionKey = 'ebay_listing_create';

    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Creating A New M2E Pro Listing'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addJs('M2ePro/AttributeHandler.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/SynchProgressHandler.js')
            ->addJs('M2ePro/Ebay/Listing/MarketplaceSynchProgressHandler.js')
            ->addJs('M2ePro/TemplateHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Template/SwitcherHandler.js')
            ->addJs('M2ePro/Ebay/Template/PaymentHandler.js')
            ->addJs('M2ePro/Ebay/Template/ReturnHandler.js')
            ->addJs('M2ePro/Ebay/Template/ShippingHandler.js')
            ->addJs('M2ePro/Ebay/Template/SellingFormatHandler.js')
            ->addJs('M2ePro/Ebay/Template/DescriptionHandler.js')
            ->addJs('M2ePro/Ebay/Template/SynchronizationHandler.js');

        $this->_initPopUp();

        $this->setComponentPageHelpLink('Creation+of+new+M2E+Pro+Listing');

        if (Mage::helper('M2ePro/Magento')->isTinyMceAvailable()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/listings');
    }

    //#############################################

    public function indexAction()
    {
        $step = (int)$this->getRequest()->getParam('step');

        switch ($step) {
            case 1:
                $this->stepOne();
                break;
            case 2:
                $this->stepTwo();
                break;
            case 3:
                $this->stepThree();
                break;
            case 4:
                $this->stepFour();
                break;
            default:
                $this->clearSession();
                $this->_redirect('*/*/index', array('_current' => true, 'step' => 1));
                break;
        }
    }

    //#############################################

    private function stepOne()
    {
        if ($this->getRequest()->getParam('clear')) {
            $this->clearSession();
            $this->getRequest()->setParam('clear',null);
            $this->_redirect('*/*/index',array('_current' => true, 'step' => 1));
            return;
        }

        $this->setWizardStep('listingAccount');

        //------------------------------
        if ($this->getRequest()->isPost()) {

            // save data
            $post = $this->getRequest()->getPost();

            // clear session data if user came back to the first step and changed the marketplace
            //------------------------------
            if ($this->getSessionValue('marketplace_id')
                && (int)$this->getSessionValue('marketplace_id') != (int)$post['marketplace_id']
            ) {
                $this->clearSession();
            }
            //------------------------------

            $this->setSessionValue('listing_title', strip_tags($post['title']));
            $this->setSessionValue('account_id', (int)$post['account_id']);
            $this->setSessionValue('marketplace_id', (int)$post['marketplace_id']);
            $this->setSessionValue('store_id', (int)$this->getRequest()->getPost('store_id'));

            $this->_redirect('*/*/index', array('_current' => true, 'step' => 2));
            return;
        }
        //------------------------------
        $listingOnlyMode = Ess_M2ePro_Helper_View::LISTING_CREATION_MODE_LISTING_ONLY;
        if($this->getRequest()->getParam('creation_mode') == $listingOnlyMode) {
            $this->setSessionValue('creation_mode', $listingOnlyMode);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('ebay_listing_title', $this->getSessionValue('listing_title'));
        Mage::helper('M2ePro/Data_Global')->setValue('ebay_account_id', $this->getSessionValue('account_id'));
        Mage::helper('M2ePro/Data_Global')->setValue('ebay_marketplace_id', $this->getSessionValue('marketplace_id'));

        $this->_initAction();
        $this->setComponentPageHelpLink('Step+1%3A+General+Settings');

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_accountMarketplace'));
        $this->renderLayout();
    }

    //#############################################

    private function stepTwo()
    {
        // Check exist temp data
        //----------------------------
        if (is_null($this->getSessionValue('account_id'))
            ||
            is_null($this->getSessionValue('marketplace_id'))
        ) {
            $this->clearSession();
            $this->_redirect('*/*/index', array('_current' => true, 'step' => 1));
            return;
        }
        //----------------------------

        //----------------------------
        $this->setWizardStep('listingGeneral');
        //----------------------------

        $templateNicks = array(
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN,
        );

        //------------------------------
        if ($this->getRequest()->isPost()) {
            // save data
            $post = $this->getRequest()->getPost();

            foreach ($templateNicks as $nick) {
                $templateData = json_decode(base64_decode($post["template_{$nick}"]), true);

                $this->setSessionValue("template_id_{$nick}", $templateData['id']);
                $this->setSessionValue("template_mode_{$nick}", $templateData['mode']);
            }

            $this->_redirect('*/*/index', array('_current' => true, 'step' => 3));
            return;
        }
        //------------------------------

        //------------------------------
        $this->loadTemplatesDataFromSession();
        //------------------------------

        //------------------------------
        $data = array(
            'allowed_tabs' => array('general')
        );
        $content = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_template_edit');
        $content->setData($data);
        //------------------------------

        $this->_initAction();
        $this->setComponentPageHelpLink('Step+2%3A+Payment+and+Shipping+Settings');

        $this->_addContent($content);
        $this->renderLayout();
    }

    //#############################################

    private function stepThree()
    {
        // Check exist temp data
        //----------------------------
        if (is_null($this->getSessionValue('account_id'))
            ||
            is_null($this->getSessionValue('marketplace_id'))
        ) {
            $this->clearSession();
            $this->_redirect('*/*/index', array('_current' => true, 'step' => 1));
            return;
        }
        //----------------------------

        //----------------------------
        $this->setWizardStep('listingSelling');
        //----------------------------

        $templateNicks = array(
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT,
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION,
        );

        //------------------------------
        if ($this->getRequest()->isPost()) {
            // save data
            $post = $this->getRequest()->getPost();

            foreach ($templateNicks as $nick) {
                //------------------------------
                $templateData = json_decode(base64_decode($post["template_{$nick}"]), true);
                //------------------------------

                $this->setSessionValue("template_id_{$nick}", $templateData['id']);
                $this->setSessionValue("template_mode_{$nick}", $templateData['mode']);
            }

            $this->_redirect('*/*/index', array('_current' => true, 'step' => 4));
            return;
        }
        //------------------------------

        //------------------------------
        $this->loadTemplatesDataFromSession();
        //------------------------------

        //------------------------------
        $data = array(
            'allowed_tabs' => array('selling')
        );
        $content = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_template_edit');
        $content->setData($data);
        //------------------------------

        $this->_initAction();
        $this->setComponentPageHelpLink('Step+3%3A+Selling+Settings');

        $this->_addContent($content);
        $this->renderLayout();
    }

    //#############################################

    private function stepFour()
    {
        // Check exist temp data
        //----------------------------
        if (is_null($this->getSessionValue('account_id'))
            ||
            is_null($this->getSessionValue('marketplace_id'))
        ) {
            $this->clearSession();
            $this->_redirect('*/*/index', array('step' => 1,'_current' => true));
            return;
        }
        //----------------------------

        //----------------------------
        $this->setWizardStep('listingSynchronization');
        //----------------------------

        $templateNicks = array(
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION,
        );

        //------------------------------
        if ($this->getRequest()->isPost()) {
            // save data
            $post = $this->getRequest()->getPost();

            foreach ($templateNicks as $nick) {
                $templateData = json_decode(base64_decode($post["template_{$nick}"]), true);

                $this->setSessionValue("template_id_{$nick}", $templateData['id']);
                $this->setSessionValue("template_mode_{$nick}", $templateData['mode']);
            }

            //------------------------------
            $listing = $this->createListing();

            if($this->isCreationModeListingOnly()) {
                // closing window for 3rd party products moving in new listing creation
                echo "<script>window.close();</script>";
                return;
            }

            $this->clearSession();
            //------------------------------

            if ((bool)$this->getRequest()->getParam('wizard',false)) {
                $this->setWizardStep('productTutorial');
                return $this->_redirect('*/adminhtml_wizard_installationEbay');
            }

            return $this->_redirect(
                '*/adminhtml_ebay_listing_productAdd/sourceMode',
                array(
                    'listing_id' => $listing->getId(),
                    'listing_creation' => true
                )
            );
        }
        //------------------------------

        //------------------------------
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {

            //------------------------------
            $synchTemplate = $this->createDefaultSynchronizationTemplate();
            //------------------------------

            //------------------------------
            $templateMode = Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM;
            $nick = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION;

            $this->setSessionValue("template_id_{$nick}", (int)$synchTemplate->getId());
            $this->setSessionValue("template_mode_{$nick}", $templateMode);
            //------------------------------

            //------------------------------
            $listing = $this->createListing();
            $this->clearSession();
            //------------------------------

            if ((bool)$this->getRequest()->getParam('wizard',false)) {
                $this->setWizardStep('productTutorial');
                return $this->_redirect('*/adminhtml_wizard_installationEbay');
            }

            return $this->_redirect(
                '*/adminhtml_ebay_listing_productAdd/sourceMode',
                array(
                    'listing_id' => $listing->getId(),
                    'listing_creation' => true
                )
            );
        }
        //------------------------------

        //------------------------------
        $this->loadTemplatesDataFromSession();
        //------------------------------

        //------------------------------
        $data = array(
            'allowed_tabs' => array('synchronization')
        );
        $content = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_template_edit');
        $content->setData($data);
        //------------------------------

        $this->_initAction();
        $this->setPageHelpLink(NULL, 'pages/viewpage.action?pageId=17367081');

        $this->_addContent($content);
        $this->renderLayout();
    }

    //#############################################

    private function createDefaultSynchronizationTemplate()
    {
        $data = Mage::getModel('M2ePro/Ebay_Template_Synchronization')->getDefaultSettingsSimpleMode();
        $data['title'] = $this->getSessionValue('listing_title');
        $data['is_custom_template'] = 1;

        $builder = Mage::getSingleton('M2ePro/Ebay_Template_Manager')
            ->setTemplate(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION)
            ->getTemplateBuilder();
        $template = $builder->build($data);

        return $template;
    }

    //#############################################

    private function createListing()
    {
        $data = array();
        $data['title'] = $this->getSessionValue('listing_title');
        $data['account_id'] = $this->getSessionValue('account_id');
        $data['marketplace_id'] = $this->getSessionValue('marketplace_id');
        $data['store_id'] = $this->getSessionValue('store_id');

        foreach(Mage::getSingleton('M2ePro/Ebay_Template_Manager')->getAllTemplates() as $nick) {
            $manager = Mage::getModel('M2ePro/Ebay_Template_Manager')
                ->setTemplate($nick);

            $templateId = $this->getSessionValue("template_id_{$nick}");
            $templateMode = $this->getSessionValue("template_mode_{$nick}");

            $idColumn = $manager->getIdColumnNameByMode($templateMode);
            $modeColumn = $manager->getModeColumnName();

            $data[$idColumn] = $templateId;
            $data[$modeColumn] = $templateMode;
        }

        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Listing');
        $model->addData($data);
        $model->save();

        return $model;
    }

    //#############################################

    private function loadTemplatesDataFromSession()
    {
        //------------------------------
        $listingTitle = $this->getSessionValue('listing_title');
        Mage::helper('M2ePro/Data_Global')->setValue('ebay_custom_template_title', $listingTitle);

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Switcher_DataLoader $dataLoader */
        $dataLoader = Mage::getBlockSingleton('M2ePro/adminhtml_ebay_listing_template_switcher_dataLoader');
        $dataLoader->load(Mage::helper('M2ePro/Data_Session'), array('session_key' => $this->sessionKey));
        //------------------------------
    }

    //#############################################

    protected function setSessionValue($key, $value)
    {
        $sessionData = $this->getSessionValue();
        $sessionData[$key] = $value;

        Mage::helper('M2ePro/Data_Session')->setValue($this->sessionKey, $sessionData);

        return $this;
    }

    protected function getSessionValue($key = NULL)
    {
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey);

        if (is_null($sessionData)) {
            $sessionData = array();
        }

        if (is_null($key)) {
            return $sessionData;
        }

        return isset($sessionData[$key]) ? $sessionData[$key] : NULL;
    }

    //#############################################

    private function clearSession()
    {
        Mage::helper('M2ePro/Data_Session')->setValue($this->sessionKey, NULL);
    }

    //#############################################

    private function setWizardStep($step)
    {
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!$wizardHelper->isActive(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK)) {
            return;
        }

        $wizardHelper->setStep(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK,$step);
    }

    //#############################################

    private function isCreationModeListingOnly()
    {
        return $this->getSessionValue('creation_mode') ===
        Ess_M2ePro_Helper_View::LISTING_CREATION_MODE_LISTING_ONLY;
    }

    //#############################################
}