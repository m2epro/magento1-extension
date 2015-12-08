<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Order_MerchantFulfillment_Message
    extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonOrderMerchantFulfillmentMessage');
        // ---------------------------------------
        $this->setTemplate('M2ePro/common/amazon/order/merchant_fulfillment/message.phtml');
    }

    //########################################
}