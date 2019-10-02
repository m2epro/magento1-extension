<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Servicing_Task_Marketplaces extends Ess_M2ePro_Model_Servicing_Task
{
    protected $_needToCleanCache = false;

    //########################################

    /**
     * @return string
     */
    public function getPublicNick()
    {
        return 'marketplaces';
    }

    //########################################

    /**
     * @return array
     */
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

        if ($this->_needToCleanCache) {
            Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');
        }
    }

    //########################################

    protected function processEbayLastUpdateDates($lastUpdateDates)
    {
        $enabledMarketplaces = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Marketplace')
            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);

        $writeConn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $dictionaryTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_dictionary_marketplace');

        /** @var $marketplace Ess_M2ePro_Model_Marketplace */
        foreach ($enabledMarketplaces as $marketplace) {
            if (!isset($lastUpdateDates[$marketplace->getNativeId()])) {
                continue;
            }

            $serverLastUpdateDate = $lastUpdateDates[$marketplace->getNativeId()];

            $select = $writeConn->select()
                ->from(
                    $dictionaryTable, array(
                    'client_details_last_update_date'
                    )
                )
                ->where('marketplace_id = ?', $marketplace->getId());

            $clientLastUpdateDate = $writeConn->fetchOne($select);

            if ($clientLastUpdateDate === null) {
                $clientLastUpdateDate = $serverLastUpdateDate;
            }

            if ($clientLastUpdateDate < $serverLastUpdateDate) {
                $this->_needToCleanCache = true;
            }

            $writeConn->update(
                $dictionaryTable,
                array(
                    'server_details_last_update_date' => $serverLastUpdateDate,
                    'client_details_last_update_date' => $clientLastUpdateDate
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
        $dictionaryTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_dictionary_marketplace');

        /** @var $marketplace Ess_M2ePro_Model_Marketplace */
        foreach ($enabledMarketplaces as $marketplace) {
            if (!isset($lastUpdateDates[$marketplace->getNativeId()])) {
                continue;
            }

            $serverLastUpdateDate = $lastUpdateDates[$marketplace->getNativeId()];

            $select = $writeConn->select()
                ->from(
                    $dictionaryTable, array(
                    'client_details_last_update_date'
                    )
                )
                ->where('marketplace_id = ?', $marketplace->getId());

            $clientLastUpdateDate = $writeConn->fetchOne($select);

            if ($clientLastUpdateDate === null) {
                $clientLastUpdateDate = $serverLastUpdateDate;
            }

            if ($clientLastUpdateDate < $serverLastUpdateDate) {
                $this->_needToCleanCache = true;
            }

            $writeConn->update(
                $dictionaryTable,
                array(
                    'server_details_last_update_date' => $serverLastUpdateDate,
                    'client_details_last_update_date' => $clientLastUpdateDate
                ),
                array('marketplace_id = ?' => $marketplace->getId())
            );
        }
    }

    //########################################
}
