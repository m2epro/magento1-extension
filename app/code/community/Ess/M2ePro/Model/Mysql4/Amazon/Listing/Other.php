<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Amazon_Listing_Other
    extends Ess_M2ePro_Model_Mysql4_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Listing_Other', 'listing_other_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################

    public function getAllRepricingSkus(Ess_M2ePro_Model_Account $account, $repricingDisabled = null)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Amazon_Listing_Other_Collection $listingOtherCollection */
        $listingOtherCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');
        $listingOtherCollection->addFieldToFilter('is_repricing', 1);
        $listingOtherCollection->addFieldToFilter('account_id', $account->getId());

        if (!is_null($repricingDisabled)) {
            $listingOtherCollection->addFieldToFilter('is_repricing_disabled', $repricingDisabled);
        }

        $listingOtherCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingOtherCollection->getSelect()->columns(
            array('sku'  => 'second_table.sku')
        );

        return $listingOtherCollection->getColumnValues('sku');
    }

    public function getProductsDataBySkus(array $skus = array(),
                                          array $filters = array(),
                                          array $columns = array())
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Other_Collection $listingOtherCollection */
        $listingOtherCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');

        if (!empty($skus)) {
            $skus = array_map(function($el){ return (string)$el; }, $skus);
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
}