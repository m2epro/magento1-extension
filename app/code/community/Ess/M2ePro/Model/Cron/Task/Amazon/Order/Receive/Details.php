<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Order_Receive_Details
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'amazon/order/receive/details';

    /** @var int $_interval (in seconds) */
    protected $_interval = 7200;

    //####################################

    /**
     * {@inheritDoc}
     */
    public function isPossibleToRun()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_ORDERS);

        return $synchronizationLog;
    }

    //########################################

    /**
     * {@inheritDoc}
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function performActions()
    {
        $permittedAccounts = $this->getPermittedAccounts();
        if (empty($permittedAccounts)) {
            return;
        }

        foreach ($permittedAccounts as $account) {

            // ---------------------------------------
            $this->getOperationHistory()->addText('Starting account "'.$account->getTitle().'"');
            // ---------------------------------------

            // ---------------------------------------
            $this->getOperationHistory()->addTimePoint(
                __METHOD__.'process'.$account->getId(),
                'Process account '.$account->getTitle()
            );
            // ---------------------------------------

            try {
                $this->processAccount($account);
            } catch (Exception $exception) {
                $message = Mage::helper('M2ePro')->__(
                    'The "Receive Details" Action for Amazon Account "%account%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }

            // ---------------------------------------
            $this->getOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
            // ---------------------------------------

            // ---------------------------------------
            $this->getLockItemManager()->activate();
            // ---------------------------------------
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account[]
     */
    protected function getPermittedAccounts()
    {
        /** @var Mage_Core_Model_Resource_Db_Collection_Abstract $accountsCollection */
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        return $accountsCollection->getItems();
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Account $account
     *
     * @throws Exception
     */
    protected function processAccount(Ess_M2ePro_Model_Account $account)
    {
        $from = new \DateTime('now', new \DateTimeZone('UTC'));
        $from->modify('-5 days');

        /** @var Ess_M2ePro_Model_Resource_Order_Collection $orderCollection */
        $orderCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Order');
        $orderCollection->getSelect()->joinLeft(
            array('oi' => Mage::getResourceModel('M2ePro/Order_Item')->getMainTable()),
            'main_table.id = oi.order_id'
        );
        $orderCollection->getSelect()->joinLeft(
            array('aoi' => Mage::getResourceModel('M2ePro/Amazon_Order_Item')->getMainTable()),
            'oi.id = aoi.order_item_id'
        );
        $orderCollection->addFieldToFilter('aoi.fulfillment_center_id', array('null' => true));
        $orderCollection->addFieldToFilter('account_id', $account->getId());
        $orderCollection->addFieldToFilter('is_afn_channel', 1);
        $orderCollection->addFieldToFilter('status', array('neq' => Ess_M2ePro_Model_Amazon_Order::STATUS_PENDING));
        $orderCollection->addFieldToFilter('main_table.create_date', array('gt' => $from->format('Y-m-d H:i:s')));
        $orderCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $orderCollection->getSelect()->columns('second_table.amazon_order_id');

        $amazonOrdersIds = $orderCollection->getColumnValues('amazon_order_id');
        if (empty($amazonOrdersIds)) {
            return;
        }

        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getCustomConnector(
            'Cron_Task_Amazon_Order_Receive_Details_Requester',
            array('items' => $amazonOrdersIds),
            $account
        );
        $dispatcherObject->process($connectorObj);
    }

    //########################################
}