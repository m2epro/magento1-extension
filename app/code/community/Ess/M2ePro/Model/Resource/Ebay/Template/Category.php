<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Ebay_Template_Category
    extends Ess_M2ePro_Model_Resource_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Template_Category', 'id');
        $this->addUniqueField(
            array(
                'field' => array(
                    'marketplace_id',
                    'category_id',
                    'category_attribute',
                    'is_custom_template'
                ),
                'title' => Mage::helper('M2ePro')->__('CategoryTemplate with same data')
            )
        );
    }

    //########################################

    public function loadByCategoryValue(
        Ess_M2ePro_Model_Ebay_Template_Category $object,
        $value,
        $mode,
        $marketplaceId,
        $isCustomTemplate = null
    ) {
        /** @var Ess_M2ePro_Model_Resource_Collection_Abstract $collection */
        $collection = $object->getCollection();
        $collection->addFieldToFilter('category_mode', $mode);
        $collection->addFieldToFilter('marketplace_id', $marketplaceId);

        if ($isCustomTemplate !== null) {
            $collection->addFieldToFilter('is_custom_template', (int)$isCustomTemplate);
        }

        $mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY
            ? $collection->addFieldToFilter('category_id', $value)
            : $collection->addFieldToFilter('category_attribute', $value);

        // @codingStandardsIgnoreLine
        if ($firstItem = $collection->getFirstItem()) {
            $object->setData($firstItem->getData());
        }
    }

    //########################################

    protected function _checkUnique(Mage_Core_Model_Abstract $object)
    {
        if ($object->getData('is_custom_template')) {
            return $this;
        }

        return parent::_checkUnique($object);
    }

    //########################################
}
