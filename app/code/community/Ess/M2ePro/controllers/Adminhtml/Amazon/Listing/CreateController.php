<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Amazon_Listing_CreateController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_MainController
{
    protected $_sessionKey = 'amazon_listing_create';

    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
            ->_title(Mage::helper('M2ePro')->__('Listings'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')

            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/SynchProgress.js')
            ->addJs('M2ePro/TemplateManager.js')
            ->addJs('M2ePro/Amazon/Listing/Product/Add.js')
            ->addJs('M2ePro/Amazon/Listing/Create/General.js')
            ->addJs('M2ePro/Amazon/Listing/Create/General/MarketplaceSynchProgress.js')
            ->addJs('M2ePro/Amazon/Listing/Create/Selling.js')
            ->addJs('M2ePro/Amazon/Listing/Create/Search.js')
            ->addJs('M2ePro/Amazon/Listing/Settings.js');

        $this->_initPopUp();

        return $this;
    }

    //########################################

    public function indexAction()
    {
        // Check clear param
        // ---------------------------------------
        if ($this->getRequest()->getParam('clear')) {
            $this->clearSession();
            $this->getRequest()->setParam('clear', null);
            $this->_redirect('*/*/index', array('_current' => true, 'step' => 1));
            return;
        }

        // ---------------------------------------

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
            default:
                $this->clearSession();
                $this->_redirect('*/*/index', array('_current' => true, 'step' => 1));
                break;
        }
    }

    protected function stepOne()
    {
        if ($this->getRequest()->isPost()) {
            // save data
            $post = $this->getRequest()->getPost();
            // ---------------------------------------

            $this->setSessionValue('title', strip_tags($post['title']));
            $this->setSessionValue('account_id', (int)$post['account_id']);
            $this->setSessionValue('store_id', (int)$post['store_id']);

            $this->_redirect('*/*/index', array('_current' => true, 'step' => 2));
            return;
        }

        $this->setWizardStep('listingGeneral');

        $this->_initAction();

        $this->setPageHelpLink(null, null, "x/tYcVAQ");

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_create_general'));

        $this->renderLayout();
    }

    // ---------------------------------------

    protected function stepTwo()
    {
        if ($this->getSessionValue('account_id') === null) {
            $this->clearSession();
            $this->_redirect('*/*/index', array('_current' => true, 'step' => 1));
            return;
        }

        if ($this->getRequest()->isPost()) {
            $this->setSessionValue('marketplace_id', $this->getMarketplaceId());

            $dataKeys = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_amazon_listing_create_selling_form'
            )->getDefaultFieldsValues();

            $post = $this->getRequest()->getPost();
            foreach ($dataKeys as $key => $value) {
                $this->setSessionValue($key, $post[$key]);
            }

            $this->_redirect('*/*/index', array('_current' => true, 'step' => 3));
            return;
        }

        $this->setWizardStep('listingSelling');

        $this->_initAction();

        $this->setPageHelpLink(null, null, "x/tYcVAQ");

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_create_selling'));
        $this->renderLayout();
    }

    // ---------------------------------------

    protected function stepThree()
    {
        if ($this->getSessionValue('account_id') === null) {
            $this->clearSession();
            $this->_redirect('*/*/index', array('_current' => true, 'step' => 1));
            return;
        }

        if ($this->getRequest()->isPost()) {
            $dataKeys = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_amazon_listing_create_search_form'
            )->getDefaultFieldsValues();

            $post = $this->getRequest()->getPost();
            foreach ($dataKeys as $key => $value) {
                $this->setSessionValue($key, $post[$key]);
            }

            $listing = $this->createListing();
            $this->clearSession();

            if ($this->isCreationModeListingOnly()) {
                // closing window for 3rd party products moving in new listing creation
                return $this->getResponse()->setBody("<script>window.close();</script>");
            }

            return $this->_redirect(
                '*/adminhtml_amazon_listing_productAdd/index', array(
                    'id'          => $listing->getId(),
                    'new_listing' => 1,
                    'wizard'      => $this->getRequest()->getParam('wizard')
                )
            );
        }

        $this->setWizardStep('listingSearch');

        $this->_initAction();

        $this->setPageHelpLink(null, null, "x/tYcVAQ");

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_create_search'));
        $this->renderLayout();
    }

    //########################################

    protected function createListing()
    {
        $sessionData = $this->getSessionValue();

        if ($sessionData['restock_date_value'] === '') {
            $sessionData['restock_date_value'] = Mage::helper('M2ePro')->getCurrentGmtDate();
        } else {
            $sessionData['restock_date_value'] = Mage::helper('M2ePro')->gmtDateToTimezone(
                $sessionData['restock_date_value']
            );
        }

        $listing = Mage::helper('M2ePro/Component')->getComponentModel('amazon', 'Listing')
            ->addData($sessionData)
            ->save();

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode($listing->getComponentMode());
        $actionId = $tempLog->getResource()->getNextActionId();
        $tempLog->addListingMessage(
            $listing->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_USER,
            $actionId,
            Ess_M2ePro_Model_Listing_Log::ACTION_ADD_LISTING,
            'Listing was successfully Added',
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE
        );
        // ---------------------------------------

        return $listing;
    }

    //########################################

    protected function getSessionKey()
    {
        return $this->_sessionKey;
    }

    //########################################

    protected function getMarketplaceId()
    {
        $accountObj = Mage::helper('M2ePro/Component')
            ->getCachedUnknownObject('Account', (int)$this->getSessionValue('account_id'));
        return (int)$accountObj->getMarketplaceId();
    }

    //########################################

    protected function setSessionValue($key, $value)
    {
        $sessionData = $this->getSessionValue();
        $sessionData[$key] = $value;

        Mage::helper('M2ePro/Data_Session')->setValue($this->getSessionKey(), $sessionData);

        return $this;
    }

    protected function getSessionValue($key = null)
    {
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->getSessionKey());

        if ($sessionData === null) {
            $sessionData = array();
        }

        if ($key === null) {
            return $sessionData;
        }

        return isset($sessionData[$key]) ? $sessionData[$key] : null;
    }

    // ---------------------------------------

    protected function clearSession()
    {
        Mage::helper('M2ePro/Data_Session')->setValue($this->getSessionKey(), null);
    }

    //########################################

    protected function setWizardStep($step)
    {
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!$wizardHelper->isActive(Ess_M2ePro_Helper_View_Amazon::WIZARD_INSTALLATION_NICK)) {
            return;
        }

        $wizardHelper->setStep(Ess_M2ePro_Helper_View_Amazon::WIZARD_INSTALLATION_NICK, $step);
    }

    //########################################

    protected function isCreationModeListingOnly()
    {
        return $this->getRequest()->getParam('creation_mode') ==
            Ess_M2ePro_Helper_View::LISTING_CREATION_MODE_LISTING_ONLY;
    }

    //########################################
}
