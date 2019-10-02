<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Payment_Diff extends Ess_M2ePro_Model_Template_Diff_Abstract
{
    //########################################

    public function isDifferent()
    {
        return $this->isPaymentDifferent();
    }

    //########################################

    public function isPaymentDifferent()
    {
        $keys = array(
            'pay_pal_mode',
            'pay_pal_email_address',
            'pay_pal_immediate_payment',
            'services',
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################
}
