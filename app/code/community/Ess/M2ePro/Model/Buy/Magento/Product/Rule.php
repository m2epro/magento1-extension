<?php

class Ess_M2ePro_Model_Buy_Magento_Product_Rule extends Ess_M2ePro_Model_Magento_Product_Rule
{
    // ####################################

    public function getConditionClassName()
    {
        return 'M2ePro/Buy_Magento_Product_Rule_Condition_Combine';
    }

    // ####################################
}