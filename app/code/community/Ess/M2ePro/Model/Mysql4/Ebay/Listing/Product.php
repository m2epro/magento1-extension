<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Listing_Product
    extends Ess_M2ePro_Model_Mysql4_Component_Child_Abstract
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

    public function getChangedItems(array $attributes,
                                    $withStoreFilter = false)
    {
        return Mage::getResourceModel('M2ePro/Listing_Product')->getChangedItems(
            $attributes,
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            $withStoreFilter
        );
    }

    public function getChangedItemsByListingProduct(array $attributes,
                                                    $withStoreFilter = false)
    {
        return Mage::getResourceModel('M2ePro/Listing_Product')->getChangedItemsByListingProduct(
            $attributes,
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            $withStoreFilter
        );
    }

    public function getChangedItemsByVariationOption(array $attributes,
                                                     $withStoreFilter = false)
    {
        return Mage::getResourceModel('M2ePro/Listing_Product')->getChangedItemsByVariationOption(
            $attributes,
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            $withStoreFilter
        );
    }

    //########################################

    public function setSynchStatusNeedByCategoryTemplate($newData, $oldData, $listingProduct)
    {
        $newTemplateSnapshot = array();

        try {
            $newTemplateSnapshot = Mage::helper('M2ePro')
                ->getCachedObject('Ebay_Template_Category',
                                  $newData['template_category_id'],
                                  NULL,array('template'))
                ->getDataSnapshot();
        } catch (Exception $exception) {}

        $oldTemplateSnapshot = array();

        try {
            $oldTemplateSnapshot = Mage::helper('M2ePro')
                ->getCachedObject('Ebay_Template_Category',
                                  $oldData['template_category_id'],
                                  NULL,array('template'))
                ->getDataSnapshot();
        } catch (Exception $exception) {}

        if (!$newTemplateSnapshot && !$oldTemplateSnapshot) {
            return;
        }

        Mage::getResourceModel('M2ePro/Ebay_Template_Category')->setSynchStatusNeed(
            $newTemplateSnapshot,
            $oldTemplateSnapshot,
            array($listingProduct)
        );
    }

    public function setSynchStatusNeedByOtherCategoryTemplate($newData, $oldData, $listingProduct)
    {
        $newTemplateSnapshot = array();

        try {
            $newTemplateSnapshot = Mage::helper('M2ePro')
                ->getCachedObject('Ebay_Template_OtherCategory',
                                  $newData['template_other_category_id'],
                                  NULL,array('template'))
                ->getDataSnapshot();
        } catch (Exception $exception) {}

        $oldTemplateSnapshot = array();

        try {
            $oldTemplateSnapshot = Mage::helper('M2ePro')
                ->getCachedObject('Ebay_Template_OtherCategory',
                                  $oldData['template_other_category_id'],
                                  NULL, array('template'))
                ->getDataSnapshot();
        } catch (Exception $exception) {}

        if (!$newTemplateSnapshot && !$oldTemplateSnapshot) {
            return;
        }

        Mage::getResourceModel('M2ePro/Ebay_Template_OtherCategory')->setSynchStatusNeed(
            $newTemplateSnapshot,
            $oldTemplateSnapshot,
            array($listingProduct)
        );
    }

    //########################################
}
