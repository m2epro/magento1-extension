<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_SynchronizationController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Synchronization'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addJs('M2ePro/SynchProgressHandler.js')
            ->addJs('M2ePro/SynchronizationHandler.js');

        $this->_initPopUp();

        $this->setComponentPageHelpLink('Synchronization');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/configuration');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent(
                 $this->getLayout()->createBlock(
                     'M2ePro/adminhtml_ebay_configuration', '',
                     array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Tabs::TAB_ID_SYNCHRONIZATION)
                 )
             )->renderLayout();
    }

    //#############################################

    public function saveAction()
    {
        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
            '/ebay/templates/', 'mode',
            (int)$this->getRequest()->getParam('ebay_templates_mode')
        );
    }

    //#############################################

    public function runAllEnabledNowAction()
    {
        session_write_close();

        /** @var $dispatcher Ess_M2ePro_Model_Synchronization_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');

        $dispatcher->setAllowedComponents(array(Ess_M2ePro_Helper_Component_Ebay::NICK));

        $tasks = array(
            Ess_M2ePro_Model_Synchronization_Task::DEFAULTS,
            Ess_M2ePro_Model_Synchronization_Task::TEMPLATES,
            Ess_M2ePro_Model_Synchronization_Task::ORDERS,
            Ess_M2ePro_Model_Synchronization_Task::FEEDBACKS
        );
        if (Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            $tasks[] = Ess_M2ePro_Model_Synchronization_Task::OTHER_LISTINGS;
        }
        $dispatcher->setAllowedTasksTypes($tasks);

        $dispatcher->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
        $dispatcher->setParams(array());

        $dispatcher->process();
    }

    //#############################################

    public function synchCheckProcessingNowAction()
    {
        $warningMessages = array();

        $synchronizationEbayOtherListingsProcessing = Mage::getModel('M2ePro/LockItem')->getCollection()
            ->addFieldToFilter('nick', array('like' => 'synchronization_ebay_other_listings_update%'))
            ->getSize();

        // M2ePro_TRANSLATIONS
        // eBay 3rd Party Listings are being downloaded now. They will be available soon in %menu_root%. You can continue working with M2E Pro.
        if ($synchronizationEbayOtherListingsProcessing > 0) {
            $warningMessages[] = Mage::helper('M2ePro')->__(
                'eBay 3rd Party Listings are being downloaded now. ' .
                'They will be available soon in %menu_root%. ' .
                'You can continue working with M2E Pro.',
                Mage::helper('M2ePro/View_Ebay')->getPageNavigationPath('listings', '3rd Party')
            );
        }

        return $this->getResponse()->setBody(json_encode(array(
            'messages' => $warningMessages
        )));
    }

    //#############################################

    public function runReviseAllAction()
    {
        $startDate = Mage::helper('M2ePro')->getCurrentGmtDate();

        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
            '/ebay/templates/revise/total/', 'start_date', $startDate
        );
        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
            '/ebay/templates/revise/total/', 'last_listing_product_id', 0
        );

        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        $this->getResponse()->setBody(json_encode(array(
            'start_date' => Mage::app()->getLocale()->date(strtotime($startDate))->toString($format)
        )));
    }

    //#############################################
}