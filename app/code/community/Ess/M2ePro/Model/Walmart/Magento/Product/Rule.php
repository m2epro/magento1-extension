<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Magento_Product_Rule extends Ess_M2ePro_Model_Magento_Product_Rule
{
    //########################################

    /**
     * @return string
     */
    public function getConditionClassName()
    {
        return 'M2ePro/Walmart_Magento_Product_Rule_Condition_Combine';
    }

    //########################################
}