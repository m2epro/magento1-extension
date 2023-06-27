<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_Listing_CreateController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Creating A New M2E Pro Listing'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addJs('M2ePro/Attribute.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/SynchProgress.js')
            ->addJs('M2ePro/Ebay/Listing/MarketplaceSynchProgress.js')
            ->addJs('M2ePro/TemplateManager.js')

            ->addJs('M2ePro/Ebay/Listing/Settings.js')
            ->addJs('M2ePro/Ebay/Listing/Create/General.js')
            ->addJs('M2ePro/Ebay/Listing/Create/General/MarketplaceSynchProgress.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "create-new-listing");

        if (Mage::helper('M2ePro/Magento')->isTinyMceAvailable()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK . '/listings'
        );
    }

    //########################################

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
            default:
                $this->clearSession();
                $this->_redirect('*/*/index', array('_current' => true, 'step' => 1));
                break;
        }
    }

    //########################################

    protected function stepOne()
    {
        if ($this->getRequest()->getParam('clear')) {
            $this->clearSession();
            $this->getRequest()->setParam('clear', null);
            $this->_redirect('*/*/index', array('_current' => true, 'step' => 1));
            return;
        }

        $this->setWizardStep('listingGeneral');

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            // clear session data if user came back to the first step and changed the marketplace
            // ---------------------------------------
            if ($this->getSessionValue('marketplace_id')
                && (int)$this->getSessionValue('marketplace_id') != (int)$post['marketplace_id']
            ) {
                $this->clearSession();
            }

            $this->setSessionValue('title', strip_tags($post['title']));
            $this->setSessionValue('account_id', (int)$post['account_id']);
            $this->setSessionValue('marketplace_id', (int)$post['marketplace_id']);
            $this->setSessionValue('store_id', (int)$this->getRequest()->getPost('store_id'));

            $this->_redirect('*/*/index', array('_current' => true, 'step' => 2));
            return;
        }

        $listingOnlyMode = Ess_M2ePro_Helper_View::LISTING_CREATION_MODE_LISTING_ONLY;
        if ($this->getRequest()->getParam('creation_mode') == $listingOnlyMode) {
            $this->setSessionValue('creation_mode', $listingOnlyMode);
        }

        $this->_initAction();
        $this->setPageHelpLink(null, null, "create-new-listing");

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_create_general'));
        $this->renderLayout();
    }

    protected function stepTwo()
    {
        if ($this->getSessionValue('account_id') === null ||
            $this->getSessionValue('marketplace_id') === null
        ) {
            $this->clearSession();
            $this->_redirect('*/*/index', array('_current' => true, 'step' => 1));
            return;
        }

        if ($this->getRequest()->isPost()) {
            $dataKeys = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_listing_create_templates_form'
            )->getDefaultFieldsValues();

            $post = $this->getRequest()->getPost();
            foreach ($dataKeys as $key => $value) {
                $this->setSessionValue($key, $post[$key]);
            }

            $listing = $this->createListing();

            //todo Transferring move in another place?
            if ($listingId = $this->getRequest()->getParam('listing_id')) {
                /** @var Ess_M2ePro_Model_Ebay_Listing_Transferring $transferring */
                $transferring = Mage::getModel('M2ePro/Ebay_Listing_Transferring');
                $transferring->setListing(
                    Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId)
                );

                $this->clearSession();
                $transferring->setTargetListingId($listing->getId());

                return $this->_redirect(
                    '*/adminhtml_ebay_listing_transferring/index',
                    array(
                        'listing_id' => $listingId,
                        'step'       => 3,
                    )
                );
            }

            if ($this->isCreationModeListingOnly()) {
                // closing window for Unmanaged products moving in new listing creation
                return $this->getResponse()->setBody("<script>window.close();</script>");
            }

            $this->clearSession();

            if ((bool)$this->getRequest()->getParam('wizard', false)) {
                $this->setWizardStep('sourceMode');
                return $this->_redirect('*/adminhtml_wizard_installationEbay');
            }

            return $this->_redirect(
                '*/adminhtml_ebay_listing_productAdd/sourceMode',
                array(
                    'listing_id'       => $listing->getId(),
                    'listing_creation' => true
                )
            );
        }

        $this->setWizardStep('listingTemplates');

        $this->_initAction();
        $this->setPageHelpLink(null, null, "create-new-listing");

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_create_templates'));
        $this->renderLayout();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function createListing()
    {
        $data = $this->getSessionValue();

        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Marketplace', $data['marketplace_id']);

        $data['parts_compatibility_mode'] = null;
        if ($marketplace->getChildObject()->isMultiMotorsEnabled()) {
            $data['parts_compatibility_mode'] = Ess_M2ePro_Model_Ebay_Listing::PARTS_COMPATIBILITY_MODE_KTYPES;
        }

        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Listing');
        $model->addData($data);
        $model->save();

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
        $tempLog->addListingMessage(
            $model->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_USER,
            $tempLog->getResource()->getNextActionId(),
            Ess_M2ePro_Model_Listing_Log::ACTION_ADD_LISTING,
            'Listing was Added',
            Ess_M2ePro_Model_Log_Abstract::TYPE_INFO
        );

        return $model;
    }

    //########################################

    protected function setSessionValue($key, $value)
    {
        $sessionData = $this->getSessionValue();
        $sessionData[$key] = $value;

        Mage::helper('M2ePro/Data_Session')->setValue(
            Ess_M2ePro_Model_Ebay_Listing::CREATE_LISTING_SESSION_DATA,
            $sessionData
        );
        return $this;
    }

    protected function getSessionValue($key = null)
    {
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue(
            Ess_M2ePro_Model_Ebay_Listing::CREATE_LISTING_SESSION_DATA
        );

        if ($sessionData === null) {
            $sessionData = array();
        }

        if ($key === null) {
            return $sessionData;
        }

        return isset($sessionData[$key]) ? $sessionData[$key] : null;
    }

    //########################################

    protected function clearSession()
    {
        Mage::helper('M2ePro/Data_Session')->setValue(Ess_M2ePro_Model_Ebay_Listing::CREATE_LISTING_SESSION_DATA, null);
    }

    //########################################

    protected function setWizardStep($step)
    {
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');
        if (!$wizardHelper->isActive(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK)) {
            return;
        }

        $wizardHelper->setStep(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK, $step);
    }

    //########################################

    protected function isCreationModeListingOnly()
    {
        return $this->getSessionValue('creation_mode') === Ess_M2ePro_Helper_View::LISTING_CREATION_MODE_LISTING_ONLY;
    }

    //########################################
}
