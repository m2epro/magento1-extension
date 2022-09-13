<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_AfnQty_ProcessingRunner as Runner;
use Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_AfnQty_MerchantManager as MerchantManager;

class Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_AfnQty
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'amazon/listing/product/channel/synchronize_data/afn_qty';
    const MERCHANT_INTERVAL = 14400; // 4 hours

    /** @var int (in seconds) */
    protected $_interval = 600;
    /** @var MerchantManager */
    protected $_merchantManager;

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function __construct()
    {
        $this->_merchantManager = Mage::getModel(
            'M2ePro/Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_AfnQty_MerchantManager'
        );
        $this->_merchantManager->init();
    }

    public function isPossibleToRun()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_LISTINGS);

        return $synchronizationLog;
    }

    /**
     * @throws Exception
     */
    protected function performActions()
    {
        $merchantsIds = $this->_merchantManager->getMerchantsIds();
        if (empty($merchantsIds)) {
            return;
        }

        foreach ($merchantsIds as $merchantId) {
            $this->getOperationHistory()->addText("Starting Merchant \"$merchantId\"");
            if ($this->isLockedMerchant($merchantId)
                || !$this->_merchantManager->isIntervalExceeded($merchantId, self::MERCHANT_INTERVAL)
            ) {
                continue;
            }

            $this->getOperationHistory()->addTimePoint(
                __METHOD__ . 'process' . $merchantId,
                "Process Merchant $merchantId"
            );

            try {
                $this->processMerchant($merchantId);
            } catch (Exception $exception) {
                $message = 'The "Get AFN Qty" Action for Amazon Merchant "%merchant%" was completed with error.';
                $message = Mage::helper('M2ePro')->__($message, $merchantId);

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }

            $this->getOperationHistory()->saveTimePoint(__METHOD__ . 'process' . $merchantId);
        }
    }

    /**
     * @param $merchantId
     * @return void
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function processMerchant($merchantId)
    {
        $merchantAccountsIds = $this->_merchantManager->getMerchantAccountsIds($merchantId);
        if ($this->isM2eProListingsHaveAfnProducts($merchantAccountsIds)
            || $this->isUnmanagedListingsHaveAfnProducts($merchantAccountsIds)
        ) {
            $someAccount = $this->_merchantManager->getMerchantAccount($merchantId);
            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getCustomConnector(
                'Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_AfnQty_Requester',
                array('merchant_id' => $merchantId),
                $someAccount
            );
            $dispatcherObject->process($connectorObj);
        }
    }

    /**
     * @param array $merchantAccountsIds
     *
     * @return Ess_M2ePro_Model_Resource_Amazon_Account_Collection
     */
    private function getBaseCollectionForAfnProductsCheck($merchantAccountsIds)
    {
        /** @var Ess_M2ePro_Model_Resource_Amazon_Account_Collection $collection */
        $collection = Mage::getModel('M2ePro/Amazon_Account')->getCollection();
        $collection->addFieldToFilter('main_table.account_id', array('in' => $merchantAccountsIds));
        $collection->addFieldToFilter('is_afn_channel', 1);

        return $collection;
    }

    /**
     * @param array $merchantAccountsIds
     *
     * @return bool
     */
    private function isM2eProListingsHaveAfnProducts($merchantAccountsIds)
    {
        $collection = $this->getBaseCollectionForAfnProductsCheck($merchantAccountsIds);
        $collection->getSelect()
            ->joinInner(
                array(
                    'l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable(),
                ),
                'l.account_id=main_table.account_id',
                array()
            )
            ->joinInner(
                array(
                    'lp' => Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable(),
                ),
                'lp.listing_id=l.id',
                array()
            )
            ->joinInner(
                array(
                    'alp' => Mage::getResourceModel('M2ePro/Amazon_Listing_Product')->getMainTable(),
                ),
                'alp.listing_product_id=lp.id',
                array()
            );

        return (bool)$collection->getSize();
    }

    /**
     * @param array $merchantAccountsIds
     *
     * @return bool
     */
    private function isUnmanagedListingsHaveAfnProducts(array $merchantAccountsIds)
    {
        $collection = $this->getBaseCollectionForAfnProductsCheck($merchantAccountsIds);
        $collection->getSelect()
            ->joinInner(
                array(
                    'lo' => Mage::getResourceModel('M2ePro/Listing_Other')->getMainTable(),
                ),
                'lo.account_id=main_table.account_id',
                array()
            )
            ->joinInner(
                array(
                    'alo' => Mage::getResourceModel('M2ePro/Amazon_Listing_Other')->getMainTable(),
                ),
                'alo.listing_other_id=lo.id',
                array()
            );

        return (bool)$collection->getSize();
    }

    protected function isLockedMerchant($merchantId)
    {
        $lockItemNick = Runner::LOCK_ITEM_PREFIX . '_' . $merchantId;

        /** @var $lockItemManager Ess_M2ePro_Model_Lock_Item_Manager */
        $lockItemManager = Mage::getModel('M2ePro/Lock_Item_Manager', array('nick' => $lockItemNick));
        if (!$lockItemManager->isExist()) {
            return false;
        }

        if ($lockItemManager->isInactiveMoreThanSeconds(Ess_M2ePro_Model_Processing_Runner::MAX_LIFETIME)) {
            $lockItemManager->remove();
            return false;
        }

        return true;
    }
}
