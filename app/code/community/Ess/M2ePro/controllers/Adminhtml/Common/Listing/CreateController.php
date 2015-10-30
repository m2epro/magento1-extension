<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_Listing_CreateController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    protected $component;
    protected $sessionKeyPostfix = '_listing_create';

    //########################################

    protected function _initAction()
    {
        $component = $this->getComponent();
        $componentTitle = Mage::helper('M2ePro/Component_'.ucfirst($component))->getTitle();

        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
            ->_title(Mage::helper('M2ePro')->__('%component_title% Listings', $componentTitle));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addCss('M2ePro/css/Plugin/AutoComplete.css')
            ->addJs('M2ePro/Plugin/AutoComplete.js')

            ->addJs('M2ePro/Common/Listing/AddListingHandler.js')
            ->addJs('M2ePro/Common/Listing/SettingsHandler.js')
            ->addJs('M2ePro/Common/'.ucfirst($component).'/Listing/ChannelSettingsHandler.js');

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
            $this->getRequest()->setParam('clear',null);
            $this->_redirect('*/*/index',array('_current' => true, 'step' => 1));
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

        $this->_initAction();

        $component = $this->getComponent();
        if (!$component) {
            $this->setComponentPageHelpLink('Step+1%3A+General+Settings');
        } else {
            $this->setPageHelpLink($component, 'Step+1%3A+General+Settings');
        }

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_add_stepOne', '',
            array(
                'component' => $component
            )
        ));

        $this->renderLayout();
    }

    // ---------------------------------------

    protected function stepTwo()
    {
        if (is_null($this->getSessionValue('account_id'))) {
            $this->clearSession();
            $this->_redirect('*/*/index', array('_current' => true, 'step' => 1));
            return;
        }

        if ($this->getRequest()->isPost()) {

            $this->setSessionValue('marketplace_id', $this->getMarketplaceId());

            $dataKeys = $this->getStepTwoFields();

            $post = $this->getRequest()->getPost();
            foreach ($dataKeys as $key) {
                $this->setSessionValue($key, $post[$key]);
            }

            $this->_redirect('*/*/index',array('_current' => true, 'step'=>'3'));
            return;
        }

        $this->_initAction();

        $component = $this->getComponent();
        if (!$component) {
            $this->setComponentPageHelpLink('Step+2%3A+Selling+Settings');
        } else {
            $this->setPageHelpLink($component, 'Step+2%3A+Selling+Settings');
        }

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_add_stepTwo', '',
            array(
                'component' => $component
            )
        ));
        $this->renderLayout();
    }

    protected function getStepTwoFields() {

        switch ($this->getComponent()) {
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $vals = Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Add_Tabs_Selling::getDefaultFieldsValues();
                break;
            case Ess_M2ePro_Helper_Component_Buy::NICK:
                $vals = Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_Add_Tabs_Selling::getDefaultFieldsValues();
                break;
            default:
                return array();
                break;
        }

        return array_keys($vals);
    }

    // ---------------------------------------

    protected function stepThree()
    {
        if (is_null($this->getSessionValue('account_id'))) {
            $this->clearSession();
            $this->_redirect('*/*/index', array('_current' => true, 'step' => 1));
            return;
        }

        if ($this->getRequest()->isPost()) {

            $dataKeys = $this->getStepThreeFields();

            $post = $this->getRequest()->getPost();
            foreach ($dataKeys as $key) {
                $this->setSessionValue($key, $post[$key]);
            }

            $listing = $this->createListing();
            $this->clearSession();

            if ($this->isCreationModeListingOnly()) {
                // closing window for 3rd party products moving in new listing creation
                echo "<script>window.close();</script>";
                return;
            }

            return $this->_redirect(
                '*/adminhtml_common_listing_productAdd/index',  array(
                    'id' => $listing->getId(),
                    'component' => $this->getComponent(),
                    'new_listing' => 1
                )
            );
        }

        $this->_initAction();

        $component = $this->getComponent();
        if (!$component) {
            $this->setComponentPageHelpLink('Step+3%3A+Search+Settings');
        } else {
            $this->setPageHelpLink($component, 'Step+3%3A+Search+Settings');
        }

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_add_stepThree', '',
            array(
                'component' => $component
            )
        ));
        $this->renderLayout();
    }

    protected function getStepThreeFields() {

        switch ($this->getComponent()) {
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $vals = Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Add_Tabs_Search::getDefaultFieldsValues();
                break;
            case Ess_M2ePro_Helper_Component_Buy::NICK:
                $vals = Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_Add_Tabs_Search::getDefaultFieldsValues();
                break;
            default:
                return array();
                break;
        }

        return array_keys($vals);
    }

    //########################################

    protected function createListing()
    {
        $sessionData = $this->getSessionValue();

        // Add new Listing
        // ---------------------------------------
        $listing = Mage::helper('M2ePro/Component')
            ->getComponentModel($this->getComponent(), 'Listing')
            ->addData($sessionData)
            ->save();
        // ---------------------------------------

        // Set message to log
        // ---------------------------------------
        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode($listing->getComponentMode());
        $tempLog->addListingMessage(
            $listing->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_USER,
            NULL,
            Ess_M2ePro_Model_Listing_Log::ACTION_ADD_LISTING,
            // M2ePro_TRANSLATIONS
            // Listing was successfully Added
            'Listing was successfully Added',
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );
        // ---------------------------------------

        return $listing;
    }

    //########################################

    protected function getComponent()
    {
        return $this->getRequest()->getParam('component');
    }

    protected function getSessionKey()
    {
        return $this->getComponent().$this->sessionKeyPostfix;
    }

    //########################################

    protected function getMarketplaceId()
    {
        switch ($this->getComponent()) {
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $accountObj = Mage::helper('M2ePro/Component')
                    ->getCachedUnknownObject('Account',(int)$this->getSessionValue('account_id'));
                return (int)$accountObj->getMarketplaceId();
                break;

            case Ess_M2ePro_Helper_Component_Buy::NICK:
                return Ess_M2ePro_Helper_Component_Buy::MARKETPLACE_ID;
                break;

        }
    }

    //########################################

    protected function setSessionValue($key, $value)
    {
        $sessionData = $this->getSessionValue();
        $sessionData[$key] = $value;

        Mage::helper('M2ePro/Data_Session')->setValue($this->getSessionKey(), $sessionData);

        return $this;
    }

    protected function getSessionValue($key = NULL)
    {
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->getSessionKey());

        if (is_null($sessionData)) {
            $sessionData = array();
        }

        if (is_null($key)) {
            return $sessionData;
        }

        return isset($sessionData[$key]) ? $sessionData[$key] : NULL;
    }

    // ---------------------------------------

    private function clearSession()
    {
        Mage::helper('M2ePro/Data_Session')->setValue($this->getSessionKey(), NULL);
    }

    //########################################

    private function isCreationModeListingOnly()
    {
        return $this->getRequest()->getParam('creation_mode') ==
            Ess_M2ePro_Helper_View::LISTING_CREATION_MODE_LISTING_ONLY;
    }

    //########################################
}