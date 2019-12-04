<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Repricing_Synchronization_General
    extends Ess_M2ePro_Model_Amazon_Repricing_Synchronization_Abstract
{
    const INSTRUCTION_TYPE_STATUS_CHANGED = 'repricing_status_changed';
    const INSTRUCTION_INITIATOR           = 'repricing_general_synchronization';

    protected $_parentProductsIds = array();

    //########################################

    public function run($skus = null)
    {
        if ($skus !== null && empty($skus)) {
            return false;
        }

        $filters = array();
        if ($skus !== null) {
            $filters = array(
                'skus_list' => $skus,
            );
            $skus = array_map('strtolower', $skus);
        }

        $response = $this->sendRequest($filters);

        if ($response === false || empty($response['status'])) {
            return false;
        }

        if (!empty($response['email'])) {
            $this->getAmazonAccountRepricing()->setData('email', $response['email']);
        }

        if ($skus === null) {
            $this->getAmazonAccountRepricing()->setData('total_products', count($response['offers']));
            $this->getAmazonAccountRepricing()->save();
        }

        $existedSkus = array_unique(
            array_merge(
                Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->getSkus(
                    $this->getAccount(), $skus
                ),
                Mage::getResourceModel('M2ePro/Amazon_Listing_Other')->getRepricingSkus(
                    $this->getAccount(), $skus
                )
            )
        );
        $existedSkus = array_map('strtolower', $existedSkus);

        $skuIndexedResultOffersData = array();
        foreach ($response['offers'] as $offerData) {
            $offerSku = strtolower($offerData['sku']);
            if ($skus !== null && !in_array($offerSku, $skus, true)) {
                continue;
            }

            $skuIndexedResultOffersData[$offerSku] = $offerData;
        }

        $this->processNewOffers($skuIndexedResultOffersData, $existedSkus);
        $this->processRemovedOffers($skuIndexedResultOffersData, $existedSkus);
        $this->processUpdatedOffers($skuIndexedResultOffersData, $existedSkus);

        $this->processVariationProcessor();

        return true;
    }

    public function reset(array $skus = array())
    {
        $this->removeListingsProductsRepricing($skus);
        $this->removeListingsOthersRepricing($skus);

        $this->processVariationProcessor();
    }

    //########################################

    protected function getMode()
    {
        return self::MODE_GENERAL;
    }

    //########################################

    protected function processNewOffers(array $resultOffersData, array $existedSkus)
    {
        $newOffersData = array();
        foreach ($resultOffersData as $offerSku => $offerData) {
            if (!in_array((string)$offerSku, $existedSkus, true)) {
                $newOffersData[(string)$offerSku] = $offerData;
            }
        }

        if (empty($newOffersData)) {
            return;
        }

        $this->addListingsProductsRepricing($newOffersData);
        $this->addListingOthersRepricing($newOffersData);
    }

    protected function processRemovedOffers(array $resultOffersData, array $existedSkus)
    {
        $removedOffersSkus = array();
        foreach ($existedSkus as $existedSku) {
            if (!array_key_exists((string)$existedSku, $resultOffersData)) {
                $removedOffersSkus[] = (string)$existedSku;
            }
        }

        if (empty($removedOffersSkus)) {
            return;
        }

        $this->removeListingsProductsRepricing($removedOffersSkus);
        $this->removeListingsOthersRepricing($removedOffersSkus);
    }

    protected function processUpdatedOffers(array $resultOffersData, array $existedSkus)
    {
        $updatedOffersData = array();
        foreach ($resultOffersData as $offerSku => $offerData) {
            if (in_array((string)$offerSku, $existedSkus, true)) {
                $updatedOffersData[(string)$offerSku] = $offerData;
            }
        }

        if (empty($updatedOffersData)) {
            return;
        }

        $this->updateListingsProductsRepricing($updatedOffersData);
        $this->updateListingsOthersRepricing($updatedOffersData);
    }

    //########################################

    protected function addListingsProductsRepricing(array $newOffersData)
    {
        $resourceModel = Mage::getResourceModel('M2ePro/Amazon_Listing_Product');
        $listingsProductsData = $resourceModel->getProductsDataBySkus(
            array_keys($newOffersData),
            array(
                'l.account_id' => $this->getAccount()->getId(),
            ),
            array(
                'second_table.variation_parent_id',
                'second_table.listing_product_id',
                'second_table.sku',
                'second_table.online_regular_price',
            )
        );

        if (empty($listingsProductsData)) {
            return;
        }

        $resource  = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');

        $insertData = array();

        foreach ($listingsProductsData as $listingProductData) {
            $listingProductId       = (int)$listingProductData['listing_product_id'];
            $parentListingProductId = (int)$listingProductData['variation_parent_id'];

            $offerData = $newOffersData[strtolower($listingProductData['sku'])];

            $insertData[$listingProductId] = array(
                'listing_product_id'        => $listingProductId,
                'online_regular_price'      => $offerData['regular_product_price'],
                'online_min_price'          => $offerData['minimal_product_price'],
                'online_max_price'          => $offerData['maximal_product_price'],
                'is_online_disabled'        => $offerData['is_calculation_disabled'],
                'is_online_inactive'        => $offerData['is_offer_inactive'],
                'last_synchronization_date' => Mage::helper('M2ePro')->getCurrentGmtDate(),
                'update_date'               => Mage::helper('M2ePro')->getCurrentGmtDate(),
                'create_date'               => Mage::helper('M2ePro')->getCurrentGmtDate(),
            );

            if ($offerData['product_price'] !== null &&
                $offerData['product_price'] != $listingProductData['online_regular_price']
            ) {
                $connWrite->update(
                    Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix('m2epro_amazon_listing_product'),
                    array('online_regular_price' => $offerData['product_price']),
                    array('listing_product_id = ?' => $listingProductId)
                );
            }

            if ($parentListingProductId && !in_array($parentListingProductId, $this->_parentProductsIds)) {
                $this->_parentProductsIds[] = $parentListingProductId;
            }
        }

        foreach (array_chunk($insertData, 1000, true) as $insertDataPack) {
            $connWrite->insertOnDuplicate(
                Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('m2epro_amazon_listing_product_repricing'),
                $insertDataPack
            );

            $connWrite->update(
                Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('m2epro_amazon_listing_product'),
                array(
                    'is_repricing'                         => 1,
                    'online_regular_sale_price'            => 0,
                    'online_regular_sale_price_start_date' => null,
                    'online_regular_sale_price_end_date'   => null,
                ),
                array('listing_product_id IN (?)' => array_keys($insertDataPack))
            );
        }
    }

    protected function addListingOthersRepricing(array $newOffersData)
    {
        $resourceModel = Mage::getResourceModel('M2ePro/Amazon_Listing_Other');
        $listingsOthersData = $resourceModel->getProductsDataBySkus(
            array_keys($newOffersData),
            array(
                'account_id' => $this->getAccount()->getId(),
            ),
            array(
                'second_table.listing_other_id',
                'second_table.sku',
                'second_table.online_price'
            )
        );

        if (empty($listingsOthersData)) {
            return;
        }

        $resource  = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');

        $enabledListingOthersIds  = array();
        $disabledListingOthersIds = array();
        $activeListingOthersIds   = array();
        $inactiveListingOthersIds = array();

        foreach ($listingsOthersData as $listingOtherData) {
            $listingOtherId = (int)$listingOtherData['listing_other_id'];
            $offerData = $newOffersData[strtolower($listingOtherData['sku'])];

            if ($offerData['product_price'] !== null &&
                $offerData['product_price'] != $listingOtherData['online_price']
            ) {
                $connWrite->update(
                    Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix('m2epro_amazon_listing_other'),
                    array(
                        'online_price'          => $offerData['product_price'],
                        'is_repricing'          => 1,
                        'is_repricing_disabled' => $offerData['is_calculation_disabled'],
                        'is_repricing_inactive' => $offerData['is_offer_inactive'],
                    ),
                    array('listing_other_id = ?' => $listingOtherId)
                );

                continue;
            }

            if ($offerData['is_calculation_disabled']) {
                $disabledListingOthersIds[] = $listingOtherId;
            } else {
                $enabledListingOthersIds[] = $listingOtherId;
            }

            if ($offerData['is_offer_inactive']) {
                $inactiveListingOthersIds[] = $listingOtherId;
            } else {
                $activeListingOthersIds[] = $listingOtherId;
            }
        }

        if (!empty($enabledListingOthersIds)) {
            $this->multipleUpdateListings(
                'm2epro_amazon_listing_other',
                array('is_repricing' => 1, 'is_repricing_disabled' => 0,),
                $enabledListingOthersIds
            );
        }

        if (!empty($disabledListingOthersIds)) {
            $this->multipleUpdateListings(
                'm2epro_amazon_listing_other',
                array('is_repricing' => 1, 'is_repricing_disabled' => 1,),
                $disabledListingOthersIds
            );
        }

        if (!empty($activeListingOthersIds)) {
            $this->multipleUpdateListings(
                'm2epro_amazon_listing_other',
                array('is_repricing' => 1, 'is_repricing_inactive' => 0,),
                $activeListingOthersIds
            );
        }

        if (!empty($inactiveListingOthersIds)) {
            $this->multipleUpdateListings(
                'm2epro_amazon_listing_other',
                array('is_repricing' => 1, 'is_repricing_inactive' => 1,),
                $inactiveListingOthersIds
            );
        }
    }

    //----------------------------------------

    protected function updateListingsProductsRepricing(array $updatedOffersData)
    {
        $keys = array_map(
            function($el){
            return (string)$el; 
            }, array_keys($updatedOffersData)
        );

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('is_variation_parent', 0);
        $listingProductCollection->addFieldToFilter('is_repricing', 1);

        $listingProductCollection->getSelect()->joinLeft(
            array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            'l.id = main_table.listing_id',
            array()
        );
        $listingProductCollection->getSelect()->joinInner(
            array('alpr' => Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->getMainTable()),
            'alpr.listing_product_id=main_table.id',
            array()
        );
        $listingProductCollection->addFieldToFilter('l.account_id', $this->getAccount()->getId());
        $listingProductCollection->addFieldToFilter('sku', array('in' => $keys));

        $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingProductCollection->getSelect()->columns(
            array(
                'main_table.product_id',
                'second_table.listing_product_id',
                'second_table.sku',
                'second_table.online_regular_price',
                'alpr.is_online_disabled',
                'alpr.is_online_inactive',
                'alpr.online_regular_price',
                'alpr.online_min_price',
                'alpr.online_max_price'
            )
        );

        $listingsProductsData = $listingProductCollection->getData();

        $resource  = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');

        $notManagedListingsProductsIds = array();

        $enabledListingsProductsIds  = array();
        $disabledListingsProductsIds = array();

        $activeListingsProductsIds = array();
        $inactiveListingsProductsIds = array();

        foreach ($listingsProductsData as $listingProductData) {
            $listingProductId = (int)$listingProductData['listing_product_id'];

            $offerData = $updatedOffersData[strtolower($listingProductData['sku'])];

            if ($offerData['product_price'] !== null &&
                !$offerData['is_calculation_disabled'] && !$offerData['is_offer_inactive'] &&
                 $listingProductData['online_regular_price'] != $offerData['product_price']
            ) {
                $connWrite->update(
                    Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix('m2epro_amazon_listing_product'),
                    array('online_regular_price' => $offerData['product_price']),
                    array('listing_product_id = ?' => $listingProductId)
                );
            }

            if ($listingProductData['online_regular_price'] != $offerData['regular_product_price'] ||
                $listingProductData['online_min_price'] != $offerData['minimal_product_price'] ||
                $listingProductData['online_max_price'] != $offerData['maximal_product_price']
            ) {
                $connWrite->update(
                    Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix('m2epro_amazon_listing_product_repricing'),
                    array(
                        'online_regular_price'      => $offerData['regular_product_price'],
                        'online_min_price'          => $offerData['minimal_product_price'],
                        'online_max_price'          => $offerData['maximal_product_price'],
                        'is_online_disabled'        => $offerData['is_calculation_disabled'],
                        'is_online_inactive'        => $offerData['is_offer_inactive'],
                        'last_synchronization_date' => Mage::helper('M2ePro')->getCurrentGmtDate(),
                        'update_date'               => Mage::helper('M2ePro')->getCurrentGmtDate(),
                    ),
                    array('listing_product_id = ?' => $listingProductId)
                );

                continue;
            }

            if ($listingProductData['is_online_disabled'] != $offerData['is_calculation_disabled']) {
                if ($offerData['is_calculation_disabled']) {
                    $disabledListingsProductsIds[] = $listingProductId;
                } else {
                    $enabledListingsProductsIds[] = $listingProductId;
                }
            }

            if ($listingProductData['is_online_inactive'] != $offerData['is_offer_inactive']) {
                if ($offerData['is_offer_inactive']) {
                    $inactiveListingsProductsIds[] = $listingProductId;
                } else {
                    $activeListingsProductsIds[] = $listingProductId;
                }
            }

            // we try to catch an event when the product becomes not managed for some reason
            // but it had the managed state before
            if ($listingProductData['is_online_disabled'] != $offerData['is_calculation_disabled'] ||
                $listingProductData['is_online_inactive'] != $offerData['is_offer_inactive']) {
                if (!$listingProductData['is_online_disabled'] && !$listingProductData['is_online_inactive']) {
                    $notManagedListingsProductsIds[] = $listingProductId;
                }
            }
        }

        if (!empty($notManagedListingsProductsIds)) {
            $instructionsData = array();

            foreach ($notManagedListingsProductsIds as $notManagedListingProductId) {
                $instructionsData[] = array(
                    'listing_product_id' => $notManagedListingProductId,
                    'type'               => self::INSTRUCTION_TYPE_STATUS_CHANGED,
                    'initiator'          => self::INSTRUCTION_INITIATOR,
                    'priority'           => 50,
                );
            }

            Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add($instructionsData);
        }

        $defaultParams = array(
            'last_synchronization_date' => Mage::helper('M2ePro')->getCurrentGmtDate(),
            'update_date'               => Mage::helper('M2ePro')->getCurrentGmtDate()
        );

        if (!empty($enabledListingsProductsIds)) {
            $this->multipleUpdateListings(
                'm2epro_amazon_listing_product_repricing',
                array_merge(
                    $defaultParams, array(
                    'is_online_disabled' => 0,
                    )
                ),
                $enabledListingsProductsIds
            );
        }

        if (!empty($disabledListingsProductsIds)) {
            $this->multipleUpdateListings(
                'm2epro_amazon_listing_product_repricing',
                array_merge(
                    $defaultParams, array(
                    'is_online_disabled' => 1,
                    )
                ),
                $disabledListingsProductsIds
            );
        }

        if (!empty($activeListingsProductsIds)) {
            $this->multipleUpdateListings(
                'm2epro_amazon_listing_product_repricing',
                array_merge(
                    $defaultParams, array(
                    'is_online_inactive' => 0,
                    )
                ),
                $activeListingsProductsIds
            );
        }

        if (!empty($inactiveListingsProductsIds)) {
            $this->multipleUpdateListings(
                'm2epro_amazon_listing_product_repricing',
                array_merge(
                    $defaultParams, array(
                    'is_online_inactive' => 1,
                    )
                ),
                $inactiveListingsProductsIds
            );
        }
    }

    protected function updateListingsOthersRepricing(array $updatedOffersData)
    {
        $keys = array_map(
            function($el){
            return (string)$el; 
            }, array_keys($updatedOffersData)
        );

        /** @var Ess_M2ePro_Model_Resource_Listing_Other_Collection $listingOtherCollection */
        $listingOtherCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');
        $listingOtherCollection->addFieldToFilter('account_id', $this->getAccount()->getId());
        $listingOtherCollection->addFieldToFilter('sku', array('in' => $keys));
        $listingOtherCollection->addFieldToFilter('is_repricing', 1);

        $listingOtherCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingOtherCollection->getSelect()->columns(
            array(
                'second_table.listing_other_id',
                'second_table.sku',
                'second_table.online_price',
                'second_table.is_repricing_disabled',
                'second_table.is_repricing_inactive',
            )
        );

        $listingsOthersData = $listingOtherCollection->getData();

        if (empty($listingsOthersData)) {
            return;
        }

        $resource  = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');

        $enabledListingOthersIds  = array();
        $disabledListingOthersIds = array();
        $activeListingOthersIds   = array();
        $inactiveListingOthersIds = array();

        foreach ($listingsOthersData as $listingOtherData) {
            $listingOtherId = (int)$listingOtherData['listing_other_id'];

            $offerData = $updatedOffersData[strtolower($listingOtherData['sku'])];

            if ($offerData['product_price'] !== null &&
                !$offerData['is_calculation_disabled'] && !$offerData['is_offer_inactive'] &&
                $offerData['product_price'] != $listingOtherData['online_price']
            ) {
                $connWrite->update(
                    Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix('m2epro_amazon_listing_other'),
                    array(
                        'online_price'            => $offerData['product_price'],
                        'is_repricing_disabled'   => $offerData['is_calculation_disabled'],
                        'is_repricing_inactive'   => $offerData['is_offer_inactive'],
                    ),
                    array('listing_other_id = ?' => $listingOtherId)
                );

                continue;
            }

            if ($listingOtherData['is_repricing_disabled'] != $offerData['is_calculation_disabled']) {
                if ($offerData['is_calculation_disabled']) {
                    $disabledListingOthersIds[] = $listingOtherId;
                } else {
                    $enabledListingOthersIds[] = $listingOtherId;
                }
            }

            if ($listingOtherData['is_repricing_inactive'] != !$offerData['is_offer_inactive']) {
                if ($offerData['is_offer_inactive']) {
                    $inactiveListingOthersIds[] = $listingOtherId;
                } else {
                    $activeListingOthersIds[] = $listingOtherId;
                }
            }
        }

        if (!empty($enabledListingOthersIds)) {
            $this->multipleUpdateListings(
                'm2epro_amazon_listing_other',
                array('is_repricing' => 1, 'is_repricing_disabled' => 0),
                $enabledListingOthersIds
            );
        }

        if (!empty($disabledListingOthersIds)) {
            $this->multipleUpdateListings(
                'm2epro_amazon_listing_other',
                array('is_repricing' => 1, 'is_repricing_disabled' => 1),
                $disabledListingOthersIds
            );
        }

        if (!empty($activeListingOthersIds)) {
            $this->multipleUpdateListings(
                'm2epro_amazon_listing_other',
                array('is_repricing' => 1, 'is_repricing_inactive' => 0),
                $activeListingOthersIds
            );
        }

        if (!empty($inactiveListingOthersIds)) {
            $this->multipleUpdateListings(
                'm2epro_amazon_listing_other',
                array('is_repricing' => 1, 'is_repricing_inactive' => 1),
                $inactiveListingOthersIds
            );
        }
    }

    //----------------------------------------

    protected function removeListingsProductsRepricing(array $removedOffersSkus)
    {
        $resourceModel = Mage::getResourceModel('M2ePro/Amazon_Listing_Product');
        $listingsProductsData = $resourceModel->getProductsDataBySkus(
            $removedOffersSkus,
            array(
                'l.account_id' => $this->getAccount()->getId(),
            ),
            array(
                'main_table.id',
                'second_table.variation_parent_id',
            )
        );

        if (empty($listingsProductsData)) {
            return;
        }

        $listingProductIds = array();

        foreach ($listingsProductsData as $listingProductData) {
            $listingProductIds[] = (int)$listingProductData['id'];
            $parentListingProductId = (int)$listingProductData['variation_parent_id'];

            if ($parentListingProductId && !in_array($parentListingProductId, $this->_parentProductsIds)) {
                $this->_parentProductsIds[] = $parentListingProductId;
            }
        }

        $resource  = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');

        foreach (array_chunk($listingProductIds, 1000, true) as $listingProductIdsPack) {
            $connWrite->delete(
                Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('m2epro_amazon_listing_product_repricing'),
                array('listing_product_id IN (?)' => $listingProductIdsPack)
            );

            $connWrite->update(
                Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('m2epro_amazon_listing_product'),
                array('is_repricing' => 0),
                array('listing_product_id IN (?)' => $listingProductIdsPack)
            );
        }
    }

    protected function removeListingsOthersRepricing(array $removedOffersSkus)
    {
        $resourceModel = Mage::getResourceModel('M2ePro/Amazon_Listing_Other');
        $listingsOthersData = $resourceModel->getProductsDataBySkus(
            $removedOffersSkus,
            array(
                'account_id' => $this->getAccount()->getId()
            ),
            array(
                'main_table.id'
            )
        );

        if (empty($listingsOthersData)) {
            return;
        }

        $listingOtherIds = array();
        foreach ($listingsOthersData as $listingsOtherData) {
            $listingOtherIds[] = (int)$listingsOtherData['id'];
        }

        $resource  = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');

        foreach (array_chunk($listingOtherIds, 1000, true) as $listingOtherIdsPack) {
            $connWrite->update(
                Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('m2epro_amazon_listing_other'),
                array(
                    'is_repricing'            => 0,
                    'is_repricing_disabled'   => 0,
                    'is_repricing_inactive'   => 0
                ),
                array('listing_other_id IN (?)' => $listingOtherIdsPack)
            );
        }
    }

    //########################################

    protected function processVariationProcessor()
    {
        if (empty($this->_parentProductsIds)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('is_variation_parent', 1);
        $listingProductCollection->addFieldToFilter('id', array('in' => $this->_parentProductsIds));

        foreach ($listingProductCollection->getItems() as $item) {
            /** @var Ess_M2ePro_Model_Listing_Product $item */
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $alp */

            $alp = $item->getChildObject();
            $alp->getVariationManager()->getTypeModel()->getProcessor()->process();
        }

        $this->_parentProductsIds = array();
    }

    //########################################

    protected function multipleUpdateListings($tableName, $params, $listingsIds)
    {
        $resource  = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');
        if ($tableName === 'm2epro_amazon_listing_product_repricing') {
            $tableId = 'listing_product_id';
        } else {
            $tableId = 'listing_other_id';
        }

        $tableName = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($tableName);

        $listingsIdsPacks = array_chunk(array_unique($listingsIds), 1000);

        foreach ($listingsIdsPacks as $listingsIdsPack) {
            $connWrite->update(
                $tableName,
                $params,
                array($tableId . ' IN (?)' => $listingsIdsPack)
            );
        }
    }

    //########################################
}
