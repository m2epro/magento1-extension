<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Repricing_Synchronization_ActualPrice
    extends Ess_M2ePro_Model_Amazon_Repricing_Synchronization_Abstract
{
    //########################################

    public function run($skus = NULL)
    {
        $requestSkus = array_unique(array_merge(
            Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->getSkus(
                $this->getAccount(), $skus, false
            ),
            Mage::getResourceModel('M2ePro/Amazon_Listing_Other')->getRepricingSkus(
                $this->getAccount(), $skus, false
            )
        ));

        if (empty($requestSkus)) {
            return false;
        }

        $response = $this->sendRequest(array(
            'skus_list' => $requestSkus,
        ));

        if ($response === false || empty($response['status'])) {
            return false;
        }

        $offersProductPrices = array();
        foreach ($response['offers'] as $offerData) {
            $productPrice = $offerData['product_price'];
            if (is_null($productPrice)) {
                continue;
            }

            $offersProductPrices[strtolower($offerData['sku'])] = $productPrice;
        }

        if (empty($offersProductPrices)) {
            return true;
        }

        $this->updateListingsProductsPrices($offersProductPrices);
        $this->updateListingsOthersPrices($offersProductPrices);

        return true;
    }

    //########################################

    protected function getMode()
    {
        return self::MODE_ACTUAL_PRICE;
    }

    //########################################

    private function updateListingsProductsPrices(array $offersProductPrices)
    {
        $keys = array_map(function($el){ return (string)$el; }, array_keys($offersProductPrices));

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
                'second_table.online_regular_price',
            )
        );

        $listingsProductsData = $listingProductCollection->getData();

        $resource  = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');

        foreach ($listingsProductsData as $listingProductData) {
            $listingProductId = (int)$listingProductData['listing_product_id'];

            $offerProductPrice = $offersProductPrices[strtolower($listingProductData['sku'])];

            if (!is_null($offerProductPrice) &&
                $listingProductData['online_regular_price'] != $offerProductPrice
            ) {
                $connWrite->update(
                    Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix('m2epro_amazon_listing_product'),
                    array('online_regular_price' => $offerProductPrice),
                    array('listing_product_id = ?' => $listingProductId)
                );
            }
        }
    }

    private function updateListingsOthersPrices(array $offersProductPrices)
    {
        $keys = array_map(function($el){ return (string)$el; }, array_keys($offersProductPrices));

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
            )
        );

        $listingsOthersData = $listingOtherCollection->getData();

        if (empty($listingsOthersData)) {
            return;
        }

        $resource  = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');

        foreach ($listingsOthersData as $listingOtherData) {
            $listingOtherId = (int)$listingOtherData['listing_other_id'];

            $offerProductPrice = $offersProductPrices[strtolower($listingOtherData['sku'])];

            if (!is_null($offerProductPrice) &&
                $offerProductPrice != $listingOtherData['online_price']
            ) {
                $connWrite->update(
                    Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix('m2epro_amazon_listing_other'),
                    array(
                        'online_price' => $offerProductPrice,
                    ),
                    array('listing_other_id = ?' => $listingOtherId)
                );
            }
        }
    }

    //########################################
}