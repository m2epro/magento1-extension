<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Ebay_Template_StoreCategory
    extends Ess_M2ePro_Model_Resource_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Template_StoreCategory', 'id');
    }

    //########################################

    public function loadByCategoryValue(Ess_M2ePro_Model_Ebay_Template_StoreCategory $object, $value, $mode, $accountId)
    {
        /** @var Ess_M2ePro_Model_Resource_Collection_Abstract $collection */
        $collection = $object->getCollection();
        $collection->addFieldToFilter('category_mode', $mode);
        $collection->addFieldToFilter('account_id', $accountId);

        $mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY
            ? $collection->addFieldToFilter('category_id', $value)
            : $collection->addFieldToFilter('category_attribute', $value);

        if ($firstItem = $collection->getFirstItem()) {
            $object->setData($firstItem->getData());
        }
    }

    //########################################
}
