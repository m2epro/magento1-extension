<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Listing_SynchronizeInventory_AbstractBlockedHandler
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
     * @return string
     */
    abstract protected function getComponentOtherListingTable();

    /**
     * @return string
     */
    abstract protected function getComponentInventoryTable();

    /**
     * @return Zend_Db_Statement_Interface
     * @throws Exception
     */
    abstract protected function getPdoStatementNotReceivedListingProducts();

    //########################################
}
