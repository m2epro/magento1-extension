<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Template_ProductTaxCode_Diff extends Ess_M2ePro_Model_Template_Diff_Abstract
{
    //########################################

    public function isDifferent()
    {
        return $this->isDetailsDifferent();
    }

    //########################################

    public function isDetailsDifferent()
    {
        $keys = array(
            'product_tax_code_mode',
            'product_tax_code_value',
            'product_tax_code_attribute',
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################
}
