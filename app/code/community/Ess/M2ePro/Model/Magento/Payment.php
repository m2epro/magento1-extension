<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'm2epropayment';

    protected $_canUseCheckout = false;
    protected $_canUseInternal = false;
    protected $_canUseForMultishipping = false;

    protected $_infoBlockType = 'M2ePro/adminhtml_magento_payment_info';

    // ########################################

    public function assignData($data)
    {
        if ($data instanceof Varien_Object) {
            $data = $data->getData();
        }

        $details = array(
            'component_mode'    => $data['component_mode'],
            'payment_method'    => $data['payment_method'],
            'channel_order_id'  => $data['channel_order_id'],
            'channel_final_fee' => $data['channel_final_fee'],
            'transactions'      => $data['transactions'],
            'tax_id'            => isset($data['tax_id']) ? $data['tax_id'] : null,
        );

        $this->getInfoInstance()->setAdditionalData(serialize($details));

        return $this;
    }

    // ########################################
}