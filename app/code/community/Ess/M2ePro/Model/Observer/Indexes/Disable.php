<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_Indexes_Disable extends Ess_M2ePro_Model_Observer_Abstract
{
    //########################################

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

    //########################################
}