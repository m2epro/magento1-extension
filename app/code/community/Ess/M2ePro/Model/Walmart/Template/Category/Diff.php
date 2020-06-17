<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_Category_Diff extends Ess_M2ePro_Model_ActiveRecord_Diff
{
    //########################################

    public function isDifferent()
    {
        return $this->isDetailsDifferent();
    }

    public function isDetailsDifferent()
    {
        $mainKeys = array(
            'browsenode_id',
            'product_data_nick',
            'specifics'
        );

        return $this->isSettingsDifferent($mainKeys);
    }

    //########################################
}
