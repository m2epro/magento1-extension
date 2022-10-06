<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_AfnQty_MerchantManager as MerchantManager;

class Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_AfnQty_Responser
    extends Ess_M2ePro_Model_Amazon_Connector_Inventory_Get_AfnQty_ItemsResponser
{
    const ERROR_CODE_UNACCEPTABLE_REPORT_STATUS = 504;

    /** @var Ess_M2ePro_Model_Synchronization_Log */
    protected $_synchronizationLog;
    /** @var MerchantManager */
    protected $_merchantManager;
    /** @var Ess_M2ePro_Helper_Module_Logger */
    protected $_logger;

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function __construct(array $params = array(), Ess_M2ePro_Model_Connector_Connection_Response $response)
    {
        parent::__construct($params, $response);

        $this->_merchantManager = Mage::getModel(
            'M2ePro/Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_AfnQty_MerchantManager'
        );
        $this->_merchantManager->init();
        $this->_logger = Mage::helper('M2ePro/Module_Logger');
    }

    /**
     * @throws Exception
     */
    protected function processResponseMessages()
    {
        parent::processResponseMessages();

        $isMessageReceived = false;
        foreach ($this->getResponse()->getMessages()->getEntities() as $message) {
            if (!$message->isError() && !$message->isWarning()) {
                continue;
            }

            $isMessageReceived = true;
            if ($message->getCode() == self::ERROR_CODE_UNACCEPTABLE_REPORT_STATUS) {
                $this->_logger->process(
                    Mage::helper('M2ePro')->__($message->getText()),
                    'Incorrect Amazon report'
                );

                continue;
            }

            $logType = $message->isError() ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($message->getText()),
                $logType
            );
        }

        if ($isMessageReceived) {
            $this->refreshLastUpdate(false);
        }
    }

    protected function isNeedProcessResponse()
    {
        if (!parent::isNeedProcessResponse()) {
            return false;
        }

        if ($this->getResponse()->getMessages()->hasErrorEntities()) {
            return false;
        }

        return true;
    }

    public function failDetected($messageText)
    {
        parent::failDetected($messageText);

        $this->getSynchronizationLog()->addMessage(
            Mage::helper('M2ePro')->__($messageText),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
        );
    }

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     * @throws Exception
     */
    protected function processResponseData()
    {
        $responseData = $this->getPreparedResponseData();
        $receivedItems = $responseData['data'];

        if (empty($receivedItems)) {
            $this->refreshLastUpdate(true);
            return;
        }

        $merchantId = $this->_merchantManager->getMerchantIdByAccountId((int)$this->_params['account_id']);
        // $this->_params['account_id'] is always available
        // next lines is for possible situation with deleted account
        if (!$merchantId) {
            $this->refreshLastUpdate(true);
            return;
        }

        $keys = array_map(
            function ($value) {
                return (string)$value;
            },
            array_keys($receivedItems)
        );

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $m2eproListingProductCollection */
        $m2eproListingProductCollection = Mage::helper('M2ePro/Component_Amazon')
            ->getCollection('Listing_Product');
        $m2eproListingProductCollection
            ->addFieldToFilter('sku', array('in' => $keys))
            ->getSelect()
            ->joinInner(
                array(
                    'l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable(),
                ),
                'l.id=main_table.listing_id',
                array()
            )
            ->joinInner(
                array(
                    'aa' => Mage::getResourceModel('M2ePro/Amazon_Account')->getMainTable(),
                ),
                'aa.account_id=l.account_id',
                array()
            )
            ->where('aa.merchant_id = ? AND is_afn_channel = 1', $merchantId);

        /** @var Ess_M2ePro_Model_Resource_Listing_Other_Collection $unmanagedListingProductCollection */
        $unmanagedListingProductCollection = Mage::helper('M2ePro/Component_Amazon')
            ->getCollection('Listing_Other');
        $unmanagedListingProductCollection
            ->addFieldToFilter('sku', array('in' => $keys))
            ->getSelect()
            ->joinInner(
                array(
                    'aa' => Mage::getResourceModel('M2ePro/Amazon_Account')->getMainTable(),
                ),
                'aa.account_id=main_table.account_id',
                array()
            )
            ->where('aa.merchant_id = ? AND is_afn_channel = 1', $merchantId);

        /** @var Ess_M2ePro_Model_Listing_Product $item */
        foreach ($m2eproListingProductCollection->getItems() as $item) {
            $this->updateItem(
                $item,
                $receivedItems[$item->getChildObject()->getSku()]
            );
        }

        /** @var Ess_M2ePro_Model_Listing_Other $item */
        foreach ($unmanagedListingProductCollection->getItems() as $item) {
            $this->updateItem(
                $item,
                $receivedItems[$item->getChildObject()->getSku()]
            );
        }

        $this->refreshLastUpdate(true);
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product|Ess_M2ePro_Model_Listing_Other $item
     * @param int|string $afnQty
     *
     * @return void
     */
    private function updateItem($item, $afnQty)
    {
        $item->setData('online_afn_qty', $afnQty);
        $item->setData(
            'status',
            $afnQty ?
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED : Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED
        );
        $item->save();
    }

    /**
     * @param bool $success
     *
     * @return void
     * @throws Exception
     */
    private function refreshLastUpdate($success)
    {
        $merchantId = $this->_merchantManager->getMerchantIdByAccountId((int)$this->_params['account_id']);
        // $this->_params['account_id'] is always available
        // next lines is for possible situation with deleted account
        if (!$merchantId) {
            return;
        }

        if ($success) {
            $this->_merchantManager->setMerchantLastUpdateNow($merchantId);
        } else {
            $this->_merchantManager->resetMerchantLastUpdate($merchantId);
        }
    }

    protected function getSynchronizationLog()
    {
        if ($this->_synchronizationLog !== null) {
            return $this->_synchronizationLog;
        }

        $this->_synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->_synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $this->_synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_LISTINGS);

        return $this->_synchronizationLog;
    }
}
