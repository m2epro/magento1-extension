<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Magento_Product_DetectSpecialPriceEndDate extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'magento/product/detect_special_price_end_date';

    /**
     * @var int (in seconds)
     */
    protected $_interval = 7200;

    //########################################

    protected function performActions()
    {
        if ($this->getLastProcessedProductId() === null) {
            $this->setLastProcessedProductId($this->getFirstProductId());
        }

        $changedProductsPrice = array();
        $magentoProducts = $this->getChangedProductsPrice();

        /** @var Mage_Catalog_Model_Product $magentoProduct */
        foreach ($magentoProducts as $magentoProduct) {
            $changedProductsPrice[$magentoProduct->getId()] = array(
                'price' => $magentoProduct->getPrice()
            );
        }

        if (!$changedProductsPrice) {
            $this->setLastProcessedProductId($this->getFirstProductId());
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::getModel('M2ePro/Listing_Product')->getCollection();
        $collection->addFieldToFilter('product_id', array('in' => array_keys($changedProductsPrice)));

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

        $lastMagentoProduct = array_pop($magentoProducts);
        $this->setLastProcessedProductId((int)$lastMagentoProduct->getId());
    }

    //########################################

    protected function getCurrentPrice(Ess_M2ePro_Model_Listing_Product $listingProduct)
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

    //########################################

    protected function getFirstProductId()
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->setOrder('entity_id', 'asc');
        $collection->setPageSize(1);
        $collection->setCurPage(1);

        return (int)$collection->getFirstItem()->getId();
    }

    protected function getChangedProductsPrice()
    {
        $date = new DateTime('now', new DateTimeZone('UTC'));
        $date->modify('-1 day');

        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect('price');
        $collection->addAttributeToFilter('special_price', array('notnull' => true));
        $collection->addAttributeToFilter('special_to_date', array('notnull' => true));
        $collection->addAttributeToFilter('special_to_date', array('lt' => $date->format('Y-m-d H:i:s')));
        $collection->addFieldToFilter('entity_id', array('gteq' => (int)$this->getLastProcessedProductId()));
        $collection->setOrder('entity_id', 'asc');
        $collection->getSelect()->limit(1000);

        return $collection->getItems();
    }

    // ---------------------------------------

    protected function getLastProcessedProductId()
    {
        return Mage::helper('M2ePro/Module')->getRegistry()->getValue(
            '/magento/product/detect_special_price_end_date/last_magento_product_id/'
        );
    }

    protected function setLastProcessedProductId($magentoProductId)
    {
        Mage::helper('M2ePro/Module')->getRegistry()->setValue(
            '/magento/product/detect_special_price_end_date/last_magento_product_id/',
            (int)$magentoProductId
        );
    }

    //########################################
}