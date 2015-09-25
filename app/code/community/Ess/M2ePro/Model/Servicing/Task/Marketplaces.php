<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Servicing_Task_Marketplaces extends Ess_M2ePro_Model_Servicing_Task
{
    // ########################################

    public function getPublicNick()
    {
        return 'marketplaces';
    }

    // ########################################

    public function getRequestData()
    {
        return array();
    }

    public function processResponseData(array $data)
    {
        if (isset($data['ebay_last_update_dates']) && is_array($data['ebay_last_update_dates'])) {
            $this->processEbayLastUpdateDates($data['ebay_last_update_dates']);
        }

        if (isset($data['amazon_last_update_dates']) && is_array($data['amazon_last_update_dates'])) {
            $this->processAmazonLastUpdateDates($data['amazon_last_update_dates']);
        }
    }

    // ########################################

    protected function processEbayLastUpdateDates($lastUpdateDates)
    {
        $enabledMarketplaces = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Marketplace')
            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);

        $writeConn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $dictionaryTable = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_marketplace');

        /* @var $marketplace Ess_M2ePro_Model_Marketplace */
        foreach ($enabledMarketplaces as $marketplace) {

            if (!isset($lastUpdateDates[$marketplace->getNativeId()])) {
                continue;
            }

            $serverLastUpdateDate = $lastUpdateDates[$marketplace->getNativeId()];

            $expr = "IF(client_details_last_update_date is NULL, '{$serverLastUpdateDate}',
                                                                 client_details_last_update_date)";

            $writeConn->update(
                $dictionaryTable,
                array(
                    'server_details_last_update_date' => $serverLastUpdateDate,
                    'client_details_last_update_date' => new Zend_Db_Expr($expr)
                ),
                array('marketplace_id = ?' => $marketplace->getId())
            );
        }
    }

    protected function processAmazonLastUpdateDates($lastUpdateDates)
    {
        $enabledMarketplaces = Mage::helper('M2ePro/Component_Amazon')
            ->getMarketplacesAvailableForApiCreation();

        $writeConn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $dictionaryTable = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_marketplace');

        /* @var $marketplace Ess_M2ePro_Model_Marketplace */
        foreach ($enabledMarketplaces as $marketplace) {

            if (!isset($lastUpdateDates[$marketplace->getNativeId()])) {
                continue;
            }

            $serverLastUpdateDate = $lastUpdateDates[$marketplace->getNativeId()];

            $expr = "IF(client_details_last_update_date is NULL, '{$serverLastUpdateDate}',
                                                                 client_details_last_update_date)";

            $writeConn->update(
                $dictionaryTable,
                array(
                    'server_details_last_update_date' => $serverLastUpdateDate,
                    'client_details_last_update_date' => new Zend_Db_Expr($expr)
                ),
                array('marketplace_id = ?' => $marketplace->getId())
            );
        }
    }

    // ########################################
}