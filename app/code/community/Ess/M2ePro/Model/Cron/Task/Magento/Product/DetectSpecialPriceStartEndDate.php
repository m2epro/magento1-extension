<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Magento_Product_DetectSpecialPriceStartEndDate extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'magento/product/detect_special_price_start_end_date';

    /** @var int (in seconds) */
    protected $_interval = 7200;

    protected function performActions()
    {
        if ($this->getLastProcessedProductId() === null) {
            $this->setLastProcessedProductId(0);
        }

        $changedProductsPrice = $this->getAllChangedProductsPrice();

        if (!$changedProductsPrice) {
            $this->setLastProcessedProductId(0);
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::getModel('M2ePro/Listing_Product')->getCollection();
        $collection->addFieldToFilter('product_id', array('in' => array_keys($changedProductsPrice)));
        $collection->addFieldToFilter('status', array('neq' => 0));

        /** @var Ess_M2ePro_PublicServices_Product_SqlChange $changesModel */
        $changesModel = Mage::getModel('M2ePro_PublicServices/Product_SqlChange');

        /** @var  Ess_M2ePro_Model_Listing_Product $listingProduct */
        foreach ($collection->getItems() as $listingProduct) {
            $currentPrice = (float)$this->getCurrentPrice($listingProduct);
            $newPrice = (float)$changedProductsPrice[$listingProduct->getProductId()]['price'];


            if ($currentPrice == $newPrice) {
                continue;
            }

            $changesModel->markPriceChanged($listingProduct->getProductId());
        }

        $changesModel->applyChanges();

        $lastMagentoProduct = $this->getArrayKeyLast($changedProductsPrice);
        $this->setLastProcessedProductId((int)$lastMagentoProduct);
    }

    private function getAllChangedProductsPrice()
    {
        $currentDate = Mage::helper('M2ePro')->createCurrentGmtDateTime();
        $toDate = clone $currentDate;
        $toDate->modify('-1 day');

        $specialFromDateResults = $this->getChangedProductPricesByDate('special_from_date', $currentDate);
        $specialToDateResults = $this->getChangedProductPricesByDate('special_to_date', $toDate);

        $allChangedProductsPrice = $specialToDateResults + $specialFromDateResults;

        ksort($allChangedProductsPrice);

        return array_slice($allChangedProductsPrice, 0, 1000, true);
    }

    private function getChangedProductPricesByDate($attributeCode, \DateTime $date)
    {
        $changedProductsPrice = array();

        foreach ($this->getAllStoreIds() as $storeId) {
            $productCollection = $this->getProductCollection($attributeCode, $storeId, $date);

            /** @var Mage_Catalog_Model_Product $magentoProduct */
            foreach ($productCollection->getItems() as $magentoProduct) {
                $magentoProductId = $magentoProduct->getId();
                $price = ($attributeCode === 'special_from_date')
                    ? $magentoProduct->getSpecialPrice()
                    : $magentoProduct->getPrice();

                $changedProductsPrice[$magentoProductId] = array(
                    'price' => $price,
                );
            }
        }

        return $changedProductsPrice;
    }

    private function getProductCollection($attributeCode, $storeId, \DateTime $date
    ) {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();

        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect('price');
        $collection->addAttributeToFilter('special_price', array('notnull' => true));
        $collection->addFieldToFilter($attributeCode, array('notnull' => true));
        $collection->addFieldToFilter($attributeCode, array('lt' => $date->format('Y-m-d H:i:s')));
        $collection->addFieldToFilter('entity_id', array('gt' => (int)$this->getLastProcessedProductId()));
        $collection->setOrder('entity_id', 'asc');
        $collection->getSelect()->limit(1000);

        return $collection;
    }

    private function getArrayKeyLast($array)
    {
        if (empty($array)) {
            return NULL;
        }

        $arrayKeys = array_keys($array);
        return $arrayKeys[count($array)-1];
    }

    private function getCurrentPrice(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if ($listingProduct->isComponentModeAmazon()) {
            return $listingProduct->getChildObject()->getOnlineRegularPrice();
        } elseif ($listingProduct->isComponentModeEbay()) {
            return $listingProduct->getChildObject()->getOnlineCurrentPrice();
        } elseif ($listingProduct->isComponentModeWalmart()) {
            return $listingProduct->getChildObject()->getOnlinePrice();
        } else {
            throw new Ess_M2ePro_Model_Exception_Logic('Component Mode is not defined.');
        }
    }

    private function getAllStoreIds()
    {
        $storeIds = array();

        /** @var Ess_M2ePro_Model_Resource_Listing_Collection $collectionListing */
        $collectionListing = Mage::getModel('M2ePro/Listing')->getCollection();
        $collectionListing->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collectionListing->getSelect()->columns(array('store_id' => 'store_id'));
        $collectionListing->getSelect()->group('store_id');

        foreach ($collectionListing->getData() as $item){
            $storeIds[] = $item['store_id'];
        }

        return $storeIds;
    }

    private function getLastProcessedProductId()
    {
        return Mage::helper('M2ePro/Module')->getRegistry()->getValue(
            '/magento/product/detect_special_price_start_end_date/last_magento_product_id/'
        );
    }

    private function setLastProcessedProductId($magentoProductId)
    {
        Mage::helper('M2ePro/Module')->getRegistry()->setValue(
            '/magento/product/detect_special_price_start_end_date/last_magento_product_id/',
            (int)$magentoProductId
        );
    }
}
