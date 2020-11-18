<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_SynchronizeInventory_Walmart_OtherListingsHandler
    extends Ess_M2ePro_Model_Listing_SynchronizeInventory_AbstractExistingProductsHandler
{
    /** @var Ess_M2ePro_Model_Resource_Listing_Other_Collection */
    protected $_preparedListingsOtherCollection;

    /**
     * @param array $responseData
     * @throws Ess_M2ePro_Model_Exception_Logic
     * @throws Zend_Db_Statement_Exception
     */
    public function handle(array $responseData)
    {
        $this->_responseData = $responseData;

        $this->updateReceivedOtherListings();
        $this->createNotExistedOtherListings();
    }

    //########################################

    /**
     * @throws Zend_Db_Statement_Exception
     */
    protected function updateReceivedOtherListings()
    {
        foreach (array_chunk(array_keys($this->_responseData), 200) as $wpids) {
            /** @var $stmtTemp Zend_Db_Statement_Pdo */
            $stmtTemp = $this->getPdoStatementExistingListings($wpids);

            while ($existingItem = $stmtTemp->fetch()) {

                $receivedItem = $this->_responseData[$existingItem['wpid']];
                unset($this->_responseData[$existingItem['wpid']]);

                $isOnlinePriceInvalid = in_array(
                    Ess_M2ePro_Helper_Component_Walmart::PRODUCT_STATUS_CHANGE_REASON_INVALID_PRICE,
                    $receivedItem['status_change_reason']
                );

                $newData = array(
                    'upc'                   => !empty($receivedItem['upc']) ? (string)$receivedItem['upc'] : null,
                    'gtin'                  => !empty($receivedItem['gtin']) ? (string)$receivedItem['gtin'] : null,
                    'wpid'                  => (string)$receivedItem['wpid'],
                    'item_id'               => (string)$receivedItem['item_id'],
                    'sku'                   => (string)$receivedItem['sku'],
                    'title'                 => (string)$receivedItem['title'],
                    'online_price'          => (float)$receivedItem['price'],
                    'online_qty'            => (int)$receivedItem['qty'],
                    'publish_status'        => (string)$receivedItem['publish_status'],
                    'lifecycle_status'      => (string)$receivedItem['lifecycle_status'],
                    'status_change_reasons' =>
                        Mage::helper('M2ePro')->jsonEncode($receivedItem['status_change_reason']),
                    'is_online_price_invalid' => $isOnlinePriceInvalid,
                );

                $newData['status'] = Mage::helper('M2ePro/Component_Walmart')->getResultProductStatus(
                    $receivedItem['publish_status'], $receivedItem['lifecycle_status'], $newData['online_qty']
                );

                $existingData = array(
                    'upc'                   => !empty($existingItem['upc']) ? (string)$existingItem['upc'] : null,
                    'gtin'                  => !empty($existingItem['gtin']) ? (string)$existingItem['gtin'] : null,
                    'wpid'                  => (string)$existingItem['wpid'],
                    'item_id'               => (string)$existingItem['item_id'],
                    'sku'                   => (string)$existingItem['sku'],
                    'title'                 => (string)$existingItem['title'],
                    'online_price'          => (float)$existingItem['online_price'],
                    'online_qty'            => (int)$existingItem['online_qty'],
                    'publish_status'        => (string)$existingItem['publish_status'],
                    'lifecycle_status'      => (string)$existingItem['lifecycle_status'],
                    'status_change_reasons' => (string)$existingItem['status_change_reasons'],
                    'status'                => (int)$existingItem['status'],
                    'is_online_price_invalid' => (bool)$existingItem['is_online_price_invalid'],
                );

                if ($newData == $existingData) {
                    continue;
                }

                if ($newData['status'] != $existingData['status']) {
                    $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;
                }

                $newData['id'] = (int)$existingItem['listing_other_id'];
                Mage::helper('M2ePro/Component_Walmart')->getModel('Listing_Other')->addData($newData)->save();
            }
        }
    }

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function createNotExistedOtherListings()
    {
        $isMappingEnabled = $this->getAccount()->getChildObject()->isOtherListingsMappingEnabled();

        if ($isMappingEnabled) {
            /** @var $mappingModel Ess_M2ePro_Model_Walmart_Listing_Other_Mapping */
            $mappingModel = Mage::getModel('M2ePro/Walmart_Listing_Other_Mapping');
            $mappingModel->initialize($this->getAccount());
        }

        foreach ($this->_responseData as $receivedItem) {

            $isOnlinePriceInvalid = in_array(
                Ess_M2ePro_Helper_Component_Walmart::PRODUCT_STATUS_CHANGE_REASON_INVALID_PRICE,
                $receivedItem['status_change_reason']
            );

            $newData = array(
                'account_id'     => $this->getAccount()->getId(),
                'marketplace_id' => $this->getAccount()->getChildObject()->getMarketplace()->getId(),
                'product_id'     => null,

                'upc'     => !empty($receivedItem['upc']) ? (string)$receivedItem['upc'] : null,
                'gtin'    => !empty($receivedItem['gtin']) ? (string)$receivedItem['gtin'] : null,
                'wpid'    => (string)$receivedItem['wpid'],
                'item_id' => (string)$receivedItem['item_id'],

                'sku'   => (string)$receivedItem['sku'],
                'title' => $receivedItem['title'],

                'online_price' => (float)$receivedItem['price'],
                'online_qty'   => (int)$receivedItem['qty'],

                'publish_status'        => (string)$receivedItem['publish_status'],
                'lifecycle_status'      => (string)$receivedItem['lifecycle_status'],
                'status_change_reasons' => Mage::helper('M2ePro')->jsonEncode($receivedItem['status_change_reason']),
                'is_online_price_invalid' => $isOnlinePriceInvalid,
            );

            $newData['status'] = Mage::helper('M2ePro/Component_Walmart')->getResultProductStatus(
                $receivedItem['publish_status'], $receivedItem['lifecycle_status'], $newData['online_qty']
            );

            $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

            $listingOtherModel = Mage::helper('M2ePro/Component_Walmart')->getModel('Listing_Other');
            $listingOtherModel->setData($newData)->save();

            if ($isMappingEnabled) {
                $mappingModel->autoMapOtherListingProduct($listingOtherModel);
            }
        }
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Other_Collection
     */
    protected function getPreparedProductsCollection()
    {
        if ($this->_preparedListingsOtherCollection) {
            return $this->_preparedListingsOtherCollection;
        }

        /** @var $collection Ess_M2ePro_Model_Resource_Listing_Other_Collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Other');
        $collection->addFieldToFilter('account_id', (int)$this->getAccount()->getId());

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(
            array(
                'main_table.status',
                'second_table.sku',
                'second_table.title',
                'second_table.online_price',
                'second_table.online_qty',
                'second_table.publish_status',
                'second_table.lifecycle_status',
                'second_table.status_change_reasons',
                'second_table.upc',
                'second_table.gtin',
                'second_table.ean',
                'second_table.wpid',
                'second_table.item_id',
                'second_table.listing_other_id',
                'second_table.is_online_price_invalid'
            )
        );

        return $this->_preparedListingsOtherCollection = $collection;
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
