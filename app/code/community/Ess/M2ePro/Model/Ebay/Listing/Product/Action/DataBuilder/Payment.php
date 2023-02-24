<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Payment
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Abstract
{
    const PAYPAL = 'PayPal';

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Payment
     */
    protected $_paymentTemplate = null;

    //########################################

    public function getData()
    {
        $data = array();

        if ($payPalData = $this->getPayPalData($data)) {
            $data['paypal'] = $payPalData;
        }

        return array('payment' => $data);
    }

    //########################################

    protected function getPayPalData($methods)
    {
        return array(
            'immediate_payment' => $this->getPaymentTemplate()->isPayPalImmediatePaymentEnabled()
        );
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Payment
     */
    protected function getPaymentTemplate()
    {
        if ($this->_paymentTemplate === null) {
            $this->_paymentTemplate = $this->getListingProduct()
                                           ->getChildObject()
                                           ->getPaymentTemplate();
        }

        return $this->_paymentTemplate;
    }

    //########################################
}
