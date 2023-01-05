<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Repricing_Synchronize
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'amazon/repricing/synchronize';

    const REGISTRY_GENERAL_START_DATE = '/amazon/repricing/synchronize/general/start_date/';

    const REGISTRY_GENERAL_LAST_LISTING_PRODUCT_ID = '/amazon/repricing/synchronize/general/last_listing_product_id/';
    const REGISTRY_GENERAL_LAST_LISTING_OTHER_ID = '/amazon/repricing/synchronize/general/last_other_product_id/';

    const REGISTRY_ACTUAL_PRICE_START_DATE = '/amazon/repricing/synchronize/actual_price/start_date/';

    const REGISTRY_ACTUAL_PRICE_LAST_LISTING_PRODUCT_ID =
        '/amazon/repricing/synchronize/actual_price/last_listing_product_id/';
    const REGISTRY_ACTUAL_PRICE_LAST_LISTING_OTHER_ID =
        '/amazon/repricing/synchronize/actual_price/last_other_product_id/';

    const SYNCHRONIZE_GENERAL_INTERVAL = 60;
    const SYNCHRONIZE_ACTUAL_PRICE_INTERVAL = 60;

    const PRODUCTS_COUNT_BY_ACCOUNT_AND_PRODUCT_TYPE = 5000;

    //####################################

    protected function getNick()
    {
        return self::NICK;
    }

    //####################################

    public function performActions()
    {
        $accounts = Mage::getResourceModel('M2ePro/Account_Collection')
            ->getAccountsWithValidRepricingAccount();
        foreach ($accounts as $account) {
            if ($this->isPossibleToSynchronizeGeneral($account)) {
                $this->synchronizeGeneral($account);
            }
        }

        $accounts = Mage::getResourceModel('M2ePro/Account_Collection')
            ->getAccountsWithValidRepricingAccount();
        foreach ($accounts as $account) {
            if ($this->isPossibleToSynchronizeActualPrice($account)) {
                $this->synchronizeActualPrice($account);
            }
        }
    }

    //####################################

    /**
     * @param Ess_M2ePro_Model_Account $account
     */
    protected function synchronizeGeneral($account)
    {
        // Listing Products
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->getSelect()->joinLeft(
            array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            'l.id = main_table.listing_id'
        );
        $listingProductCollection->addFieldToFilter('is_variation_parent', 0);
        $listingProductCollection->addFieldToFilter('l.account_id', $account->getId());
        $listingProductCollection->addFieldToFilter('second_table.sku', array('notnull' => true));
        $listingProductCollection->addFieldToFilter('second_table.online_regular_price', array('notnull' => true));

        $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingProductCollection->getSelect()->columns(
            array(
            'id'   => 'main_table.id',
            'sku'  => 'second_table.sku'
            )
        );

        $lastListingProductId = $this->getAccountData($account, self::REGISTRY_GENERAL_LAST_LISTING_PRODUCT_ID);
        $listingProductCollection->getSelect()->where('main_table.id > ?', $lastListingProductId);
        $listingProductCollection->getSelect()->limit(self::PRODUCTS_COUNT_BY_ACCOUNT_AND_PRODUCT_TYPE);
        $listingProductCollection->getSelect()->order('id ASC');

        // Listing Others
        /** @var Ess_M2ePro_Model_Resource_Amazon_Listing_Other_Collection $listingOtherCollection */
        $listingOtherCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');
        $listingOtherCollection->addFieldToFilter('account_id', $account->getId());

        $listingOtherCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingOtherCollection->getSelect()->columns(
            array(
            'id'   => 'main_table.id',
            'sku'  => 'second_table.sku'
            )
        );

        $lastListingOtherId = $this->getAccountData($account, self::REGISTRY_GENERAL_LAST_LISTING_OTHER_ID);
        $listingOtherCollection->getSelect()->where('main_table.id > ?', $lastListingOtherId);
        $listingOtherCollection->getSelect()->limit(self::PRODUCTS_COUNT_BY_ACCOUNT_AND_PRODUCT_TYPE);
        $listingOtherCollection->getSelect()->order('id ASC');

        $listingProducts = $listingProductCollection->getData();
        $listingOthers = $listingOtherCollection->getData();

        if (empty($listingProducts) && empty($listingOthers)) {
            $this->deleteAccountData($account, self::REGISTRY_GENERAL_LAST_LISTING_PRODUCT_ID);
            $this->deleteAccountData($account, self::REGISTRY_GENERAL_LAST_LISTING_OTHER_ID);

            return;
        }

        $skus = array();
        foreach ($listingProducts as $listingProduct) {
            $skus[] = $listingProduct['sku'];
        }

        foreach ($listingOthers as $listingOther) {
            $skus[] = $listingOther['sku'];
        }

        /** @var $repricingSynchronization Ess_M2ePro_Model_Amazon_Repricing_Synchronization_General */
        $repricingSynchronization = Mage::getModel(
            'M2ePro/Amazon_Repricing_Synchronization_General', $account
        );
        $result = $repricingSynchronization->run($skus);
        if ($result) {
            if (!empty($listingProducts)) {
                $this->setAccountData(
                    $account,
                    self::REGISTRY_GENERAL_LAST_LISTING_PRODUCT_ID,
                    $listingProducts[count($listingProducts) - 1]['id']
                );
            }

            if (!empty($listingOthers)) {
                $this->setAccountData(
                    $account,
                    self::REGISTRY_GENERAL_LAST_LISTING_OTHER_ID,
                    $listingOthers[count($listingOthers) - 1]['id']
                );
            }
        }
    }

    /**
     * @param Ess_M2ePro_Model_Account $account
     */
    protected function synchronizeActualPrice($account)
    {
        // Listing Products
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->getSelect()->joinLeft(
            array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            'l.id = main_table.listing_id'
        );
        $listingProductCollection->addFieldToFilter('is_variation_parent', 0);
        $listingProductCollection->addFieldToFilter('is_repricing', 1);
        $listingProductCollection->addFieldToFilter('l.account_id', $account->getId());
        $listingProductCollection->addFieldToFilter('second_table.sku', array('notnull' => true));
        $listingProductCollection->addFieldToFilter('second_table.online_regular_price', array('notnull' => true));

        $listingProductCollection->getSelect()->joinLeft(
            array('alpr' => Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->getMainTable()),
            'alpr.listing_product_id = main_table.id'
        );
        $listingProductCollection->addFieldToFilter('alpr.is_online_disabled', 0);

        $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingProductCollection->getSelect()->columns(
            array(
            'id'   => 'main_table.id',
            'sku'  => 'second_table.sku'
            )
        );

        $lastListingProductId = $this->getAccountData($account, self::REGISTRY_ACTUAL_PRICE_LAST_LISTING_PRODUCT_ID);
        $listingProductCollection->getSelect()->where('main_table.id > ?', $lastListingProductId);
        $listingProductCollection->getSelect()->limit(self::PRODUCTS_COUNT_BY_ACCOUNT_AND_PRODUCT_TYPE);
        $listingProductCollection->getSelect()->order('id ASC');

        // Listing Others
        /** @var Ess_M2ePro_Model_Resource_Amazon_Listing_Other_Collection $listingOtherCollection */
        $listingOtherCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');
        $listingOtherCollection->addFieldToFilter('account_id', $account->getId());
        $listingOtherCollection->addFieldToFilter('is_repricing', 1);
        $listingOtherCollection->addFieldToFilter('is_repricing_disabled', 0);

        $listingOtherCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingOtherCollection->getSelect()->columns(
            array(
            'id'   => 'main_table.id',
            'sku'  => 'second_table.sku'
            )
        );

        $lastListingOtherId = $this->getAccountData($account, self::REGISTRY_ACTUAL_PRICE_LAST_LISTING_OTHER_ID);
        $listingOtherCollection->getSelect()->where('main_table.id > ?', $lastListingOtherId);
        $listingOtherCollection->getSelect()->limit(self::PRODUCTS_COUNT_BY_ACCOUNT_AND_PRODUCT_TYPE);
        $listingOtherCollection->getSelect()->order('id ASC');

        $listingProducts = $listingProductCollection->getData();
        $listingOthers = $listingOtherCollection->getData();

        if (empty($listingProducts) && empty($listingOthers)) {
            $this->deleteAccountData($account, self::REGISTRY_ACTUAL_PRICE_LAST_LISTING_PRODUCT_ID);
            $this->deleteAccountData($account, self::REGISTRY_ACTUAL_PRICE_LAST_LISTING_OTHER_ID);

            return;
        }

        $skus = array();
        foreach ($listingProducts as $listingProduct) {
            $skus[] = $listingProduct['sku'];
        }

        foreach ($listingOthers as $listingOther) {
            $skus[] = $listingOther['sku'];
        }

        /** @var $repricingSynchronization Ess_M2ePro_Model_Amazon_Repricing_Synchronization_ActualPrice */
        $repricingSynchronization = Mage::getModel(
            'M2ePro/Amazon_Repricing_Synchronization_ActualPrice', $account
        );
        $result = $repricingSynchronization->run($skus);
        if ($result) {
            if (!empty($listingProducts)) {
                $this->setAccountData(
                    $account,
                    self::REGISTRY_ACTUAL_PRICE_LAST_LISTING_PRODUCT_ID,
                    $listingProducts[count($listingProducts) - 1]['id']
                );
            }

            if (!empty($listingOthers)) {
                $this->setAccountData(
                    $account,
                    self::REGISTRY_ACTUAL_PRICE_LAST_LISTING_OTHER_ID,
                    $listingOthers[count($listingOthers) - 1]['id']
                );
            }
        }
    }

    //####################################

    /**
     * @return bool
     */
    protected function isPossibleToSynchronizeGeneral($account)
    {
        /** @var Ess_M2ePro_Helper_Data $helper */
        $helper = Mage::helper('M2ePro');
        $currentTimeStamp = $helper->getCurrentGmtDate(true);

        $startDate = $this->getAccountData($account, self::REGISTRY_GENERAL_START_DATE);
        $startDate = !empty($startDate) ?
            (int)$helper->createGmtDateTime($startDate)->format('U') : 0;

        $lastListingProductId = $this->getAccountData($account, self::REGISTRY_GENERAL_LAST_LISTING_PRODUCT_ID);
        $lastListingOtherId = $this->getAccountData($account, self::REGISTRY_GENERAL_LAST_LISTING_OTHER_ID);

        if ($lastListingProductId !== null || $lastListingOtherId !== null) {
            return true;
        }

        if ($currentTimeStamp > $startDate + self::SYNCHRONIZE_GENERAL_INTERVAL) {
            $this->setAccountData(
                $account,
                self::REGISTRY_GENERAL_START_DATE,
                Mage::helper('M2ePro')->getCurrentGmtDate()
            );

            $this->setAccountData($account, self::REGISTRY_GENERAL_LAST_LISTING_PRODUCT_ID, 0);
            $this->setAccountData($account, self::REGISTRY_GENERAL_LAST_LISTING_OTHER_ID, 0);

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function isPossibleToSynchronizeActualPrice($account)
    {
        /** @var Ess_M2ePro_Helper_Data $helper */
        $helper = Mage::helper('M2ePro');
        $currentTimeStamp = $helper->getCurrentGmtDate(true);

        $startDate = $this->getAccountData($account, self::REGISTRY_ACTUAL_PRICE_START_DATE);
        $startDate = !empty($startDate) ?
            (int)$helper->createGmtDateTime($startDate)->format('U') : 0;

        $lastListingProductId = $this->getAccountData($account, self::REGISTRY_ACTUAL_PRICE_LAST_LISTING_PRODUCT_ID);
        $lastListingOtherId = $this->getAccountData($account, self::REGISTRY_ACTUAL_PRICE_LAST_LISTING_OTHER_ID);

        if ($lastListingProductId !== null || $lastListingOtherId !== null) {
            return true;
        }

        if ($currentTimeStamp > $startDate + self::SYNCHRONIZE_ACTUAL_PRICE_INTERVAL) {
            $this->setAccountData(
                $account,
                self::REGISTRY_ACTUAL_PRICE_START_DATE,
                Mage::helper('M2ePro')->getCurrentGmtDate()
            );

            $this->setAccountData($account, self::REGISTRY_ACTUAL_PRICE_LAST_LISTING_PRODUCT_ID, 0);
            $this->setAccountData($account, self::REGISTRY_ACTUAL_PRICE_LAST_LISTING_OTHER_ID, 0);

            return true;
        }

        return false;
    }

    //#####################################

    protected function getAccountData($account, $key)
    {
        return Mage::helper('M2ePro/Module')->getRegistry()->getValue($key . $account->getId() . '/');
    }

    protected function setAccountData($account, $key, $value)
    {
        Mage::helper('M2ePro/Module')->getRegistry()->setValue($key . $account->getId() . '/', $value);
    }

    protected function deleteAccountData($account, $key)
    {
        Mage::helper('M2ePro/Module')->getRegistry()->deleteValue($key . $account->getId() . '/');
    }

    //####################################
}
