<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Ebay_Listing_Product
    extends Ess_M2ePro_Model_Resource_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Listing_Product', 'listing_product_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################

    public function getTemplateCategoryIds(array $listingProductIds)
    {
        $select = $this->_getReadAdapter()
                       ->select()
                       ->from(array('elp' => $this->getMainTable()))
                       ->reset(Zend_Db_Select::COLUMNS)
                       ->columns(array('template_category_id'))
                       ->where('listing_product_id IN (?)', $listingProductIds)
                       ->where('template_category_id IS NOT NULL');

        $ids = $select->query()->fetchAll(PDO::FETCH_COLUMN);

        return array_unique($ids);
    }

    public function getTemplateOtherCategoryIds(array $listingProductIds)
    {
        $select = $this->_getReadAdapter()
                       ->select()
                       ->from(array('elp' => $this->getMainTable()))
                       ->reset(Zend_Db_Select::COLUMNS)
                       ->columns(array('template_other_category_id'))
                       ->where('listing_product_id IN (?)', $listingProductIds)
                       ->where('template_other_category_id IS NOT NULL');

        $ids = $select->query()->fetchAll(PDO::FETCH_COLUMN);

        return array_unique($ids);
    }

    //########################################

    public function getChangedItems(
        array $attributes,
        $withStoreFilter = false
    ) {
        return Mage::getResourceModel('M2ePro/Listing_Product')->getChangedItems(
            $attributes,
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            $withStoreFilter
        );
    }

    public function getChangedItemsByListingProduct(
        array $attributes,
        $withStoreFilter = false
    ) {
        return Mage::getResourceModel('M2ePro/Listing_Product')->getChangedItemsByListingProduct(
            $attributes,
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            $withStoreFilter
        );
    }

    public function getChangedItemsByVariationOption(
        array $attributes,
        $withStoreFilter = false
    ) {
        return Mage::getResourceModel('M2ePro/Listing_Product')->getChangedItemsByVariationOption(
            $attributes,
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            $withStoreFilter
        );
    }

    //########################################
}
