<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Indexes_Disable extends Ess_M2ePro_Model_Observer_Abstract
{
    //####################################

    public function process()
    {
        /** @var $index Ess_M2ePro_Model_Magento_Product_Index */
        $index = Mage::getSingleton('M2ePro/Magento_Product_Index');

        if (!$index->isIndexManagementEnabled()) {
            return;
        }

        foreach ($index->getIndexes() as $code) {
            if ($index->disableReindex($code)) {
                $index->rememberDisabledIndex($code);
            }
        }
    }

    //####################################
}