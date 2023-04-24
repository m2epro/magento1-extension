<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Walmart_Listing_Product
    extends Ess_M2ePro_Model_Resource_Component_Child_Abstract
{
    const IS_STOPPED_MANUALLY_FIELD = 'is_stopped_manually';

    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Walmart_Listing_Product', 'listing_product_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################

    public function getProductsDataBySkus(
        array $skus = array(),
        array $filters = array(),
        array $columns = array()
    ) {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
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

    public function mapChannelItemProduct(Ess_M2ePro_Model_Walmart_Listing_Product $listingProduct)
    {
        $walmartItemTable = Mage::getResourceModel('M2ePro/Walmart_Item')->getMainTable();
        $existedRelation = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(array('ei' => $walmartItemTable))
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
            $walmartItemTable,
            array('product_id' => $listingProduct->getParentObject()->getProductId()),
            array(
                'account_id = ?' => $listingProduct->getListing()->getAccountId(),
                'marketplace_id = ?' => $listingProduct->getListing()->getMarketplaceId(),
                'sku = ?' => $listingProduct->getSku(),
                'product_id = ?' => $listingProduct->getParentObject()->getOrigData('product_id')
            )
        );
    }

    public function moveChildrenToListing($listingProduct)
    {
        /** @var Varien_Db_Adapter_Pdo_Mysql $connection */
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $select = $connection->select();
        $select->join(
            array('wlp' => $this->getMainTable()),
            'lp.id = wlp.listing_product_id',
            null
        );
        $select->join(
            array('parent_lp' => Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable()),
            'parent_lp.id = wlp.variation_parent_id',
            array('listing_id' => 'parent_lp.listing_id')
        );
        $select->where('wlp.variation_parent_id = ?', $listingProduct->getId());

        $updateQuery = $connection->updateFromSelect(
            $select,
            array('lp' => Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable())
        );

        $connection->query($updateQuery);
    }

    //########################################
}
