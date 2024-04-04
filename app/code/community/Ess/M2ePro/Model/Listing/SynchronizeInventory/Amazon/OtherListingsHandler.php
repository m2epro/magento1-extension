<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_SynchronizeInventory_Amazon_OtherListingsHandler
    extends Ess_M2ePro_Model_Listing_SynchronizeInventory_AbstractExistingProductsHandler
{
    /** @var Ess_M2ePro_Model_Resource_Listing_Other_Collection */
    protected $_preparedListingsOtherCollection;

    //########################################

    /**
     * @param array $responseData
     * @throws Ess_M2ePro_Model_Exception_Logic
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function handle(array $responseData)
    {
        $this->_responseData = $responseData;

        $this->updateReceivedOtherListings();
        $this->createNotExistedOtherListings();
    }

    /**
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     */
    protected function updateReceivedOtherListings()
    {
        foreach (array_chunk(array_keys($this->_responseData), 200) as $skuPack) {
            /** @var $stmtTemp Zend_Db_Statement_Pdo */
            $stmtTemp = $this->getPdoStatementExistingListings($skuPack);

            while ($existingItem = $stmtTemp->fetch()) {

                $receivedItem = $this->_responseData[$existingItem['sku']];
                unset($this->_responseData[$existingItem['sku']]);

                $existingData = array(
                    'general_id'         => (string)$existingItem['general_id'],
                    'title'              => (string)$existingItem['title'],
                    'online_price'       => (float)$existingItem['online_price'],
                    'online_qty'         => (int)$existingItem['online_qty'],
                    'is_afn_channel'     => (bool)$existingItem['is_afn_channel'],
                    'is_isbn_general_id' => (bool)$existingItem['is_isbn_general_id'],
                    'status'             => (int)$existingItem['status']
                );

                $newData = array(
                    'general_id'         => (string)$receivedItem['identifiers']['general_id'],
                    'title'              => (string)$receivedItem['title'],
                    'online_price'       => (float)$receivedItem['price'],
                    'online_qty'         => (int)$receivedItem['qty'],
                    'is_afn_channel'     => (bool)$receivedItem['channel']['is_afn'],
                    'is_isbn_general_id' => (bool)$receivedItem['identifiers']['is_isbn']
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

                if ($receivedItem['title'] === null ||
                    $receivedItem['title'] == Ess_M2ePro_Model_Amazon_Listing_Other::EMPTY_TITLE_PLACEHOLDER) {
                    unset($newData['title'], $existingData['title']);
                }

                if ($existingItem['is_repricing'] && !$existingItem['is_repricing_disabled']) {
                    unset($newData['online_price'], $existingData['online_price']);
                }

                if ($newData == $existingData) {
                    continue;
                }

                if ($newData['status'] != $existingData['status']) {
                    $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;
                }

                $newData['id'] = (int)$existingItem['listing_other_id'];
                Mage::helper('M2ePro/Component_Amazon')->getModel('Listing_Other')->addData($newData)->save();
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
            /** @var $mappingModel Ess_M2ePro_Model_Amazon_Listing_Other_Mapping */
            $mappingModel = Mage::getModel('M2ePro/Amazon_Listing_Other_Mapping');
            $mappingModel->initialize($this->getAccount());
        }

        foreach ($this->_responseData as $receivedItem) {

            $newData = array(
                'account_id'     => $this->getAccount()->getId(),
                'marketplace_id' => $this->getAccount()->getChildObject()->getMarketplace()->getId(),
                'product_id'     => null,

                'general_id' => (string)$receivedItem['identifiers']['general_id'],

                'sku'   => (string)$receivedItem['identifiers']['sku'],
                'title' => $receivedItem['title'],

                'online_price' => (float)$receivedItem['price'],
                'online_qty'   => (int)$receivedItem['qty'],

                'is_afn_channel'     => (bool)$receivedItem['channel']['is_afn'],
                'is_isbn_general_id' => (bool)$receivedItem['identifiers']['is_isbn']
            );

            if (isset($this->_responserParams['full_items_data']) && $this->_responserParams['full_items_data'] &&
                $newData['title'] == Ess_M2ePro_Model_Amazon_Listing_Other::EMPTY_TITLE_PLACEHOLDER) {
                $newData['title'] = null;
            }

            if ((bool)$newData['is_afn_channel']) {
                $newData['online_qty'] = null;
                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN;
            } else {
                if ((int)$newData['online_qty'] > 0) {
                    $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
                } else {
                    $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE;
                }
            }

            $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

            /** @var Ess_M2ePro_Model_Listing_Other $listingOtherModel */
            $listingOtherModel = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing_Other');
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
        if ($this->_preparedListingsOtherCollection !== null) {
            return $this->_preparedListingsOtherCollection;
        }

        /** @var $collection Ess_M2ePro_Model_Resource_Listing_Other_Collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');
        $collection->addFieldToFilter('account_id', (int)$this->getAccount()->getId());

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(
            array(
                'main_table.status',
                'second_table.sku',
                'second_table.general_id',
                'second_table.title',
                'second_table.online_price',
                'second_table.online_qty',
                'second_table.online_afn_qty',
                'second_table.is_afn_channel',
                'second_table.is_isbn_general_id',
                'second_table.listing_other_id',
                'second_table.is_repricing',
                'second_table.is_repricing_disabled'
            )
        );

        return $this->_preparedListingsOtherCollection = $collection;
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
}
