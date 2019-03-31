<?php
/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Magento_Stock extends Mage_Core_Helper_Abstract
{
    //########################################

    /**
     * @return bool
     */
    public function canSubtractQty()
    {
        return Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_CAN_SUBTRACT);
    }

    //########################################
}