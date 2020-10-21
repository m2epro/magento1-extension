<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Amazon_Listing_Other
    extends Ess_M2ePro_Model_Resource_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Listing_Other', 'listing_other_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################

    public function getRepricingSkus(Ess_M2ePro_Model_Account $account, $filterSkus = null, $repricingDisabled = null)
    {
        /** @var Ess_M2ePro_Model_Resource_Amazon_Listing_Other_Collection $listingOtherCollection */
        $listingOtherCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');
        $listingOtherCollection->addFieldToFilter('is_repricing', 1);
        $listingOtherCollection->addFieldToFilter('account_id', $account->getId());

        if (!empty($filterSkus)) {
            $listingOtherCollection->addFieldToFilter('sku', array('in' => $filterSkus));
        }

        if ($repricingDisabled !== null) {
            $listingOtherCollection->addFieldToFilter('is_repricing_disabled', (int)$repricingDisabled);
        }

        $listingOtherCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingOtherCollection->getSelect()->columns(
            array('sku'  => 'second_table.sku')
        );

        return $listingOtherCollection->getColumnValues('sku');
    }

    public function getProductsDataBySkus(
        array $skus = array(),
        array $filters = array(),
        array $columns = array()
    ) {
        /** @var Ess_M2ePro_Model_Resource_Listing_Other_Collection $listingOtherCollection */
        $listingOtherCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');

        if (!empty($skus)) {
            $skus = array_map(
                function($el){
                return (string)$el; 
                }, $skus
            );
            $listingOtherCollection->addFieldToFilter('sku', array('in' => array_unique($skus)));
        }

        if (!empty($filters)) {
            foreach ($filters as $columnName => $columnValue) {
                $listingOtherCollection->addFieldToFilter($columnName, $columnValue);
            }
        }

        if (!empty($columns)) {
            $listingOtherCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $listingOtherCollection->getSelect()->columns($columns);
        }

        return $listingOtherCollection->getData();
    }

    //########################################

    public function resetEntities()
    {
        $listingOther = Mage::getModel('M2ePro/Listing_Other');
        $amazonListingOther = Mage::getModel('M2ePro/Amazon_Listing_Other');

        $stmt = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other')->getSelect()->query();

        $SKUs = array();
        foreach ($stmt as $row) {
            $listingOther->setData($row);
            $amazonListingOther->setData($row);

            $listingOther->setChildObject($amazonListingOther);
            $amazonListingOther->setParentObject($listingOther);
            $SKUs[] = $amazonListingOther->getSku();

            $listingOther->deleteInstance();
        }

        $tableName = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_amazon_item');
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        foreach (array_chunk($SKUs, 1000) as $chunkSKUs) {
            $writeConnection->delete($tableName, array('sku IN (?)' => $chunkSKUs));
        }

        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        $accountsCollection->addFieldToFilter('other_listings_synchronization', 1);

        foreach ($accountsCollection->getItems() as $account) {
            $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($account->getAdditionalData());
            unset($additionalData['is_amazon_other_listings_full_items_data_already_received']);

            $account->setSettings('additional_data', $additionalData)
                    ->addData('inventory_last_synchronization', null)
                    ->save();
        }
    }

    //########################################
}
