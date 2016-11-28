<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Cron_Task_RepricingInspectProducts extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'repricing_inspect_products';
    const MAX_MEMORY_LIMIT = 512;

    //####################################

    protected function getNick()
    {
        return self::NICK;
    }

    protected function getMaxMemoryLimit()
    {
        return self::MAX_MEMORY_LIMIT;
    }

    //####################################

    public function performActions()
    {
        $permittedAccounts = $this->getPermittedAccounts();

        foreach ($permittedAccounts as $permittedAccount) {

            $operationDate = Mage::helper('M2ePro')->getCurrentGmtDate();
            $skus = $this->getNewNoneSyncSkus($permittedAccount);

            /** @var $repricingSynchronization Ess_M2ePro_Model_Amazon_Repricing_Synchronization_General   */
            $repricingSynchronization = Mage::getModel('M2ePro/Amazon_Repricing_Synchronization_General',
                $permittedAccount
            );
            $repricingSynchronization->run($skus);

            $this->setLastUpdateDate($permittedAccount, $operationDate);
        }
    }

    //####################################

    /**
     * @return Ess_M2ePro_Model_Account[]
     */
    private function getPermittedAccounts()
    {
        /** @var Ess_M2ePro_Model_Mysql4_Account_Collection $accountCollection */
        $accountCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');

        $accountCollection->getSelect()->joinInner(
            array('aar' => Mage::getResourceModel('M2ePro/Amazon_Account_Repricing')->getMainTable()),
            'aar.account_id=main_table.id', array()
        );

        return $accountCollection->getItems();
    }

    /**
     * @param $account Ess_M2ePro_Model_Account
     * @return array
     */
    private function getNewNoneSyncSkus(Ess_M2ePro_Model_Account $account)
    {
        $accountId = $account->getId();

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->getSelect()->join(
            array('l' => Mage::getSingleton('core/resource')->getTableName('M2ePro/Listing')),
            'l.id=main_table.listing_id', array()
        );
        $listingProductCollection->addFieldToFilter('l.account_id', $accountId);
        $listingProductCollection->addFieldToFilter('main_table.status', array('in' => array(
            Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
            Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED,
            Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN)));
        $listingProductCollection->addFieldToFilter('main_table.update_date',
            array('gt' => $this->getLastUpdateDate($account)));

        $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingProductCollection->getSelect()->columns('second_table.sku');

        return $listingProductCollection->getColumnValues('sku');
    }

    /**
     * @param $account Ess_M2ePro_Model_Account
     * @return string
     */
    private function getLastUpdateDate(Ess_M2ePro_Model_Account $account)
    {
        $accountId = $account->getId();

        $lastCheckedUpdateTime = Mage::getModel('M2ePro/Amazon_Account_Repricing')->load($accountId)
            ->getLastCheckedListingProductDate();

        if (is_null($lastCheckedUpdateTime)) {
            $lastCheckedUpdateTime = new DateTime(Mage::helper('M2ePro')->getCurrentGmtDate(), new DateTimeZone('UTC'));
            $lastCheckedUpdateTime->modify('-1 hour');
            $lastCheckedUpdateTime = $lastCheckedUpdateTime->format('Y-m-d H:i:s');
        }

         return $lastCheckedUpdateTime;
    }

    /**
     * @param $account Ess_M2ePro_Model_Account
     * @param $syncDate Datetime|String
     */
    private function setLastUpdateDate(Ess_M2ePro_Model_Account $account , $syncDate)
    {
        $accountId = $account->getId();

        /** @var $accountRepricingModel Ess_M2ePro_Model_Amazon_Account_Repricing */
        $accountRepricingModel = Mage::getModel('M2ePro/Amazon_Account_Repricing')->load($accountId);
        $accountRepricingModel->setData(
            'last_checked_listing_product_update_date',
            $syncDate
        );

        $accountRepricingModel->save();
    }

    //####################################
}