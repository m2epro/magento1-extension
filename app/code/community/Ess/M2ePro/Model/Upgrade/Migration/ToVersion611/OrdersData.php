<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion611_OrdersData
{
    const MAX_ORDERS_PER_ITERATION = 100;

    const MAX_EXECUTION_LIMIT_PERCENT = 70;

    //########################################

    /** @var $connection Varien_Db_Adapter_Pdo_Mysql */
    private $connection = null;

    private $maxOrdersCount = null;

    private $maxBackDaysInterval = null;

    private $dataForOrderItems = array();

    private $startExecutionTimestamp = null;

    //########################################

    public function setMaxOrdersCount($ordersCount)
    {
        $this->maxOrdersCount = $ordersCount;
        return $this;
    }

    public function setMaxBackDaysInterval($days)
    {
        $this->maxBackDaysInterval = $days;
        return $this;
    }

    //########################################

    public function migrate()
    {
        $this->processFromLastProcessedOrder($this->getLastProcessedOrderId());
    }

    public function getNotMigratedOrdersCount()
    {
        $orderBackupTable = $this->getTableName(
            'm2epro'. Ess_M2ePro_Model_Upgrade_Migration_ToVersion611::BACKUP_TABLE_PREFIX .'_ebay_order'
        );
        if (!$this->isTableExists($orderBackupTable)) {
            return 0;
        }

        $lastProcessedOrderId = $this->getLastProcessedOrderId();

        $where = '';
        if (!is_null($lastProcessedOrderId)) {
            $where = 'WHERE `order_id` < '.$lastProcessedOrderId;
        }

        $tempQuery = $this->getConnection()->query("
            SELECT COUNT(*)
            FROM `{$orderBackupTable}`
            $where
        ");

        return $tempQuery->fetchColumn();
    }

    public function finishMigration()
    {
        $configTable = $this->getTableName('m2epro_config');

        $query = $this->getConnection()->query(<<<SQL

SELECT `value` FROM `{$configTable}`
WHERE `group` = '/ebay/order/migration_to_v611/' AND
      `key` = 'is_migration_finished'

SQL
        );
        if ($query->fetchColumn() !== false) {
            return;
        }

        $this->getConnection()->insert(
            $configTable,
            array(
                'group' => '/ebay/order/migration_to_v611/',
                'key'   => 'is_migration_finished',
                'value' => '1',
                'update_date' => Mage::getModel('core/date')->gmtDate(),
                'create_date' => Mage::getModel('core/date')->gmtDate(),
            )
        );
    }

    //########################################

    protected function processFromLastProcessedOrder($lastProcessedOrderId)
    {
        $ebayOrderTable = $this->getTableName('m2epro_ebay_order');
        $ebayOrderItemTable = $this->getTableName('m2epro_ebay_order_item');

        $processedOrdersCount = 0;
        $lastOrderCreateDate = null;

        while (true) {

            if ($this->isExceededMaxExecutionTime() ||
                $this->isExceededMinOrderCreateDate($lastOrderCreateDate) ||
                $this->isExceededMaxOrdersCount($processedOrdersCount)
            ) {
                break;
            }

            $ordersStatement = $this->getOrdersStatement($lastProcessedOrderId);
            if ($ordersStatement->rowCount() <= 0) {
                $this->finishMigration();
                break;
            }

            $performedOrders = array();
            $ordersPack = array();
            $itemsPack = array();

            while ($row = $ordersStatement->fetch()) {

                $orderId = (int)$row['order_id'];

                if (!isset($performedOrders[$orderId])) {
                    $ordersPack[] = $this->getNewOrderData($row);

                    $performedOrders[$orderId] = true;
                    $processedOrdersCount++;
                }

                $itemsPack[] = $this->getNewOrderItemData($row);

                $lastProcessedOrderId = $orderId;
                $lastOrderCreateDate = $row['create_date'];
            }

            if (empty($ordersPack)) {
                break;
            }

            // ---------------------------------------

            $this->getConnection()->beginTransaction();

            try {
                $this->getConnection()->insertMultiple($ebayOrderTable, $ordersPack);
                $this->getConnection()->insertMultiple($ebayOrderItemTable, $itemsPack);

                $this->saveLastProcessedOrderId($lastProcessedOrderId);
            } catch (Exception $e) {
                $this->getConnection()->rollBack();
                break;
            }

            $this->getConnection()->commit();
        }
    }

    //########################################

    protected function getNewOrderData(array $oldData)
    {
        $orderId = (int)$oldData['order_id'];

        $order = array(
            'order_id'             => $orderId,
            'ebay_order_id'        => $oldData['ebay_order_id'],
            'selling_manager_id'   => $oldData['selling_manager_record_number'],
            'buyer_name'           => $oldData['buyer_name'],
            'buyer_email'          => $oldData['buyer_email'],
            'buyer_user_id'        => $oldData['buyer_user_id'],
            'buyer_message'        => $oldData['checkout_buyer_message'],
            'checkout_status'      => $oldData['checkout_status'],
            'shipping_status'      => $oldData['shipping_status'],
            'payment_status'       => $oldData['payment_status'],
            'paid_amount'          => $oldData['paid_amount'],
            'saved_amount'         => $oldData['saved_amount'],
            'currency'             => $oldData['currency'],
            'purchase_create_date' => $oldData['purchase_create_date'],
            'purchase_update_date' => $oldData['purchase_update_date'],
        );

        // ---------------------------------------

        $shippingAddress = '';
        if (!empty($oldData['shipping_address'])) {
            $address = @unserialize($oldData['shipping_address']);

            if (is_array($address)) {
                // compatibility with M2E 3.x
                // ---------------------------------------
                $shippingAddress = array(
                    'country_code' => $address['country_id'],
                    'country_name' => null,
                    'city'         => $address['city'],
                    'state'        => $address['region_id'],
                    'postal_code'  => $address['postcode'],
                    'phone'        => $address['telephone'],
                    'street'       => $address['street']
                );
                // ---------------------------------------
            } else {
                $shippingAddress = json_decode($oldData['shipping_address'], true);
            }
        }

        $shippingService = null;
        if ($oldData['shipping_method'] != 'NotSelected') {
            $shippingService = $oldData['shipping_method'];
        }

        $globalShippingDetails = null;
        if (!empty($oldData['global_shipping_details'])) {
            $globalShippingDetails = json_decode($oldData['global_shipping_details'], true);
        }

        $shippingDetails = array(
            'address' => $shippingAddress,
            'service' => $shippingService,
            'price'   => (float)$oldData['shipping_price'],
            'date'    => $oldData['shipping_date'],
            'global_shipping_details' => $globalShippingDetails,
        );

        $order['shipping_details'] = json_encode($shippingDetails);

        // ---------------------------------------

        $paymentDetails = array(
            'method' => $oldData['payment_method'],
            'date'   => $oldData['payment_date'],
            'status' => $oldData['payment_status_ebay'],
        );

        $order['payment_details'] = json_encode($paymentDetails);

        // ---------------------------------------

        $taxDetails = null;
        if ((float)$oldData['tax_rate'] > 0) {
            $isVat = false;
            if ((float)$oldData['tax_rate'] > 0 && (float)$oldData['tax_amount'] <= 0) {
                $isVat = true;
            }

            $taxDetails = array(
                'rate'   => $oldData['tax_rate'],
                'amount' => $oldData['tax_amount'],
                'is_vat' => $isVat,
                'includes_shipping' => $oldData['tax_includes_shipping'],
            );
        }

        $order['tax_details'] = json_encode($taxDetails);

        // ---------------------------------------

        $trackingDetails = null;
        if (!empty($oldData['shipping_tracking_details'])) {
            // compatibility with M2E 3.x
            // ---------------------------------------
            $trackingDetails = @unserialize($oldData['shipping_tracking_details']);
            if (!is_array($trackingDetails)) {
                $trackingDetails = json_decode($oldData['shipping_tracking_details'], true);
            }

            if (is_array($trackingDetails) && !empty($trackingDetails)) {
                $trackingDetails = json_encode($trackingDetails);
            } else {
                $trackingDetails = null;
            }
            // ---------------------------------------
        }

        $this->dataForOrderItems[$orderId] = array(
            'tracking_details' => $trackingDetails,
            'tax_details'      => $taxDetails,
            'final_fee'        => $oldData['final_fee'],
        );

        return $order;
    }

    protected function getNewOrderItemData(array $oldData)
    {
        $orderId = (int)$oldData['order_id'];

        $item = array(
            'order_item_id'  => $oldData['order_item_id'],
            'transaction_id' => $oldData['transaction_id'],
            'item_id'        => $oldData['item_id'],
            'title'          => $oldData['title'],
            'sku'            => $oldData['sku'],
            'price'          => $oldData['price'],
            'final_fee'      => 0.0,
            'qty_purchased'  => $oldData['qty_purchased'],
            'unpaid_item_process_state' => $oldData['unpaid_item_process_state'],
        );

        // ---------------------------------------

        $variationDetails = array();
        if (!empty($oldData['variation'])) {
            // compatibility with M2E 3.x
            // ---------------------------------------
            $variationDetails = @unserialize($oldData['variation']);
            $variationDetails === false && $variationDetails = json_decode($oldData['variation'], true);
            $variationDetails = is_array($variationDetails) ? $variationDetails : array();
            // ---------------------------------------
        }

        if (!empty($variationDetails)) {
            $item['variation_details'] = json_encode(array(
                'title' => $oldData['title'],
                'sku'   => $oldData['sku'],
                'options' => $variationDetails,
            ));
        } else {
            $item['variation_details'] = null;
        }

        // ---------------------------------------

        $additionalItemData = array();
        if (isset($this->dataForOrderItems[$orderId])) {
            $additionalItemData = $this->dataForOrderItems[$orderId];
            unset($this->dataForOrderItems[$orderId]);
        }

        // ---------------------------------------

        $taxDetails = null;
        if (!empty($additionalItemData['tax_details'])) {

            $taxRate = (float)$additionalItemData['tax_details']['rate'];
            $taxAmount = 0.0;
            if (!$additionalItemData['tax_details']['is_vat']) {
                $taxAmount = (float)$oldData['price'] * ($taxRate / 100);
                $taxAmount = round($taxAmount, 2);
            }

            $taxDetails = array(
                'rate' => $taxRate,
                'amount' => $taxAmount,
            );
        }

        $item['tax_details'] = json_encode($taxDetails);

        // ---------------------------------------

        $item['final_fee'] = 0.0;
        $item['tracking_details'] = null;

        if (!empty($additionalItemData)) {
            $item['final_fee'] = $additionalItemData['final_fee'];
            $item['tracking_details'] = $additionalItemData['tracking_details'];
        }

        return $item;
    }

    //########################################

    protected function getLastProcessedOrderId()
    {
        $configTable = $this->getTableName('m2epro_config');

        $query = $this->getConnection()->query(<<<SQL

SELECT `value` FROM `{$configTable}`
WHERE `group` = '/ebay/order/migration_to_v611/' AND
      `key` = 'last_processed_order_id'

SQL
        );

        $lastProcessedOrderId = $query->fetchColumn();
        if ($lastProcessedOrderId === false) {
            return null;
        }

        return (int)$lastProcessedOrderId;
    }

    protected function saveLastProcessedOrderId($lastProcessedOrderId)
    {
        $configTable = $this->getTableName('m2epro_config');

        if (is_null($this->getLastProcessedOrderId())) {
            $dataForInsert = array(
                'group' => '/ebay/order/migration_to_v611/',
                'key'   => 'last_processed_order_id',
                'value' => $lastProcessedOrderId,
                'update_date' => Mage::getModel('core/date')->gmtDate(),
                'create_date' => Mage::getModel('core/date')->gmtDate(),
            );

            $this->getConnection()->insert($configTable, $dataForInsert);
        }

        $this->getConnection()->update(
            $configTable,
            array('value' => $lastProcessedOrderId, 'update_date' => Mage::getModel('core/date')->gmtDate()),
            array(
                '`group` = ?' => '/ebay/order/migration_to_v611/',
                '`key` = ?' => 'last_processed_order_id'
            )
        );
    }

    //########################################

    protected function getOrdersStatement($lastProcessedOrderId = null)
    {
        $orderBackupTable = $this->getTableName(
            'm2epro' . Ess_M2ePro_Model_Upgrade_Migration_ToVersion611::BACKUP_TABLE_PREFIX .'_ebay_order'
        );
        $orderItemBackupTable = $this->getTableName(
            'm2epro' . Ess_M2ePro_Model_Upgrade_Migration_ToVersion611::BACKUP_TABLE_PREFIX .'_ebay_order_item'
        );
        $orderTable = $this->getTableName('m2epro_order');
        $orderItemTable = $this->getTableName('m2epro_order_item');

        $where = '';
        if (!is_null($lastProcessedOrderId)) {
            $where = 'WHERE `meo`.`order_id` < ' . $lastProcessedOrderId;
        }

        $ordersCount = self::MAX_ORDERS_PER_ITERATION;

        return $this->getConnection()->query(<<<SQL

SELECT `meo`.*, `meoi`.*, `mo`.`create_date`
FROM `{$orderBackupTable}` AS `meo`
JOIN `{$orderTable}` AS `mo` ON `mo`.`id` = `meo`.`order_id`
JOIN `{$orderItemTable}` AS `moi` ON `moi`.`order_id` = `meo`.`order_id`
JOIN `{$orderItemBackupTable}` AS `meoi` ON `meoi`.`order_item_id` = `moi`.`id`
{$where}
ORDER BY `meo`.`order_id` DESC
LIMIT 0, {$ordersCount}

SQL
        );
    }

    //########################################

    protected function isExceededMaxExecutionTime()
    {
        if (is_null($this->startExecutionTimestamp)) {
            $this->startExecutionTimestamp = time();
            return false;
        }

        $maxExecutionTime = (int)@ini_get('max_execution_time');
        if ($maxExecutionTime <= 0) {
            return false;
        }

        $limit = (int)($maxExecutionTime * (self::MAX_EXECUTION_LIMIT_PERCENT / 100));

        return (time() - $this->startExecutionTimestamp) >= $limit;
    }

    protected function isExceededMinOrderCreateDate($orderCreateDate)
    {
        if (is_null($this->maxBackDaysInterval) || is_null($orderCreateDate)) {
            return false;
        }

        $minOrderCreateDateObject = new DateTime('now', new DateTimeZone('UTC'));
        $minOrderCreateDateObject->modify('- '.$this->maxBackDaysInterval.' days');

        $orderCreateDateObject = new DateTime($orderCreateDate, new DateTimeZone('UTC'));

        return (int)$orderCreateDateObject->format('U') < (int)$minOrderCreateDateObject->format('U');
    }

    protected function isExceededMaxOrdersCount($processedOrdersCount)
    {
        if (is_null($this->maxOrdersCount)) {
            return false;
        }

        return $processedOrdersCount > $this->maxOrdersCount;
    }

    //########################################

    protected function getConnection()
    {
        if (!is_null($this->connection)) {
            return $this->connection;
        }

        return $this->connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    protected function getTableName($table)
    {
        return Mage::getSingleton('core/resource')->getTableName($table);
    }

    protected function isTableExists($table)
    {
        $select = $this->getConnection()->quoteInto('SHOW TABLES LIKE ?', $table);
        $result = $this->getConnection()->fetchOne($select);
        return !empty($result);
    }

    //########################################
}