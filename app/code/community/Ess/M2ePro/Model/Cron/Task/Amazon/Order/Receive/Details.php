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

    /**
     * @var int (in seconds)
     */
    protected $_interval = 7200;

    //####################################

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

    protected function performActions()
    {
        $permittedAccounts = $this->getPermittedAccounts();
        if (empty($permittedAccounts)) {
            return;
        }

        foreach ($permittedAccounts as $account) {

            /** @var Ess_M2ePro_Model_Account $account */

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

    protected function getPermittedAccounts()
    {
        /** @var $accountsCollection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        return $accountsCollection->getItems();
    }

    // ---------------------------------------

    protected function processAccount(Ess_M2ePro_Model_Account $account)
    {
        $from = new \DateTime('now', new \DateTimeZone('UTC'));;
        $from->modify('- 5 days');

        /** @var Ess_M2ePro_Model_Resource_Order_Collection $orderCollection */
        $orderCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Order');
        $orderCollection->addFieldToFilter('account_id', $account->getId());
        $orderCollection->addFieldToFilter('is_afn_channel', 1);
        $orderCollection->addFieldToFilter('status', array('neq' => Ess_M2ePro_Model_Amazon_Order::STATUS_PENDING));
        $orderCollection->addFieldToFilter('create_date', array('gt' => $from->format('Y-m-d H:i:s')));
        $orderCollection->addFieldToFilter(
            array('additional_data', 'additional_data'),
            array(
                array('additional_data', 'null' => true),
                array('additional_data', 'nlike' => '%fulfillment_details%')
            )
        );

        $amazonOrdersIds = $orderCollection->getColumnValues('amazon_order_id');
        if (empty($amazonOrdersIds)) {
            return;
        }

        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getCustomConnector(
            'Cron_Task_Amazon_Order_Receive_Details_Requester', array('items' => $amazonOrdersIds), $account
        );
        $dispatcherObject->process($connectorObj);
    }

    //########################################
}