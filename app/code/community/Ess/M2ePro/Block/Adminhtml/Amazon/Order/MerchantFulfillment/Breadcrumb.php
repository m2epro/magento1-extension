<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Order_MerchantFulfillment_Breadcrumb
    extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonOrderMerchantFulfillmentBreadcrumb');
        // ---------------------------------------
        $this->setTemplate('M2ePro/amazon/order/merchant_fulfillment/breadcrumb.phtml');
    }

    //########################################
}