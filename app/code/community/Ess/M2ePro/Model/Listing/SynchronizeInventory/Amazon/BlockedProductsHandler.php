<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_SynchronizeInventory_Amazon_BlockedProductsHandler
    extends Ess_M2ePro_Model_Listing_SynchronizeInventory_AbstractBlockedHandler
{
    //########################################

    /**
     * @return Zend_Db_Statement_Interface
     * @throws Exception
     */
    protected function getPdoStatementNotReceivedListingProducts()
    {
        $borderDate = new DateTime('now', new \DateTimeZone('UTC'));
        $borderDate->modify('- 1 hour');

        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->join(
            array('l' => Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_listing')),
            'main_table.listing_id = l.id',
            array()
        );
        $collection->getSelect()->joinLeft(
            array('ais' => $this->getComponentInventoryTable()),
            'second_table.sku = ais.sku AND l.account_id = ais.account_id',
            array()
        );
        $collection->getSelect()->where('l.account_id = ?', (int)$this->getAccount()->getId());
        $collection->getSelect()->where('second_table.is_variation_parent != ?', 1);
        $collection->getSelect()->where(
            'second_table.list_date IS NULL OR second_table.list_date < ?', $borderDate->format('Y-m-d H:i:s')
        );
        $collection->getSelect()->where('ais.sku IS NULL');
        $collection->getSelect()->where(
            '`main_table`.`status` != ?',
            Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED
        );
        $collection->getSelect()->where(
            '`main_table`.`status` != ?',
            Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED
        );

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(
            array(
                'main_table.id',
                'main_table.status',
                'main_table.listing_id',
                'main_table.product_id',
                'main_table.additional_data',
                'second_table.is_variation_product',
                'second_table.variation_parent_id'
            )
        );

        return Mage::getSingleton('core/resource')->getConnection('core_read')->query(
            $collection->getSelect()->__toString()
        );
    }

    /**
     * @return string
     */
    protected function getComponentInventoryTable()
    {
        return Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_amazon_inventory_sku');
    }

    /**
     * @return string
     */
    protected function getComponentOtherListingTable()
    {
        return Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix("m2epro_amazon_listing_other");
    }

    /**
     * @return string
     */
    protected function getInventoryIdentifier()
    {
        return 'sku';
    }

    /**
     * @return string
     */
    protected function getComponentMode()
    {
        return Ess_M2ePro_Helper_Component_Amazon::NICK;
    }

    //########################################
}
