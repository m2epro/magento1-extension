<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Repricing_InspectProducts extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'amazon/repricing/inspect_products';

    //####################################

    public function performActions()
    {
        $permittedAccounts = Mage::getResourceModel('M2ePro/Account_Collection')
            ->getAccountsWithValidRepricingAccount();

        foreach ($permittedAccounts as $permittedAccount) {
            $operationDate = Mage::helper('M2ePro')->getCurrentGmtDate();
            $skus = $this->getNewNoneSyncSkus($permittedAccount);

            if (empty($skus)) {
                continue;
            }

            /** @var $repricingSynchronization Ess_M2ePro_Model_Amazon_Repricing_Synchronization_General   */
            $repricingSynchronization = Mage::getModel(
                'M2ePro/Amazon_Repricing_Synchronization_General',
                $permittedAccount
            );
            $repricingSynchronization->run($skus);

            $this->setLastUpdateDate($permittedAccount, $operationDate);
        }
    }

    //####################################

    /**
     * @param $account Ess_M2ePro_Model_Account
     * @return array
     */
    protected function getNewNoneSyncSkus(Ess_M2ePro_Model_Account $account)
    {
        $accountId = $account->getId();

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->getSelect()->join(
            array('l' => Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('M2ePro/Listing')),
            'l.id=main_table.listing_id', array()
        );
        $listingProductCollection->addFieldToFilter('l.account_id', $accountId);
        $listingProductCollection->addFieldToFilter(
            'main_table.status', array('in' => array(
            Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
            Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE,
            Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN))
        );
        $listingProductCollection->addFieldToFilter(
            'main_table.update_date',
            array('gt' => $this->getLastUpdateDate($account))
        );

        $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingProductCollection->getSelect()->columns('second_table.sku');

        return $listingProductCollection->getColumnValues('sku');
    }

    /**
     * @param $account Ess_M2ePro_Model_Account
     * @return string
     */
    protected function getLastUpdateDate(Ess_M2ePro_Model_Account $account)
    {
        $accountId = $account->getId();

        $lastCheckedUpdateTime = Mage::getModel('M2ePro/Amazon_Account_Repricing')->load($accountId)
            ->getLastCheckedListingProductDate();

        if ($lastCheckedUpdateTime === null) {
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
    protected function setLastUpdateDate(Ess_M2ePro_Model_Account $account , $syncDate)
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
