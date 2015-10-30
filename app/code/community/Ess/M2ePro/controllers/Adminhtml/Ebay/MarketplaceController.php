<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_MarketplaceController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Sites'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/SynchProgressHandler.js')
            ->addJs('M2ePro/Ebay/MarketplaceSynchProgressHandler.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/MarketplaceHandler.js')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css');

        $this->setComponentPageHelpLink('eBay+Sites');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/configuration');
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent(
                 $this->getLayout()->createBlock(
                     'M2ePro/adminhtml_ebay_configuration', '',
                     array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Tabs::TAB_ID_MARKETPLACE)
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
        $marketplaceObj = Mage::helper('M2ePro/Component')->getUnknownObject('Marketplace',$marketplaceId);

        /** @var $dispatcher Ess_M2ePro_Model_Synchronization_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');

        $dispatcher->setAllowedComponents(array($marketplaceObj->getComponentMode()));
        $dispatcher->setAllowedTasksTypes(array(Ess_M2ePro_Model_Synchronization_Task::MARKETPLACES));

        $dispatcher->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
        $dispatcher->setParams(array('marketplace_id' => $marketplaceId));

        $dispatcher->process();
    }

    public function isExistDeletedCategoriesAction()
    {
        if (Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->isExistDeletedCategories()) {
            return $this->getResponse()->setBody('1');
        }

        return $this->getResponse()->setBody('0');
    }

    //########################################
}