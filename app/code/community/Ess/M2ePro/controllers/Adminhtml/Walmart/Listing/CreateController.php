<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Walmart_Listing_CreateController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    protected $_sessionKeyPostfix = '_listing_create';

    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
            ->_title(Mage::helper('M2ePro')->__('Listings'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addCss('M2ePro/css/Plugin/AutoComplete.css')
            ->addJs('M2ePro/Plugin/AutoComplete.js')

            ->addJs('M2ePro/TemplateHandler.js')
            ->addJs('M2ePro/Walmart/Listing/AddListingHandler.js')
            ->addJs('M2ePro/Walmart/Listing/SettingsHandler.js')
            ->addJs('M2ePro/Walmart/Listing/ChannelSettingsHandler.js');

        $this->_initPopUp();

        return $this;
    }

    //########################################

    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            $listing = $this->createListing();

            if ($this->isCreationModeListingOnly()) {
                // closing window for 3rd party products moving in new listing creation
                return $this->getResponse()->setBody("<script>window.close();</script>");
            }

            return $this->_redirect(
                '*/adminhtml_walmart_listing_productAdd/index', array(
                    'id' => $listing->getId(),
                    'new_listing' => 1
                )
            );
        }

        $this->_initAction();

        $this->setPageHelpLink(NULL, NULL, "x/L4taAQ");

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_add', ''));

        $this->renderLayout();
    }

    //########################################

    protected function createListing()
    {
        $post = $this->getRequest()->getPost();

        // Validate Templates / Account
        // ---------------------------------------
        $account = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Account', (int)$post['account_id']
        );
        $selling = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Template_SellingFormat', (int)$post['template_selling_format_id']
        );
        $synchronization = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Template_Synchronization', (int)$post['template_synchronization_id']
        );
        $description = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Template_Description', (int)$post['template_description_id']
        );
        // ---------------------------------------

        $post['marketplace_id'] = $account->getMarketplaceId();

        // Add new Listing
        // ---------------------------------------
        $listing = Mage::helper('M2ePro/Component')->getComponentModel('walmart', 'Listing')
            ->addData($post)
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

    protected function getSessionKey()
    {
        return 'walmart'.$this->_sessionKeyPostfix;
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

    protected function getSessionValue($key = NULL)
    {
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->getSessionKey());

        if ($sessionData === null) {
            $sessionData = array();
        }

        if ($key === null) {
            return $sessionData;
        }

        return isset($sessionData[$key]) ? $sessionData[$key] : NULL;
    }

    // ---------------------------------------

    protected function clearSession()
    {
        Mage::helper('M2ePro/Data_Session')->setValue($this->getSessionKey(), NULL);
    }

    //########################################

    protected function isCreationModeListingOnly()
    {
        return $this->getRequest()->getParam('creation_mode') ==
            Ess_M2ePro_Helper_View::LISTING_CREATION_MODE_LISTING_ONLY;
    }

    //########################################
}
