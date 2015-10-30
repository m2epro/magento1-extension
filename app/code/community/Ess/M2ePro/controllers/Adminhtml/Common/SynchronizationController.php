<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_SynchronizationController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
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
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/configuration');
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_common_configuration', '',
                    array(
                        'active_tab' => Ess_M2ePro_Block_Adminhtml_Common_Configuration_Tabs::TAB_ID_SYNCHRONIZATION
                    )
                )
            )->renderLayout();
    }

    //########################################

    public function saveAction()
    {
        $components = json_decode($this->getRequest()->getParam('components'));

        foreach ($components as $component) {

            if ($component == Ess_M2ePro_Helper_Component_Amazon::NICK) {

                Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
                    '/amazon/templates/', 'mode',
                    (int)$this->getRequest()->getParam('amazon_templates_mode')
                );
            } elseif ($component == Ess_M2ePro_Helper_Component_Buy::NICK) {

                Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
                    '/buy/templates/', 'mode',
                    (int)$this->getRequest()->getParam('buy_templates_mode'));
            }
        }
    }

    //########################################

    public function runAllEnabledNowAction()
    {
        session_write_close();

        /** @var $dispatcher Ess_M2ePro_Model_Synchronization_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');

        $dispatcher->setAllowedComponents(json_decode($this->getRequest()->getParam('components')));
        $dispatcher->setAllowedTasksTypes(array(
            Ess_M2ePro_Model_Synchronization_Task::DEFAULTS,
            Ess_M2ePro_Model_Synchronization_Task::TEMPLATES,
            Ess_M2ePro_Model_Synchronization_Task::ORDERS,
            Ess_M2ePro_Model_Synchronization_Task::OTHER_LISTINGS
        ));

        $dispatcher->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
        $dispatcher->setParams(array());

        $dispatcher->process();
    }

    //########################################

    public function synchCheckProcessingNowAction()
    {
        $warningMessages = array();

        $amazonProcessing = Mage::getModel('M2ePro/LockItem')->getCollection()
            ->addFieldToFilter('nick', array('like' => 'synchronization_amazon%'))
            ->getSize();

        $buyProcessing = Mage::getModel('M2ePro/LockItem')->getCollection()
            ->addFieldToFilter('nick', array('like' => 'synchronization_buy%'))
            ->getSize();

        if ($amazonProcessing > 0) {
            $warningMessages[] = Mage::helper('M2ePro')->__(
                'Data has been sent on Amazon. It is being processed now. You can continue working with M2E Pro.'
            );
        }

        if ($buyProcessing > 0) {
            $warningMessages[] = Mage::helper('M2ePro')->__(
                'Data has been sent on Rakuten.com. It is being processed now. You can continue working with M2E Pro.'
            );
        }

        return $this->getResponse()->setBody(json_encode(array(
            'messages' => $warningMessages
        )));
    }

    //########################################

    public function runReviseAllAction()
    {
        $startDate = Mage::helper('M2ePro')->getCurrentGmtDate();
        $component = $this->getRequest()->getParam('component');

        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
            "/{$component}/templates/revise/total/", 'start_date', $startDate
        );
        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
            "/{$component}/templates/revise/total/", 'last_listing_product_id', 0
        );

        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        $this->getResponse()->setBody(json_encode(array(
            'start_date' => Mage::app()->getLocale()->date(strtotime($startDate))->toString($format)
        )));

    }

    //########################################
}