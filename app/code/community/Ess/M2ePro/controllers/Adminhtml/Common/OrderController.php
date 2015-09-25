<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_OrderController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Orders'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Order/Handler.js')
             ->addJs('M2ePro/Order/Edit/ItemHandler.js');

        $this->_initPopUp();

        $this->setComponentPageHelpLink('Sales+and+Orders+Overview');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/orders');
    }

    //#############################################

    public function preDispatch()
    {
        parent::preDispatch();

        Mage::getSingleton('M2ePro/Order_Log_Manager')
            ->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_order'));

        $this->renderLayout();
    }

    //#############################################
}