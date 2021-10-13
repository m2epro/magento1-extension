<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Listing_Product as WalmartProduct;
use Ess_M2ePro_Model_Cron_Task_Walmart_Listing_SynchronizeInventory_Responser as Responser;

class Ess_M2ePro_Model_Listing_SynchronizeInventory_Walmart_ListingProductsHandler
    extends Ess_M2ePro_Model_Listing_SynchronizeInventory_AbstractExistingProductsHandler
{
    /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection */
    protected $_preparedListingProductsCollection;

    //########################################

    /**
     * @param array $responseData
     * @return array
     * @throws Zend_Db_Statement_Exception
     */
    public function handle(array $responseData)
    {
        $this->_responseData = $responseData;
        $this->updateReceivedListingProducts();

        return $this->_responseData;
    }

    /**
     * @throws Zend_Db_Statement_Exception
     * @throws Exception
     */
    protected function updateReceivedListingProducts()
    {
        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode($this->getComponentMode());

        $parentIdsForProcessing = array();

        $instructionsData = array();

        foreach (array_chunk(array_keys($this->_responseData), 200) as $wpids) {
            /** @var $stmtTemp Zend_Db_Statement_Pdo */
            $stmtTemp = $this->getPdoStatementExistingListings($wpids);

            while ($existingItem = $stmtTemp->fetch()) {
                if (!isset($this->_responseData[$existingItem['wpid']])) {
                    continue;
                }

                $receivedItem = $this->_responseData[$existingItem['wpid']];
                unset($this->_responseData[$existingItem['wpid']]);

                $isOnlinePriceInvalid = in_array(
                    Ess_M2ePro_Helper_Component_Walmart::PRODUCT_STATUS_CHANGE_REASON_INVALID_PRICE,
                    $receivedItem['status_change_reason']
                );

                $newData = array(
                    'upc'                     => !empty($receivedItem['upc']) ? (string)$receivedItem['upc'] : null,
                    'gtin'                    => !empty($receivedItem['gtin']) ? (string)$receivedItem['gtin'] : null,
                    'wpid'                    => (string)$receivedItem['wpid'],
                    'item_id'                 => (string)$receivedItem['item_id'],
                    'online_qty'              => (int)$receivedItem['qty'],
                    'publish_status'          => (string)$receivedItem['publish_status'],
                    'lifecycle_status'        => (string)$receivedItem['lifecycle_status'],
                    'status_change_reasons'   =>
                        Mage::helper('M2ePro')->jsonEncode($receivedItem['status_change_reason']),
                    'is_online_price_invalid' => $isOnlinePriceInvalid,
                    'is_missed_on_channel'    => false,
                );

                $newData['status'] = Mage::helper('M2ePro/Component_Walmart')->getResultProductStatus(
                    $receivedItem['publish_status'], $receivedItem['lifecycle_status'], $newData['online_qty']
                );

                $existingData = array(
                    'upc'                     => !empty($existingItem['upc']) ? (string)$existingItem['upc'] : null,
                    'gtin'                    => !empty($existingItem['gtin']) ? (string)$existingItem['gtin'] : null,
                    'wpid'                    => (string)$existingItem['wpid'],
                    'item_id'                 => (string)$existingItem['item_id'],
                    'online_qty'              => (int)$existingItem['online_qty'],
                    'status'                  => (int)$existingItem['status'],
                    'publish_status'          => (string)$existingItem['publish_status'],
                    'lifecycle_status'        => (string)$existingItem['lifecycle_status'],
                    'status_change_reasons'   => (string)$existingItem['status_change_reasons'],
                    'is_online_price_invalid' => (bool)$existingItem['is_online_price_invalid'],
                    'is_missed_on_channel'    => (bool)$existingItem['is_missed_on_channel'],
                );

                $existingAdditionalData = Mage::helper('M2ePro')->jsonDecode($existingItem['additional_data']);
                $lastSynchDates = !empty($existingAdditionalData['last_synchronization_dates'])
                    ? $existingAdditionalData['last_synchronization_dates']
                    : array();

                if (!empty($lastSynchDates['qty']) && !empty($receivedItem['actual_on_date'])) {
                    if ($this->isProductInfoOutdated($lastSynchDates['qty'], $receivedItem['actual_on_date'])) {
                        unset(
                            $newData['online_qty'], $newData['status'],
                            $newData['lifecycle_status'], $newData['publish_status']
                        );
                        unset(
                            $existingData['online_qty'], $existingData['status'],
                            $existingData['lifecycle_status'], $existingData['publish_status']
                        );
                    }
                }

                if (!empty($lastSynchDates['price']) && !empty($receivedItem['actual_on_date'])) {
                    if ($this->isProductInfoOutdated($lastSynchDates['price'], $receivedItem['actual_on_date'])) {
                        unset(
                            $newData['status'], $newData['lifecycle_status'],
                            $newData['publish_status'], $newData['is_online_price_invalid']
                        );
                        unset(
                            $existingData['status'], $existingData['lifecycle_status'],
                            $existingData['publish_status'], $existingData['is_online_price_invalid']
                        );
                    }
                }

                if ($newData == $existingData) {
                    continue;
                }

                $tempLogMessages = array();

                if ($this->isDataChanged($existingData, $newData, 'status')) {
                    $instructionsData[] = array(
                        'listing_product_id' => $existingItem['listing_product_id'],
                        'type'               => WalmartProduct::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
                        'initiator'          => Responser::INSTRUCTION_INITIATOR,
                        'priority'           => 80,
                    );

                    $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

                    $statusChangedFrom = Mage::helper('M2ePro/Component_Walmart')
                        ->getHumanTitleByListingProductStatus($existingData['status']);
                    $statusChangedTo = Mage::helper('M2ePro/Component_Walmart')
                        ->getHumanTitleByListingProductStatus($newData['status']);

                    if (!empty($statusChangedFrom) && !empty($statusChangedTo)) {
                        $tempLogMessages[] = Mage::helper('M2ePro')->__(
                            'Item Status was changed from "%from%" to "%to%" .',
                            $statusChangedFrom,
                            $statusChangedTo
                        );
                    }

                    if (!empty($existingItem['is_variation_product']) && !empty($existingItem['variation_parent_id'])) {
                        $parentIdsForProcessing[] = (int)$existingItem['variation_parent_id'];
                    }
                }

                if ($this->isDataChanged($existingData, $newData, 'online_qty')) {
                    $instructionsData[] = array(
                        'listing_product_id' => $existingItem['listing_product_id'],
                        'type'               => WalmartProduct::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
                        'initiator'          => Responser::INSTRUCTION_INITIATOR,
                        'priority'           => 80,
                    );

                    $tempLogMessages[] = Mage::helper('M2ePro')->__(
                        'Item QTY was changed from %from% to %to% .',
                        (int)$existingData['online_qty'],
                        (int)$newData['online_qty']
                    );

                    if (!empty($existingItem['is_variation_product']) && !empty($existingItem['variation_parent_id'])) {
                        $parentIdsForProcessing[] = (int)$existingItem['variation_parent_id'];
                    }
                }

                foreach ($tempLogMessages as $tempLogMessage) {
                    $tempLog->addProductMessage(
                        $existingItem['listing_id'],
                        $existingItem['product_id'],
                        $existingItem['listing_product_id'],
                        Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                        $this->getLogsActionId(),
                        Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE,
                        $tempLogMessage,
                        Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS
                    );
                }

                $newData['id'] = (int)$existingItem['listing_product_id'];

                Mage::helper('M2ePro/Component_Walmart')->getModel('Listing_Product')->addData($newData)->save();
            }
        }

        Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add($instructionsData);
        $this->processParentProcessors($parentIdsForProcessing);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Product_Collection
     */
    protected function getPreparedProductsCollection()
    {
        if ($this->_preparedListingProductsCollection !== null) {
            return $this->_preparedListingProductsCollection;
        }

        /** @var $collection Ess_M2ePro_Model_Resource_Listing_Product_Collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->getSelect()->join(
            array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            'main_table.listing_id = l.id',
            array()
        );

        $collection->getSelect()->where('l.account_id = ?', (int)$this->getAccount()->getId());
        $collection->getSelect()->where(
            '`main_table`.`status` != ?',
            Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED
        );
        $collection->getSelect()->where("`second_table`.`wpid` is not null and `second_table`.`wpid` != ''");
        $collection->getSelect()->where("`second_table`.`is_variation_parent` != ?", 1);

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(
            array(
                'main_table.listing_id',
                'main_table.product_id',
                'main_table.status',
                'main_table.additional_data',
                'second_table.sku',
                'second_table.upc',
                'second_table.ean',
                'second_table.gtin',
                'second_table.wpid',
                'second_table.item_id',
                'second_table.online_qty',
                'second_table.listing_product_id',
                'second_table.is_variation_product',
                'second_table.variation_parent_id',
                'second_table.is_online_price_invalid',
                'second_table.publish_status',
                'second_table.lifecycle_status',
                'second_table.status_change_reasons',
                'second_table.is_missed_on_channel',
            )
        );

        return $this->_preparedListingProductsCollection = $collection;
    }

    /**
     * @param $lastDate
     * @param $actualOnDate
     * @return bool
     * @throws Exception
     */
    protected function isProductInfoOutdated($lastDate, $actualOnDate)
    {
        $lastDate = new DateTime($lastDate, new DateTimeZone('UTC'));
        $actualOnDate = new DateTime($actualOnDate, new DateTimeZone('UTC'));

        $lastDate->modify('+1 hour');

        return $lastDate > $actualOnDate;
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
