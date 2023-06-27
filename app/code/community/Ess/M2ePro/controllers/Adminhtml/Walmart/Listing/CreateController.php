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
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')

            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/SynchProgress.js')
            ->addJs('M2ePro/TemplateManager.js')
            ->addJs('M2ePro/Walmart/Listing/Product/Add.js')
            ->addJs('M2ePro/Walmart/Listing/Settings.js')
            ->addJs('M2ePro/Walmart/Listing/Create/General.js')
            ->addJs('M2ePro/Walmart/Listing/Create/General/MarketplaceSynchProgress.js');

        $this->_initPopUp();

        return $this;
    }

    //########################################

    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            $listing = $this->createListing();

            if ($this->isCreationModeListingOnly()) {
                // closing window for Unmanaged products moving in new listing creation
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
        $this->setPageHelpLink(null, null, "walmart-integration");

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_create'));

        $this->renderLayout();
    }

    //########################################

    protected function createListing()
    {
        $post = $this->getRequest()->getPost();

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

        $post['marketplace_id'] = $account->getMarketplaceId();

        $listing = Mage::helper('M2ePro/Component')->getComponentModel('walmart', 'Listing')
            ->addData($post)
            ->save();

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode($listing->getComponentMode());
        $actionId = $tempLog->getResource()->getNextActionId();
        $tempLog->addListingMessage(
            $listing->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_USER,
            $actionId,
            Ess_M2ePro_Model_Listing_Log::ACTION_ADD_LISTING,
            'Listing was Added',
            Ess_M2ePro_Model_Log_Abstract::TYPE_INFO
        );

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

    protected function isCreationModeListingOnly()
    {
        return $this->getRequest()->getParam('creation_mode') ==
            Ess_M2ePro_Helper_View::LISTING_CREATION_MODE_LISTING_ONLY;
    }

    //########################################
}
