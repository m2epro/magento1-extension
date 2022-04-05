<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Ebay_Listing_Other
    extends Ess_M2ePro_Model_Resource_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Listing_Other', 'listing_other_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################

    public function resetEntities()
    {
        $listingOtherTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_listing_other');
        $ebayItemTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_item');
        $ebayListingOtherTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_listing_other');
        $ebayListingProductTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_listing_product');

        $componentName = Ess_M2ePro_Helper_Component_Ebay::NICK;

        $ebayItemIdsQuery = <<<SQL
SELECT `ei`.`item_id`
FROM `{$ebayItemTable}` AS `ei`
INNER JOIN `{$ebayListingOtherTable}` AS `elo`
ON `ei`.`item_id` = `elo`.`item_id`
WHERE `ei`.`id` NOT IN (
    SELECT `elp`.`ebay_item_id`
    FROM `{$ebayListingProductTable}` AS `elp`
    WHERE `elp`.`ebay_item_id` IS NOT NULL
)
SQL;

        $listingOtherIdsQuery = <<<SQL
SELECT `id`
FROM `{$listingOtherTable}`
WHERE `component_mode` = '{$componentName}'
SQL;

        $ebayListingOtherIdsQuery = <<<SQL
SELECT `listing_other_id`
FROM `{$ebayListingOtherTable}`
SQL;

        $this->removeRecords($ebayItemIdsQuery, 'item_id', $ebayItemTable);
        $this->removeRecords($listingOtherIdsQuery, 'id', $listingOtherTable);
        $this->removeRecords($ebayListingOtherIdsQuery, 'listing_other_id', $ebayListingOtherTable);

        foreach (Mage::helper('M2ePro/Component_Ebay')->getCollection('Account') as $account) {
            $account->setData('other_listings_last_synchronization', null)->save();
        }
    }

    /**
     * @param string $sql
     * @param string $key
     * @param string $table
     */
    private function removeRecords($sql, $key, $table)
    {
        $itemIds = array();

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

        foreach ($connection->fetchAll($sql) as $row) {
            $itemIds[] = $row[$key];
        }

        foreach (array_chunk($itemIds, 1000) as $itemIdsSet) {
            $connection->delete($table, array('`' . $key . '` IN (?)' => $itemIdsSet));
        }
    }

    //########################################
}
