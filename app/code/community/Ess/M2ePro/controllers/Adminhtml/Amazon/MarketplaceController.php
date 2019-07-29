<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Amazon_MarketplaceController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_MainController
{
    const SYNCHRONIZATION_LOCK_ITEM_NICK = 'amazon_marketplace_synchronization';

    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('Marketplaces'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Plugin/ProgressBar.js')
             ->addCss('M2ePro/css/Plugin/ProgressBar.css')
             ->addJs('M2ePro/Plugin/AreaWrapper.js')
             ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
             ->addJs('M2ePro/SynchProgressHandler.js')
             ->addJs('M2ePro/Amazon/Marketplace/SynchProgressHandler.js')
             ->addJs('M2ePro/MarketplaceHandler.js');

        $this->setPageHelpLink(NULL, NULL, "x/ioIVAQ");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Amazon::MENU_ROOT_NODE_NICK . '/configuration'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_amazon_configuration', '',
                    array('active_tab' => Ess_M2ePro_Block_Adminhtml_Amazon_Configuration_Tabs::TAB_ID_MARKETPLACE)
                )
            )->renderLayout();
    }

    public function saveAction()
    {
        $marketplaces = Mage::getModel('M2ePro/Marketplace')->getCollection();

        foreach ($marketplaces as $marketplace) {
            $newStatus = $this->getRequest()->getParam('status_'.$marketplace->getId());

            if (is_null($newStatus)) {
                 continue;
            }
            if ($marketplace->getStatus() == $newStatus) {
                continue;
            }
            $marketplace->setData('status', $newStatus)->save();
        }
    }

    //########################################

    public function runSynchNowAction()
    {
        session_write_close();

        $marketplaceId = (int)$this->getRequest()->getParam('marketplace_id');
        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = Mage::helper('M2ePro/Component')->getUnknownObject('Marketplace',$marketplaceId);

        $lockItemManager = Mage::getModel('M2ePro/Lock_Item_Manager', array(
            'nick' => self::SYNCHRONIZATION_LOCK_ITEM_NICK,
        ));

        if ($lockItemManager->isExist()) {
            return;
        }

        $lockItemManager->create();

        $progressManager = Mage::getModel('M2ePro/Lock_Item_Progress', array(
            'lock_item_manager' => $lockItemManager,
            'progress_nick'     => $marketplace->getTitle() . ' Marketplace',
        ));

        $synchronization = Mage::getModel('M2ePro/Amazon_Marketplace_Synchronization');
        $synchronization->setMarketplace($marketplace);
        $synchronization->setProgressManager($progressManager);

        $synchronization->process();

        $lockItemManager->remove();
    }

    public function synchGetExecutingInfoAction()
    {
        $response = array();

        $lockItemManager = Mage::getModel('M2ePro/Lock_Item_Manager', array(
            'nick' => self::SYNCHRONIZATION_LOCK_ITEM_NICK,
        ));

        if (!$lockItemManager->isExist()) {
            $response['mode'] = 'inactive';
        } else {
            $response['mode'] = 'executing';

            $contentData = $lockItemManager->getContentData();
            $progressData = $contentData[Ess_M2ePro_Model_Lock_Item_Progress::CONTENT_DATA_KEY];

            if (!empty($progressData)) {
                $response['title'] = 'Marketplace Synchronization';
                $response['percents'] = $progressData[key($progressData)]['percentage'];
                $response['status'] = key($progressData);
            }
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($response));
    }

    //########################################
}