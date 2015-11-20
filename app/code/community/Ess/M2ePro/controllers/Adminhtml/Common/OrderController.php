<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_OrderController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Orders'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Plugin/ActionColumn.js')
             ->addJs('M2ePro/Order/Handler.js')
             ->addJs('M2ePro/Common/Amazon/Order/MerchantFulfillmentHandler.js')
             ->addJs('M2ePro/Order/Edit/ItemHandler.js');

        $this->_initPopUp();

        $this->setComponentPageHelpLink('Sales+and+Orders+Overview');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/orders');
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_order'));

        $this->renderLayout();
    }

    //########################################
}