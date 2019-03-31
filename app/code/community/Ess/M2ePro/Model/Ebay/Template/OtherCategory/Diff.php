<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_OtherCategory_Diff extends Ess_M2ePro_Model_Template_Diff_Abstract
{
    //########################################

    public function isDifferent()
    {
        return $this->isCategoriesDifferent();
    }

    //########################################

    public function isCategoriesDifferent()
    {
        $keys = array(
            'category_secondary_mode',
            'category_secondary_id',
            'category_secondary_path',
            'category_secondary_attribute',
            'store_category_main_mode',
            'store_category_main_id',
            'store_category_main_path',
            'store_category_main_attribute',
            'store_category_secondary_mode',
            'store_category_secondary_id',
            'store_category_secondary_path',
            'store_category_secondary_attribute',
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################
}