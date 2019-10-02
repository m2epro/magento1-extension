<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Repricing_UpdateSettings extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'amazon/repricing/update_settings';

    const MAX_COUNT_OF_ITERATIONS     = 10;
    const MAX_ITEMS_COUNT_PER_REQUEST = 500;

    //####################################

    public function performActions()
    {
        $permittedAccounts = $this->getPermittedAccounts();
        if (empty($permittedAccounts)) {
            return;
        }

        foreach ($permittedAccounts as $permittedAccount) {
            $this->processAccount($permittedAccount);
        }
    }

    //####################################

    /**
     * @return Ess_M2ePro_Model_Account[]
     */
    protected function getPermittedAccounts()
    {
        /** @var Ess_M2ePro_Model_Resource_Account_Collection $accountCollection */
        $accountCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        $accountCollection->getSelect()->joinInner(
            array('aar' => Mage::getResourceModel('M2ePro/Amazon_Account_Repricing')->getMainTable()),
            'aar.account_id=main_table.id', array()
        );

        return $accountCollection->getItems();
    }

    protected function processAccount(Ess_M2ePro_Model_Account $acc)
    {
        /** @var Ess_M2ePro_Model_Amazon_Repricing_Updating $repricingUpdating */
        $repricingUpdating        = Mage::getModel('M2ePro/Amazon_Repricing_Updating', $acc);
        /** @var Ess_M2ePro_Model_Amazon_Repricing_Synchronization_General $repricingSynchronization */
        $repricingSynchronization = Mage::getModel('M2ePro/Amazon_Repricing_Synchronization_General', $acc);

        $iteration = 0;
        while (($products = $this->getProcessRequiredProducts($acc)) && $iteration <= self::MAX_COUNT_OF_ITERATIONS) {
            $iteration++;

            $updatedSkus = $repricingUpdating->process($products);
            $this->getLockItemManager()->activate();

            Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->resetProcessRequired(
                array_unique(array_keys($products))
            );

            if (empty($updatedSkus)) {
                continue;
            }

            $repricingSynchronization->run($updatedSkus);
        }
    }

    /**
     * @param $account Ess_M2ePro_Model_Account
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Repricing[]
     */
    protected function getProcessRequiredProducts(Ess_M2ePro_Model_Account $account)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->getSelect()->joinLeft(
            array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            'l.id=main_table.listing_id',
            array()
        );
        $listingProductCollection->addFieldToFilter('is_variation_parent', 0);
        $listingProductCollection->addFieldToFilter('is_repricing', 1);
        $listingProductCollection->addFieldToFilter('l.account_id', $account->getId());
        $listingProductCollection->addFieldToFilter(
            'status',
            array('in' => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
                Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN
            ))
        );

        $listingProductCollection->getSelect()->joinInner(
            array('alpr' => Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->getMainTable()),
            'alpr.listing_product_id=main_table.id',
            array()
        );
        $listingProductCollection->addFieldToFilter('alpr.is_process_required', true);

        $listingProductCollection->getSelect()->limit(self::MAX_ITEMS_COUNT_PER_REQUEST);

        /** @var Ess_M2ePro_Model_Listing_Product[] $listingsProducts */
        $listingsProducts = $listingProductCollection->getItems();
        if (empty($listingsProducts)) {
            return array();
        }

        $listingProductRepricingCollection = Mage::getResourceModel(
            'M2ePro/Amazon_Listing_Product_Repricing_Collection'
        );
        $listingProductRepricingCollection->addFieldToFilter(
            'listing_product_id', array('in' => $listingProductCollection->getColumnValues('id'))
        );

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Repricing[] $listingsProductsRepricing */
        $listingsProductsRepricing = $listingProductRepricingCollection->getItems();

        foreach ($listingsProductsRepricing as $listingProductRepricing) {
            $listingProductRepricing->setListingProduct(
                $listingProductCollection->getItemById($listingProductRepricing->getListingProductId())
            );
        }

        return $listingsProductsRepricing;
    }

    //####################################
}
