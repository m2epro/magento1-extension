<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Amazon_Listing_RepricingController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_MainController
{
    public function indexAction()
    {
        $listingId = $this->getRequest()->getParam('id');

        return $this->_redirect(
            '*/adminhtml_amazon_listing/view', array(
            'id' => $listingId
            )
        );
    }

    public function getUpdatedPriceBySkusAction()
    {
        $groupedSkus = $this->getRequest()->getParam('grouped_skus');

        if (empty($groupedSkus)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $groupedSkus = Mage::helper('M2ePro')->jsonDecode($groupedSkus);
        $resultPrices = array();

        foreach ($groupedSkus as $accountId => $skus) {
            /** @var Ess_M2ePro_Model_Account $account */
            $account = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Account', $accountId);

            /** @var Ess_M2ePro_Model_Amazon_Account $amazonAccount */
            $amazonAccount = $account->getChildObject();

            $currency = $amazonAccount->getMarketplace()->getChildObject()->getDefaultCurrency();

            $repricingSynchronization = Mage::getModel('M2ePro/Amazon_Repricing_Synchronization_ActualPrice', $account);
            $repricingSynchronization->run($skus);

            /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
            $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
            $listingProductCollection->getSelect()->joinLeft(
                array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
                'l.id = main_table.listing_id',
                array()
            );
            $listingProductCollection->addFieldToFilter('l.account_id', $accountId);
            $listingProductCollection->addFieldToFilter('sku', array('in' => $skus));

            $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $listingProductCollection->getSelect()->columns(
                array(
                    'second_table.sku',
                    'second_table.online_regular_price'
                )
            );

            $listingsProductsData = $listingProductCollection->getData();

            foreach ($listingsProductsData as $listingProductData) {
                $price = Mage::app()->getLocale()
                    ->currency($currency)
                    ->toCurrency($listingProductData['online_regular_price']);
                $resultPrices[$accountId][$listingProductData['sku']] = $price;
            }

            /** @var Ess_M2ePro_Model_Resource_Listing_Other_Collection $listingOtherCollection */
            $listingOtherCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');

            $listingOtherCollection->addFieldToFilter('account_id', $accountId);
            $listingOtherCollection->addFieldToFilter('sku', array('in' => $skus));

            $listingOtherCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $listingOtherCollection->getSelect()->columns(
                array(
                    'second_table.sku',
                    'second_table.online_price'
                )
            );

            $listingsOthersData = $listingOtherCollection->getData();

            foreach ($listingsOthersData as $listingOtherData) {
                $price = Mage::app()->getLocale()->currency($currency)->toCurrency($listingOtherData['online_price']);
                $resultPrices[$accountId][$listingOtherData['sku']] = $price;
            }
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($resultPrices));
    }

    protected function addRepricingMessages($messages)
    {
        foreach ($messages as $message) {
            if ($message['type'] == 'notice') {
                $this->_getSession()->addNotice($message['text']);
            }

            if ($message['type'] == 'warning') {
                $this->_getSession()->addWarning($message['text']);
            }

            if ($message['type'] == 'error') {
                $this->_getSession()->addError($message['text']);
            }
        }
    }

    protected function validateRegularPrice($productsIds)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableAmazonListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_listing_product');

        $select = $connRead->select();

        // selecting all products with online_regular_price
        $select->from($tableAmazonListingProduct, 'listing_product_id')
            ->where('online_regular_price IS NOT NULL');

        $select->where('listing_product_id IN (?)', $productsIds);

        return Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($select);
    }
}
