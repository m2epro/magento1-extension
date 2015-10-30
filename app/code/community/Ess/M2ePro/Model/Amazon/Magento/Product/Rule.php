<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Magento_Product_Rule extends Ess_M2ePro_Model_Magento_Product_Rule
{
    //########################################

    /**
     * @return string
     */
    public function getConditionClassName()
    {
        return 'M2ePro/Amazon_Magento_Product_Rule_Condition_Combine';
    }

    //########################################
}