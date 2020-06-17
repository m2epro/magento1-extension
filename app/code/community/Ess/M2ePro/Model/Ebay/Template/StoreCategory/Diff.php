<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_StoreCategory_Diff extends Ess_M2ePro_Model_ActiveRecord_Diff
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
            'category_mode',
            'category_id',
            'category_path',
            'category_attribute',
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################
}
