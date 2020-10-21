<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_SynchronizeInventory_Walmart_BlockedProductsHandler
    extends Ess_M2ePro_Model_Listing_SynchronizeInventory_AbstractBlockedHandler
{
    //########################################

    /**
     * @return Zend_Db_Statement_Interface
     * @throws Exception
     */
    protected function getPdoStatementNotReceivedListingProducts()
    {
        /**
         * Wait for 24 hours before the newly listed item can be marked as inactive blocked
         */
        $borderDate = new DateTime('now', new \DateTimeZone('UTC'));
        $borderDate->modify('- 24 hours');

        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->getSelect()->join(
            array('l' => Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_listing')),
            'main_table.listing_id = l.id',
            array()
        );
        $collection->getSelect()->joinLeft(
            array('wiw' => $this->getComponentInventoryTable()),
            'second_table.wpid = wiw.wpid AND l.account_id = wiw.account_id',
            array()
        );

        $collection->addFieldToFilter('l.account_id', (int)$this->getAccount()->getId());
        $collection->addFieldToFilter(
            'status', array('nin' => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
                Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED
            ))
        );
        $collection->addFieldToFilter('is_variation_parent', array('neq' => 1));
        $collection->addFieldToFilter('is_missed_on_channel', array('neq' => 1));
        $collection->addFieldToFilter(
            new \Zend_Db_Expr('list_date IS NULL OR list_date'), array('lt' => $borderDate->format('Y-m-d H:i:s'))
        );
        $collection->getSelect()->where('wiw.wpid IS NULL');


        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(
            array(
                'main_table.id',
                'main_table.status',
                'main_table.listing_id',
                'main_table.product_id',
                'second_table.wpid',
                'second_table.is_variation_product',
                'second_table.variation_parent_id'
            )
        );

        return Mage::getSingleton('core/resource')->getConnection('core_read')->query(
            $collection->getSelect()->__toString()
        );
    }

    /**
     * @param array $listingProductIds
     */
    protected function updateListingProductStatuses(array $listingProductIds)
    {
        parent::updateListingProductStatuses($listingProductIds);

        Mage::getSingleton('core/resource')->getConnection('core_write')->update(
            $this->_listingProductChildTable,
            array('is_missed_on_channel' => 1),
            '`listing_product_id` IN ('.implode(',', $listingProductIds).')'
        );
    }

    /**
     * @return string
     */
    protected function getComponentInventoryTable()
    {
        return Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix(
            'm2epro_walmart_inventory_wpid'
        );
    }

    /**
     * @return string
     */
    protected function getComponentOtherListingTable()
    {
        return Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix("m2epro_walmart_listing_other");
    }

    /**
     * @return string
     */
    protected function getInventoryIdentifier()
    {
        return 'wpid';
    }

    /**
     * @return string
     */
    protected function getComponentMode()
    {
        return Ess_M2ePro_Helper_Component_Walmart::NICK;
    }

    //########################################
}
