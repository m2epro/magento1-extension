<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Category_Diff extends Ess_M2ePro_Model_Template_Diff_Abstract
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
            'category_main_id',
            'category_main_mode',
            'category_main_path',
            'category_main_attribute',
            'specifics'
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################
}
