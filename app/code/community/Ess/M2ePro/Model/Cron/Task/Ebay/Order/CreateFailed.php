<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_Order_CreateFailed extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'ebay/order/create_failed';

    const MAX_TRIES_TO_CREATE_ORDER = 3;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
        $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_ORDERS);

        return $synchronizationLog;
    }

    //########################################

    protected function performActions()
    {
        /** @var $accountsCollection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');

        foreach ($accountsCollection->getItems() as $account) {
            /** @var $account Ess_M2ePro_Model_Account **/

            try {
                $ebayOrders = $this->getEbayOrders($account);
                $this->createMagentoOrders($ebayOrders);
            } catch (\Exception $exception) {
                $message = Mage::helper('M2ePro')->__(
                    'The "Create Failed Orders" Action for eBay Account "%account%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }
        }
    }

    //########################################

    protected function createMagentoOrders($ebayOrders)
    {
        /** @var Ess_M2ePro_Model_Cron_Task_Ebay_Order_Creator $ordersCreator */
        $ordersCreator = Mage::getModel('M2ePro/Cron_Task_Ebay_Order_Creator');
        $ordersCreator->setSynchronizationLog($this->getSynchronizationLog());

        foreach ($ebayOrders as $order) {
            /** @var $order Ess_M2ePro_Model_Order */

            if ($ordersCreator->isOrderChangedInParallelProcess($order)) {
                continue;
            }

            if (!$order->canCreateMagentoOrder()) {
                $order->addData(
                    array(
                        'magento_order_creation_failure' => Ess_M2ePro_Model_Order::MAGENTO_ORDER_CREATION_FAILED_NO,
                        'magento_order_creation_fails_count' => 0,
                        'magento_order_creation_latest_attempt_date' => null
                    )
                );
                $order->save();
                continue;
            }

            $ordersCreator->createMagentoOrder($order);
        }
    }

    protected function getEbayOrders(Ess_M2ePro_Model_Account $account)
    {
        $backToDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $backToDate->modify('-15 minutes');

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order');
        $collection->addFieldToFilter('account_id', $account->getId());
        $collection->addFieldToFilter('magento_order_id', array('null' => true));
        $collection->addFieldToFilter(
            'magento_order_creation_failure', Ess_M2ePro_Model_Order::MAGENTO_ORDER_CREATION_FAILED_YES
        );
        $collection->addFieldToFilter(
            'magento_order_creation_fails_count', array('lt' => self::MAX_TRIES_TO_CREATE_ORDER)
        );
        $collection->addFieldToFilter(
            'magento_order_creation_latest_attempt_date', array('lt' => $backToDate->format('Y-m-d H:i:s'))
        );
        $collection->getSelect()->order('magento_order_creation_latest_attempt_date ASC');
        $collection->setPageSize(25);

        return $collection->getItems();
    }

    //########################################
}
