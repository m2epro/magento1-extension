<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Cron_Task_Amazon_Listing_SynchronizeInventory_Responser as Responser;

class Ess_M2ePro_Model_Listing_SynchronizeInventory_Amazon_ListingProductsHandler
    extends Ess_M2ePro_Model_Listing_SynchronizeInventory_AbstractExistingProductsHandler
{
    /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection */
    protected $_preparedListingProductsCollection;

    //########################################

    /**
     * @param array $responseData
     * @return array
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function handle(array $responseData)
    {
        $this->_responseData = $responseData;
        $this->updateReceivedListingProducts();

        return $this->_responseData;
    }

    /**
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     * @throws Exception
     */
    protected function updateReceivedListingProducts()
    {
        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode($this->getComponentMode());

        $parentIdsForProcessing = array();
        $instructionsData = array();

        foreach (array_chunk(array_keys($this->_responseData), 200) as $skuPack) {
            /** @var $stmtTemp Zend_Db_Statement_Pdo */
            $stmtTemp = $this->getPdoStatementExistingListings($skuPack);

            while ($existingItem = $stmtTemp->fetch()) {
                if (!isset($this->_responseData[$existingItem['sku']])) {
                    continue;
                }

                $receivedItem = $this->_responseData[$existingItem['sku']];
                unset($this->_responseData[$existingItem['sku']]);

                $existingData = array(
                    'general_id'           => (string)$existingItem['general_id'],
                    'online_regular_price' => !empty($existingItem['online_regular_price'])
                        ? (float)$existingItem['online_regular_price'] : null,
                    'online_qty'           => (int)$existingItem['online_qty'],
                    'is_afn_channel'       => (bool)$existingItem['is_afn_channel'],
                    'is_isbn_general_id'   => (bool)$existingItem['is_isbn_general_id'],
                    'status'               => (int)$existingItem['status']
                );

                $newData = array(
                    'general_id'           => (string)$receivedItem['identifiers']['general_id'],
                    'online_regular_price' => !empty($receivedItem['price']) ? (float)$receivedItem['price'] : null,
                    'online_qty'           => (int)$receivedItem['qty'],
                    'is_afn_channel'       => (bool)$receivedItem['channel']['is_afn'],
                    'is_isbn_general_id'   => (bool)$receivedItem['identifiers']['is_isbn']
                );

                if ($newData['is_afn_channel']) {
                    $newData['online_qty'] = null;
                    $newData['status'] = $existingData['is_afn_channel'] ?
                        $existingData['status'] : Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN;
                } else {
                    if ($existingItem['online_afn_qty'] !== null) {
                        $newData['online_afn_qty'] = null;
                    }

                    if ($newData['online_qty'] > 0) {
                        $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
                    } else {
                        $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE;
                    }
                }

                $existingAdditionalData = Mage::helper('M2ePro')->jsonDecode($existingItem['additional_data']);
                $lastSynchDates = !empty($existingAdditionalData['last_synchronization_dates'])
                    ? $existingAdditionalData['last_synchronization_dates']
                    : array();

                if (!empty($lastSynchDates['qty'])) {
                    if ($this->isProductInfoOutdated($lastSynchDates['qty'])) {
                        unset($newData['online_qty'], $newData['status'], $newData['is_afn_channel']);
                        unset($existingData['online_qty'], $existingData['status'], $existingData['is_afn_channel']);
                    }
                }

                if (!empty($lastSynchDates['price'])) {
                    if ($this->isProductInfoOutdated($lastSynchDates['price'])) {
                        unset($newData['online_regular_price']);
                        unset($existingData['online_regular_price']);
                    }
                }

                if (!empty($lastSynchDates['fulfillment_switching'])) {
                    if ($this->isProductInfoOutdated($lastSynchDates['fulfillment_switching'])) {
                        unset($newData['online_qty'], $newData['status'], $newData['is_afn_channel']);
                        unset($existingData['online_qty'], $existingData['status'], $existingData['is_afn_channel']);
                    }
                }

                if ($existingItem['is_repricing'] &&
                    !$existingItem['is_online_disabled'] &&
                    !$existingItem['is_online_inactive']
                ) {
                    unset($newData['online_regular_price'], $existingData['online_regular_price']);
                }

                if ($newData == $existingData) {
                    continue;
                }

                $tempLogMessages = array();

                if ($this->isDataChanged($existingData, $newData, 'status')) {
                    $instructionsData[] = array(
                        'listing_product_id' => (int)$existingItem['listing_product_id'],
                        'type'               =>
                            Ess_M2ePro_Model_Amazon_Listing_Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
                        'initiator'          => Responser::INSTRUCTION_INITIATOR,
                        'priority'           => 80,
                    );

                    $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

                    $statusChangedFrom = Mage::helper('M2ePro/Component_Amazon')
                        ->getHumanTitleByListingProductStatus($existingData['status']);
                    $statusChangedTo = Mage::helper('M2ePro/Component_Amazon')
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
                    if ($this->isNeedSkipQTYChange($existingData, $newData)) {
                        unset($newData['online_qty']);
                    } else {
                        $instructionsData[] = array(
                            'listing_product_id' => (int)$existingItem['listing_product_id'],
                            'type'               =>
                                Ess_M2ePro_Model_Amazon_Listing_Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
                            'initiator'          => Responser::INSTRUCTION_INITIATOR,
                            'priority'           => 80,
                        );

                        $tempLogMessages[] = Mage::helper('M2ePro')->__(
                            'Item QTY was changed from %from% to %to% .',
                            (int)$existingData['online_qty'],
                            (int)$newData['online_qty']
                        );

                        if (!empty($existingItem['is_variation_product']) &&
                            !empty($existingItem['variation_parent_id'])
                        ) {
                            $parentIdsForProcessing[] = (int)$existingItem['variation_parent_id'];
                        }
                    }
                }

                if ($this->isDataChanged($existingData, $newData, 'online_regular_price')) {
                    $instructionsData[] = array(
                        'listing_product_id' => (int)$existingItem['listing_product_id'],
                        'type'               =>
                            Ess_M2ePro_Model_Amazon_Listing_Product::INSTRUCTION_TYPE_CHANNEL_REGULAR_PRICE_CHANGED,
                        'initiator'          => Responser::INSTRUCTION_INITIATOR,
                        'priority'           => 60,
                    );

                    $tempLogMessages[] = Mage::helper('M2ePro')->__(
                        'Item Price was changed from %from% to %to% .',
                        (float)$existingData['online_regular_price'],
                        (float)$newData['online_regular_price']
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

                Mage::helper('M2ePro/Component_Amazon')->getModel('Listing_Product')->addData($newData)->save();
            }
        }

        Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add($instructionsData);
        $this->processParentProcessors($parentIdsForProcessing);
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Product_Collection
     */
    protected function getPreparedProductsCollection()
    {
        if ($this->_preparedListingProductsCollection !== null) {
            return $this->_preparedListingProductsCollection;
        }

        /** @var $collection Ess_M2ePro_Model_Resource_Listing_Product_Collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
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
        $collection->getSelect()->where("`second_table`.`is_variation_parent` != ?", 1);
        $collection->getSelect()->joinLeft(
            array(
                'repricing' => Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->getMainTable()
            ),
            'second_table.listing_product_id = repricing.listing_product_id',
            array('is_online_disabled', 'is_online_inactive')
        );

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(
            array(
                'main_table.listing_id',
                'main_table.product_id',
                'main_table.status',
                'main_table.additional_data',
                'second_table.sku',
                'second_table.general_id',
                'second_table.online_regular_price',
                'second_table.online_qty',
                'second_table.online_afn_qty',
                'second_table.is_afn_channel',
                'second_table.is_isbn_general_id',
                'second_table.listing_product_id',
                'second_table.is_variation_product',
                'second_table.variation_parent_id',
                'second_table.is_repricing',
                'repricing.is_online_disabled',
                'repricing.is_online_inactive'
            )
        );

        return $this->_preparedListingProductsCollection = $collection;
    }

    /**
     * @param $lastDate
     * @return bool
     * @throws Exception
     */
    protected function isProductInfoOutdated($lastDate)
    {
        if (empty($this->_responserParams['request_date'])) {
            return false;
        }

        $lastDate = new DateTime($lastDate, new DateTimeZone('UTC'));
        $requestDate = new DateTime($this->_responserParams['request_date'], new DateTimeZone('UTC'));

        $lastDate->modify('+1 hour');

        return $lastDate > $requestDate;
    }

    /**
     * Skip channel change to prevent oversell when we have got report before an order
     * https://m2epro.atlassian.net/browse/M1-77
     *
     * @param $existData
     * @param $newData
     * @return bool
     */
    protected function isNeedSkipQTYChange($existData, $newData)
    {
        return $newData['online_qty'] < 5 && $newData['online_qty'] < $existData['online_qty'];
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
