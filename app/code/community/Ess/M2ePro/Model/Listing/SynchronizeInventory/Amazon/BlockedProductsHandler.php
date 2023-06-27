<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_SynchronizeInventory_Amazon_BlockedProductsHandler
    extends Ess_M2ePro_Model_Listing_SynchronizeInventory_AbstractHandler
{
    /** @var string */
    protected $_listingProductTable;

    /** @var string */
    protected $_listingProductChildTable;

    //########################################

    /**
     * @param array $responseData
     * @throws Ess_M2ePro_Model_Exception_Logic
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function handle(array $responseData = array())
    {
        $this->markNotReceivedListingProductsAsBlocked();

        if ($this->getAccount()->getChildObject()->getOtherListingsSynchronization()) {
            $this->markNotReceivedOtherListingsAsBlocked();
        }
    }

    /**
     * @throws Zend_Db_Statement_Exception
     * @throws Exception
     */
    protected function markNotReceivedListingProductsAsBlocked()
    {
        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode($this->getComponentMode());

        $notReceivedIds = array();
        $stmt = $this->getPdoStatementNotReceivedListingProducts();

        $uppercasedComponent = ucfirst($this->getComponentMode());

        $componentHelper = Mage::helper("M2ePro/Component_{$uppercasedComponent}");

        while ($notReceivedItem = $stmt->fetch()) {

            if (!in_array((int)$notReceivedItem['id'], $notReceivedIds)) {
                $statusChangedFrom = $componentHelper->getHumanTitleByListingProductStatus($notReceivedItem['status']);
                $statusChangedTo = $componentHelper->getHumanTitleByListingProductStatus(
                    Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED
                );

                $tempLogMessage = Mage::helper('M2ePro')->__(
                    'Item Status was changed from "%from%" to "%to%" .',
                    $statusChangedFrom,
                    $statusChangedTo
                );

                $tempLog->addProductMessage(
                    $notReceivedItem['listing_id'],
                    $notReceivedItem['product_id'],
                    $notReceivedItem['id'],
                    Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                    $this->getLogsActionId(),
                    Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE,
                    $tempLogMessage,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS
                );

                if (!empty($notReceivedItem['is_variation_product']) &&
                    !empty($notReceivedItem['variation_parent_id'])
                ) {
                    $parentIdsForProcessing[] = $notReceivedItem['variation_parent_id'];
                }
            }

            $notReceivedIds[] = (int)$notReceivedItem['id'];
        }

        $notReceivedIds = array_unique($notReceivedIds);

        if (empty($notReceivedIds)) {
            return;
        }

        $this->_listingProductTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();
        $this->_listingProductChildTable = Mage::getResourceModel("M2ePro/{$uppercasedComponent}_Listing_Product")
            ->getMainTable();

        foreach (array_chunk($notReceivedIds, 1000) as $idsPart) {
            $this->updateListingProductStatuses($idsPart);
        }

        if (!empty($parentIdsForProcessing)) {
            Mage::getSingleton('core/resource')->getConnection('core_write')->update(
                $this->_listingProductChildTable,
                array('variation_parent_need_processor' => 1),
                array(
                    'is_variation_parent = ?'   => 1,
                    'listing_product_id IN (?)' => $parentIdsForProcessing,
                )
            );
        }
    }

    /**
     * @throws Zend_Db_Adapter_Exception
     */
    protected function markNotReceivedOtherListingsAsBlocked()
    {
        $structureHelper = Mage::helper('M2ePro/Module_Database_Structure');

        $statusBlocked = Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED;
        $statusNotListed = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;
        $statusChangerComponent = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

        $sql = <<<SQL
UPDATE {$structureHelper->getTableNameWithPrefix('m2epro_listing_other')} AS `lo`
    INNER JOIN {$this->getComponentOtherListingTable()} AS `clo` 
        ON `lo`.`id` = `clo`.`listing_other_id`
    LEFT JOIN {$this->getComponentInventoryTable()} AS `it` 
        ON `clo`.`{$this->getInventoryIdentifier()}` = `it`.`{$this->getInventoryIdentifier()}` 
        AND `lo`.`account_id` = `it`.`account_id`
SET `lo`.`status` = {$statusBlocked}, `lo`.`status_changer` = {$statusChangerComponent}
WHERE `lo`.`account_id` = {$this->getAccount()->getId()}
  AND `lo`.`status` != {$statusBlocked} 
  AND `lo`.`status` != {$statusNotListed} 
  AND `it`.`{$this->getInventoryIdentifier()}` IS NULL
SQL;

        Mage::getSingleton('core/resource')->getConnection('core_write')->query($sql);
    }

    /**
     * @param array $listingProductIds
     */
    protected function updateListingProductStatuses(array $listingProductIds)
    {
        Mage::getSingleton('core/resource')->getConnection('core_write')->update(
            $this->_listingProductTable,
            array(
                'status'         => Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
                'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT,
            ),
            '`id` IN ('.implode(',', $listingProductIds).')'
        );
    }

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
