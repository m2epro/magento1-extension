<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Repricing_Synchronization_General
    extends Ess_M2ePro_Model_Amazon_Repricing_Synchronization_Abstract
{
    private $parentProductsIds = array();

    //########################################

    public function run($skus = NULL)
    {
        $filters = array();
        if (!is_null($skus)) {
            $filters = array(
                'skus_list' => $skus,
            );
        }

        $response = $this->sendRequest($filters);

        if ($response === false || empty($response['status'])) {
            return false;
        }

        if (!empty($response['email'])) {
            $this->getAmazonAccountRepricing()->setData('email', $response['email']);
        }

        if (empty($skus)) {
            $this->getAmazonAccountRepricing()->setData('total_products', count($response['offers']));
            $this->getAmazonAccountRepricing()->save();
        }

        $existedSkus = array_unique(array_merge(
            Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->getAllSkus($this->getAccount()),
            Mage::getResourceModel('M2ePro/Amazon_Listing_Other')->getAllRepricingSkus($this->getAccount())
        ));

        if (!is_null($skus)) {
            $existedSkus = array_intersect($skus, $existedSkus);
        }

        $existedSkus = array_map('strtolower', $existedSkus);

        $skuIndexedResultOffersData = array();
        foreach ($response['offers'] as $offerData) {
            $skuIndexedResultOffersData[strtolower($offerData['sku'])] = $offerData;
        }

        $this->processNewOffers($skuIndexedResultOffersData, $existedSkus);
        $this->processRemovedOffers($skuIndexedResultOffersData, $existedSkus);
        $this->processUpdatedOffers($skuIndexedResultOffersData, $existedSkus);

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

    private function processNewOffers(array $resultOffersData, array $existedSkus)
    {
        $newOffersSkus = array_diff(array_keys($resultOffersData), $existedSkus);
        if (empty($newOffersSkus)) {
            return;
        }

        $newOffersData = array();
        foreach ($newOffersSkus as $newOfferSku) {
            $newOffersData[$newOfferSku] = $resultOffersData[$newOfferSku];
        }

        $this->addListingsProductsRepricing($newOffersData);
        $this->addListingOthersRepricing($newOffersData);
    }

    private function processRemovedOffers(array $resultOffersData, array $existedSkus)
    {
        $removedOffersSkus = array_diff($existedSkus, array_keys($resultOffersData));
        if (empty($removedOffersSkus)) {
            return;
        }

        $this->removeListingsProductsRepricing($removedOffersSkus);
        $this->removeListingsOthersRepricing($removedOffersSkus);
    }

    private function processUpdatedOffers(array $resultOffersData, array $existedSkus)
    {
        $updatedOffersSkus = array_intersect($existedSkus, array_keys($resultOffersData));
        if (empty($updatedOffersSkus)) {
            return;
        }

        $updatedOffersData = array();
        foreach ($updatedOffersSkus as $updatedOfferSku) {
            $updatedOffersData[$updatedOfferSku] = $resultOffersData[$updatedOfferSku];
        }

        $this->updateListingsProductsRepricing($updatedOffersData);
        $this->updateListingsOthersRepricing($updatedOffersData);
    }

    //########################################

    private function addListingsProductsRepricing(array $newOffersData)
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
                'second_table.online_price',
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
                'listing_product_id'   => $listingProductId,
                'online_regular_price' => $offerData['regular_product_price'],
                'online_min_price'     => $offerData['minimal_product_price'],
                'online_max_price'     => $offerData['maximal_product_price'],
                'is_online_disabled'   => $offerData['is_calculation_disabled'],
                'update_date'          => Mage::helper('M2ePro')->getCurrentGmtDate(),
                'create_date'          => Mage::helper('M2ePro')->getCurrentGmtDate(),
            );

            if (!is_null($offerData['product_price']) &&
                $offerData['product_price'] != $listingProductData['online_price']
            ) {
                $connWrite->update(
                    $resource->getTableName('m2epro_amazon_listing_product'),
                    array('online_price' => $offerData['product_price']),
                    array('listing_product_id = ?' => $listingProductId)
                );
            }

            if ($parentListingProductId && !in_array($parentListingProductId, $this->parentProductsIds)) {
                $this->parentProductsIds[] = $parentListingProductId;
            }
        }

        foreach (array_chunk($insertData, 1000, true) as $insertDataPack) {

            $connWrite->insertMultiple(
                $resource->getTableName('m2epro_amazon_listing_product_repricing'),
                $insertDataPack
            );

            $connWrite->update(
                $resource->getTableName('m2epro_amazon_listing_product'),
                array(
                    'is_repricing'                 => 1,
                    'online_sale_price'            => 0,
                    'online_sale_price_start_date' => NULL,
                    'online_sale_price_end_date'   => NULL,
                ),
                array('listing_product_id IN (?)' => array_keys($insertDataPack))
            );
        }
    }

    private function addListingOthersRepricing(array $newOffersData)
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

        $disabledListingOthersIds = array();
        $enabledListingOthersIds  = array();

        foreach ($listingsOthersData as $listingOtherData) {

            $listingOtherId = (int)$listingOtherData['listing_other_id'];
            $offerData = $newOffersData[strtolower($listingOtherData['sku'])];

            if (!is_null($offerData['product_price']) &&
                $offerData['product_price'] != $listingOtherData['online_price']
            ) {
                $connWrite->update(
                    $resource->getTableName('m2epro_amazon_listing_other'),
                    array(
                        'online_price'          => $offerData['product_price'],
                        'is_repricing'          => 1,
                        'is_repricing_disabled' => $offerData['is_calculation_disabled'],
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
        }

        if (!empty($disabledListingOthersIds)) {

            $disabledListingOthersIdsPacks = array_chunk(array_unique($disabledListingOthersIds), 1000);

            foreach ($disabledListingOthersIdsPacks as $disabledListingOthersIdsPack) {
                $connWrite->update(
                    $resource->getTableName('m2epro_amazon_listing_other'),
                    array(
                        'is_repricing'          => 1,
                        'is_repricing_disabled' => 1,
                    ),
                    array('listing_other_id IN (?)' => $disabledListingOthersIdsPack)
                );
            }
        }

        if (!empty($enabledListingOthersIds)) {

            $enabledListingOthersIdsPacks = array_chunk(array_unique($enabledListingOthersIds), 1000);

            foreach ($enabledListingOthersIdsPacks as $enabledListingOthersIdsPack) {
                $connWrite->update(
                    $resource->getTableName('m2epro_amazon_listing_other'),
                    array(
                        'is_repricing'          => 1,
                        'is_repricing_disabled' => 0,
                    ),
                    array('listing_other_id IN (?)' => $enabledListingOthersIdsPack)
                );
            }
        }
    }

    //----------------------------------------

    private function updateListingsProductsRepricing(array $updatedOffersData)
    {
        $keys = array_map(function($el){ return (string)$el; }, array_keys($updatedOffersData));

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
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
                'second_table.online_price',
                'alpr.is_online_disabled',
                'alpr.online_regular_price',
                'alpr.online_min_price',
                'alpr.online_max_price'
            )
        );

        $listingsProductsData = $listingProductCollection->getData();

        $resource  = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');

        $disabledListingsProductsIds = array();
        $disabledProductsIds = array();

        $enabledListingsProductsIds  = array();

        foreach ($listingsProductsData as $listingProductData) {
            $listingProductId = (int)$listingProductData['listing_product_id'];

            $offerData = $updatedOffersData[strtolower($listingProductData['sku'])];

            if (!is_null($offerData['product_price']) &&
                $listingProductData['online_price'] != $offerData['product_price']
            ) {
                $connWrite->update(
                    $resource->getTableName('m2epro_amazon_listing_product'),
                    array('online_price' => $offerData['product_price']),
                    array('listing_product_id = ?' => $listingProductId)
                );
            }

            if ($listingProductData['online_regular_price'] != $offerData['regular_product_price'] ||
                $listingProductData['online_min_price'] != $offerData['minimal_product_price'] ||
                $listingProductData['online_max_price'] != $offerData['maximal_product_price']
            ) {
                $connWrite->update(
                    $resource->getTableName('m2epro_amazon_listing_product_repricing'),
                    array(
                        'online_regular_price' => $offerData['regular_product_price'],
                        'online_min_price'     => $offerData['minimal_product_price'],
                        'online_max_price'     => $offerData['maximal_product_price'],
                        'is_online_disabled'   => $offerData['is_calculation_disabled'],
                        'update_date'          => Mage::helper('M2ePro')->getCurrentGmtDate(),
                    ),
                    array('listing_product_id = ?' => $listingProductId)
                );

                continue;
            }

            if ($listingProductData['is_online_disabled'] != $offerData['is_calculation_disabled']) {
                if ($offerData['is_calculation_disabled']) {
                    $disabledListingsProductsIds[] = $listingProductId;
                    $disabledProductsIds[] = (int)$listingProductData['product_id'];
                } else {
                    $enabledListingsProductsIds[] = $listingProductId;
                }
            }
        }

        if (!empty($disabledListingsProductsIds)) {

            $disabledListingsProductsIdsPacks = array_chunk(array_unique($disabledListingsProductsIds), 1000);

            foreach ($disabledListingsProductsIdsPacks as $disabledListingsProductsIdsPack) {
                $connWrite->update(
                    $resource->getTableName('m2epro_amazon_listing_product_repricing'),
                    array(
                        'is_online_disabled' => 1,
                        'update_date'        => Mage::helper('M2ePro')->getCurrentGmtDate(),
                    ),
                    array('listing_product_id IN (?)' => $disabledListingsProductsIdsPack)
                );
            }
        }

        if (!empty($disabledProductsIds)) {

            foreach ($disabledProductsIds as $disabledProductId) {
                Mage::getModel('M2ePro/ProductChange')->addUpdateAction(
                    $disabledProductId, Ess_M2ePro_Model_ProductChange::INITIATOR_SYNCHRONIZATION
                );
            }
        }

        if (!empty($enabledListingsProductsIds)) {

            $enabledListingsProductsIdsPacks = array_chunk(array_unique($enabledListingsProductsIds), 1000);

            foreach ($enabledListingsProductsIdsPacks as $enabledListingsProductsIdsPack) {
                $connWrite->update(
                    $resource->getTableName('m2epro_amazon_listing_product_repricing'),
                    array(
                        'is_online_disabled' => 0,
                        'update_date'        => Mage::helper('M2ePro')->getCurrentGmtDate(),
                    ),
                    array('listing_product_id IN (?)' => $enabledListingsProductsIdsPack)
                );
            }
        }
    }

    private function updateListingsOthersRepricing(array $updatedOffersData)
    {
        $keys = array_map(function($el){ return (string)$el; }, array_keys($updatedOffersData));

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Other_Collection $listingOtherCollection */
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
            )
        );

        $listingsOthersData = $listingOtherCollection->getData();

        if (empty($listingsOthersData)) {
            return;
        }

        $resource  = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');

        $disabledListingOthersIds = array();
        $enabledListingOthersIds  = array();

        foreach ($listingsOthersData as $listingOtherData) {
            $listingOtherId = (int)$listingOtherData['listing_other_id'];

            $offerData = $updatedOffersData[strtolower($listingOtherData['sku'])];

            if (!is_null($offerData['product_price']) &&
                $offerData['product_price'] != $listingOtherData['online_price']
            ) {
                $connWrite->update(
                    $resource->getTableName('m2epro_amazon_listing_other'),
                    array(
                        'online_price'          => $offerData['product_price'],
                        'is_repricing_disabled' => $offerData['is_calculation_disabled'],
                    ),
                    array('listing_other_id = ?' => $listingOtherId)
                );

                continue;
            }

            if ($listingOtherData['is_repricing_disabled'] != $offerData['is_calculation_disabled']) {
                $offerData['is_calculation_disabled'] && $disabledListingOthersIds[] = $listingOtherId;
                !$offerData['is_calculation_disabled'] && $enabledListingOthersIds[] = $listingOtherId;
            }
        }

        if (!empty($disabledListingOthersIds)) {

            $disabledListingOthersIdsPacks = array_chunk(array_unique($disabledListingOthersIds), 1000);

            foreach ($disabledListingOthersIdsPacks as $disabledListingOthersIdsPack) {
                $connWrite->update(
                    $resource->getTableName('m2epro_amazon_listing_other'),
                    array('is_repricing_disabled' => 1),
                    array('listing_other_id IN (?)' => $disabledListingOthersIdsPack)
                );
            }
        }

        if (!empty($enabledListingOthersIds)) {

            $enabledListingOthersIdsPacks = array_chunk(array_unique($enabledListingOthersIds), 1000);

            foreach ($enabledListingOthersIdsPacks as $enabledListingOthersIdsPack) {
                $connWrite->update(
                    $resource->getTableName('m2epro_amazon_listing_other'),
                    array('is_repricing_disabled' => 0),
                    array('listing_other_id IN (?)' => $enabledListingOthersIdsPack)
                );
            }
        }
    }

    //----------------------------------------

    private function removeListingsProductsRepricing(array $removedOffersSkus)
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

            if ($parentListingProductId && !in_array($parentListingProductId, $this->parentProductsIds)) {
                $this->parentProductsIds[] = $parentListingProductId;
            }
        }

        $resource  = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');

        foreach (array_chunk($listingProductIds, 1000, true) as $listingProductIdsPack) {

            $connWrite->delete(
                $resource->getTableName('m2epro_amazon_listing_product_repricing'),
                array('listing_product_id IN (?)' => $listingProductIdsPack)
            );

            $connWrite->update(
                $resource->getTableName('m2epro_amazon_listing_product'),
                array('is_repricing' => 0),
                array('listing_product_id IN (?)' => $listingProductIdsPack)
            );
        }
    }

    private function removeListingsOthersRepricing(array $removedOffersSkus)
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
                $resource->getTableName('m2epro_amazon_listing_other'),
                array(
                    'is_repricing'          => 0,
                    'is_repricing_disabled' => 0
                ),
                array('listing_other_id IN (?)' => $listingOtherIdsPack)
            );
        }
    }

    //########################################

    private function processVariationProcessor()
    {
        if (empty($this->parentProductsIds)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('is_variation_parent', 1);
        $listingProductCollection->addFieldToFilter('id', array('in' => $this->parentProductsIds));

        foreach ($listingProductCollection->getItems() as $item) {
            /** @var Ess_M2ePro_Model_Listing_Product $item */
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $alp */

            $alp = $item->getChildObject();
            $alp->getVariationManager()->getTypeModel()->getProcessor()->process();
        }

        $this->parentProductsIds = array();
    }

    //########################################
}