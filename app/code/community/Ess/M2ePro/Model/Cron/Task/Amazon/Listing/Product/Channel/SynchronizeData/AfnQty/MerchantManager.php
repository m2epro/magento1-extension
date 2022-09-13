<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_AfnQty_MerchantManager
{
    const REGISTRY_PREFIX = '/amazon/inventory/afn_qty/by_merchant/';
    const REGISTRY_SUFFIX = '/last_update/';

    /** @var bool */
    protected $_init = false;
    /** @var array */
    protected $_merchantAccounts = array();
    /** @var array */
    protected $_accountIdToMerchantId = array();
    /** @var Ess_M2ePro_Model_Registry_Manager */
    protected $_registryManager;
    /** @var Ess_M2ePro_Helper_Data */
    protected $_dataHelper;

    public function __construct()
    {
        $this->_registryManager = Mage::getModel('M2ePro/Registry_Manager');
        $this->_dataHelper = Mage::helper('M2ePro');
    }

    /**
     * @return void
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function init()
    {
        if ($this->_init) {
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Account_Collection $accountsCollection */
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')
            ->getCollection('Account');

        /** @var Ess_M2ePro_Model_Account $item */
        foreach ($accountsCollection->getItems() as $item) {
            $merchantId = $item->getChildObject()->getData('merchant_id');
            $this->_merchantAccounts[$merchantId][] = $item;

            $this->_accountIdToMerchantId[(int)$item->getId()] = $merchantId;
        }

        $this->_init = true;
    }

    /**
     * @return array
     */
    public function getMerchantsIds()
    {
        return array_keys($this->_merchantAccounts);
    }

    /**
     * @param string $merchantId
     *
     * @return array
     */
    public function getMerchantAccountsIds($merchantId)
    {
        if (empty($this->_merchantAccounts[$merchantId])) {
            return array();
        }

        $accountsIds = array();
        /** @var Ess_M2ePro_Model_Account $account */
        foreach ($this->_merchantAccounts[$merchantId] as $account) {
            $accountsIds[] = $account->getId();
        }

        return $accountsIds;
    }

    /**
     * @param string $merchantId
     * @return Ess_M2ePro_Model_Account
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getMerchantAccount($merchantId)
    {
        if (empty($this->_merchantAccounts[$merchantId][0])) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                'Incorrect MerchantManager usage: you need to do init() first!'
            );
        }

        return $this->_merchantAccounts[$merchantId][0];
    }

    /**
     * @param int $accountId
     *
     * @return string|null
     */
    public function getMerchantIdByAccountId($accountId)
    {
        return !empty($this->_accountIdToMerchantId[$accountId]) ?
            $this->_accountIdToMerchantId[$accountId] : null;
    }

    /**
     * @param string $merchantId
     * @param int $interval
     *
     * @return bool
     * @throws Exception
     */
    public function isIntervalExceeded($merchantId, $interval)
    {
        $lastUpdate = $this->_registryManager->getValue(self::REGISTRY_PREFIX . $merchantId . self::REGISTRY_SUFFIX);
        if (!$lastUpdate) {
            return true;
        }

        $now = $this->_dataHelper->createCurrentGmtDateTime();
        $lastUpdateDate = $this->_dataHelper->createGmtDateTime($lastUpdate);

        return $now->getTimestamp() - $lastUpdateDate->getTimestamp() > $interval;
    }

    /**
     * @param string $merchantId
     * @param string|null $value
     *
     * @return void
     * @throws Exception
     */
    private function setMerchantLastUpdate($merchantId, $value)
    {
        $this->_registryManager->setValue(
            self::REGISTRY_PREFIX . $merchantId . self::REGISTRY_SUFFIX,
            $value
        );
    }

    /**
     * @param string $merchantId
     *
     * @return void
     * @throws Exception
     */
    public function setMerchantLastUpdateNow($merchantId)
    {
        $now = $this->_dataHelper->createCurrentGmtDateTime();
        $this->setMerchantLastUpdate(
            $merchantId,
            $now->format('Y-m-d H:i:s')
        );
    }

    /**
     * @param string $merchantId
     *
     * @return void
     * @throws \Exception
     */
    public function resetMerchantLastUpdate($merchantId)
    {
        $this->setMerchantLastUpdate($merchantId, null);
    }
}
