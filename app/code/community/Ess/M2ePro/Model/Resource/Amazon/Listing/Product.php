<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Amazon_Listing_Product
    extends Ess_M2ePro_Model_Resource_Component_Child_Abstract
{
    const IS_STOPPED_MANUALLY_FIELD = 'is_stopped_manually';

    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Listing_Product', 'listing_product_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################

    public function getProductsDataBySkus(
        array $skus = array(),
        array $filters = array(),
        array $columns = array()
    ) {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->getSelect()->joinLeft(
            array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            'l.id = main_table.listing_id',
            array()
        );

        if (!empty($skus)) {
            $skus = array_map(
                function($el){
                return (string)$el; 
                }, $skus
            );
            $listingProductCollection->addFieldToFilter('sku', array('in' => array_unique($skus)));
        }

        if (!empty($filters)) {
            foreach ($filters as $columnName => $columnValue) {
                $listingProductCollection->addFieldToFilter($columnName, $columnValue);
            }
        }

        if (!empty($columns)) {
            $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $listingProductCollection->getSelect()->columns($columns);
        }

        return $listingProductCollection->getData();
    }

    //########################################

    public function moveChildrenToListing(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        // Get child products ids
        // ---------------------------------------
        $dbSelect = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Amazon_Listing_Product')->getMainTable(),
                array('listing_product_id', 'sku')
            )
            ->where('`variation_parent_id` = ?', $listingProduct->getId());
        $products = $connRead->fetchPairs($dbSelect);

        if (!empty($products)) {
            $connWrite->update(
                Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable(),
                array(
                    'listing_id' => $listingProduct->getListing()->getId()
                ),
                '`id` IN (' . implode(',', array_keys($products)) . ')'
            );
        }

        $dbSelect = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Amazon_Item')->getMainTable(),
                array('id')
            )
            ->where('`account_id` = ?', $listingProduct->getListing()->getAccountId())
            ->where('`marketplace_id` = ?', $listingProduct->getListing()->getMarketplaceId())
            ->where('`sku` IN (?)', implode(',', array_values($products)));
        $items = $connRead->fetchCol($dbSelect);

        if (!empty($items)) {
            $connWrite->update(
                Mage::getResourceModel('M2ePro/Amazon_Item')->getMainTable(),
                array(
                    'store_id' => $listingProduct->getListing()->getStoreId()
                ),
                '`id` IN ('.implode(',', $items).')'
            );
        }
    }

    //########################################

    public function mapChannelItemProduct(Ess_M2ePro_Model_Amazon_Listing_Product $listingProduct)
    {
        $amazonItemTable = Mage::getResourceModel('M2ePro/Amazon_Item')->getMainTable();
        $existedRelation = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(array('ei' => $amazonItemTable))
            ->where('`account_id` = ?', $listingProduct->getListing()->getAccountId())
            ->where('`marketplace_id` = ?', $listingProduct->getListing()->getMarketplaceId())
            ->where('`sku` = ?', $listingProduct->getSku())
            ->where('`product_id` = ?', $listingProduct->getParentObject()->getProductId())
            ->query()
            ->fetchColumn();

        if ($existedRelation) {
            return;
        }

        $this->_getWriteAdapter()->update(
            $amazonItemTable,
            array('product_id' => $listingProduct->getParentObject()->getProductId()),
            array(
                'account_id = ?' => $listingProduct->getListing()->getAccountId(),
                'marketplace_id = ?' => $listingProduct->getListing()->getMarketplaceId(),
                'sku = ?' => $listingProduct->getSku(),
                'product_id = ?' => $listingProduct->getParentObject()->getOrigData('product_id')
            )
        );
    }

    //########################################
}
