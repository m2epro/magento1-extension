<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Order_Update_SellerOrderId
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'amazon/order/update/seller_order_id';

    const ORDERS_PER_MERCHANT = 1000;

    /** @var int (in seconds) */
    protected $_interval = 3600;

    //####################################

    public function isPossibleToRun()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    //####################################

    protected function performActions()
    {
        /** @var $accounts Ess_M2ePro_Model_Resource_Amazon_Account_Collection */
        $accounts = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');

        // Getting accounts with enabled feature
        $enabledAccountIds = array();
        $enabledMerchantIds = array();

        foreach ($accounts->getItems() as $account) {
            /** @var $account Ess_M2ePro_Model_Account */

            if ($account->getChildObject()->isMagentoOrdersNumberApplyToAmazonOrderEnable()) {
                $enabledAccountIds[] = $account->getId();
                $enabledMerchantIds[] = $account->getChildObject()->getMerchantId();
            }
        }

        if (empty($enabledAccountIds)) {
            return;
        }

        // Processing orders from last 30 days
        $backToDate = Mage::helper('M2ePro/Data')->createCurrentGmtDateTime();
        $backToDate->modify('-30 day');

        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $enabledMerchantIds = array_unique($enabledMerchantIds);

        foreach ($enabledMerchantIds as $enabledMerchantId) {
            // Preparing data structure for calls
            $orders = array();
            $accounts = array();
            $ordersToUpdate = array();
            $collection = $this->getOrderCollection(
                $enabledAccountIds,
                $enabledMerchantId,
                $backToDate->format('Y-m-d H:i:s')
            );

            foreach ($collection->getItems() as $orderData) {
                $orders[$orderData['order_id']] = array(
                    'amazon_order_id' => $orderData['amazon_order_id'],
                    'seller_order_id' => $orderData['increment_id']
                );
                $accounts[] = $orderData['server_hash'];

                $ordersToUpdate[$orderData['order_id']] = array(
                    'seller_order_id' => $orderData['increment_id']
                );
            }

            if (empty($ordersToUpdate)) {
                continue;
            }

            // Sending update requests
            /** @var Ess_M2ePro_Model_Amazon_Connector_Dispatcher $dispatcherObject */
            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'orders', 'update', 'sellerOrderId',
                array(
                    'orders' => $orders,
                    'accounts' => array_unique($accounts),
                    'ignore_processing_request' => 1
                )
            );
            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();

            // Updating orders if Amazon accepted processing
            if (isset($response['processed']) && $response['processed'] == true) {
                /** @var Ess_M2ePro_Model_Order_Log $logModel */
                $logModel = Mage::getModel('M2ePro/Order_Log');
                $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

                foreach ($ordersToUpdate as $orderId => $orderData) {
                    $connWrite->update(
                        Mage::getResourceModel('M2ePro/Amazon_Order')->getMainTable(),
                        array(
                            'seller_order_id' => $orderData['seller_order_id']
                        ),
                        '`order_id` = ' . $orderId
                    );

                    $logModel->addMessage(
                        $orderId,
                        Mage::helper('M2ePro')->__(
                            'Magento Order number has been set as Your Seller Order ID in Amazon Order details.'
                        ),
                        Ess_M2ePro_Model_Log_Abstract::TYPE_INFO
                    );
                }
            }
        }
    }

    /**
     * @param $enabledAccountIds
     * @param $enabledMerchantId
     * @param $date
     * @param $replacementDate
     *
     * @return Ess_M2ePro_Model_Resource_Order_Collection
     */
    private function getOrderCollection($enabledAccountIds, $enabledMerchantId, $date)
    {
        /** @var Ess_M2ePro_Model_Resource_Order_Collection $collection*/
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Order');
        $collection->getSelect()->join(
            array('sfo' => Mage::getModel('sales/order')->getResource()->getMainTable()),
            '(`main_table`.`magento_order_id` = `sfo`.`entity_id`)',
            array(
                'increment_id' => 'sfo.increment_id',
            )
        );

        $collection->getSelect()->join(
            array('maa' => Mage::getResourceModel('M2ePro/Amazon_Account')->getMainTable()),
            '(`main_table`.`account_id` = `maa`.`account_id`)',
            array(
                'merchant_id' => 'maa.merchant_id',
                'server_hash' => 'maa.server_hash',
            )
        );

        $collection->addFieldToFilter('main_table.account_id', array('in' => $enabledAccountIds));
        $collection->addFieldToFilter('main_table.magento_order_id', array('notnull' => true));
        $collection->addFieldToFilter(
            'second_table.status', array('neq' => Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED)
        );
        $collection->addFieldToFilter('second_table.seller_order_id', array('null' => true));
        $collection->addFieldToFilter('maa.merchant_id', array('eq' => $enabledMerchantId));
        $where = "`main_table`.`create_date` > '{$date}'";
        $collection->getSelect()->where($where);
        $collection->getSelect()->limit(self::ORDERS_PER_MERCHANT);

        return $collection;
    }
    //####################################
}
