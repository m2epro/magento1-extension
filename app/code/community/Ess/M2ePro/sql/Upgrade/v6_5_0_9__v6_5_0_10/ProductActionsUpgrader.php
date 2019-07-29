<?php

class ProductActionsUpgrader
{
    const BACKUP_TABLE_SUFFIX             = '_b';
    const BACKUP_TABLE_IDENTIFIER_MAX_LEN = 20;

    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup $installer */
    private $installer  = NULL;

    /** @var Varien_Db_Adapter_Pdo_Mysql $connection */
    private $connection = NULL;

    //########################################

    public function __construct(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer, Varien_Db_Adapter_Pdo_Mysql $connection)
    {
        $this->installer  = $installer;
        $this->connection = $connection;
    }

    //########################################

    public function run()
    {
        if ($this->isCompleted()) {
            return;
        }

        if (!$this->isMovedToBackup()) {
            $this->moveToBackup();
            $this->markAsMovedToBackup();
        }

        $this->prepareStructure();

        $processingsStmt = $this->connection->select()
            ->from($this->getBackupTableName('processing'))
            ->query();

        while ($oldProcessingRow = $processingsStmt->fetch()) {

            if (strpos($oldProcessingRow['model'], 'Ebay_Connector_Item') !== false) {
                $this->processEbayItemProcessing($oldProcessingRow);
                continue;
            }

            if (strpos($oldProcessingRow['model'], 'Amazon_Connector_Product') !== false) {
                $this->processAmazonProductProcessing($oldProcessingRow);
                continue;
            }

            if (strpos($oldProcessingRow['model'], 'Amazon_Connector_Order') !== false) {
                $this->processAmazonOrderProcessing($oldProcessingRow);
                continue;
            }

            $newProcessingRow = $oldProcessingRow;
            unset($newProcessingRow['id']);

            $this->connection->insert($this->getTableName('processing'), $newProcessingRow);

            $this->updateProcessingLocks($oldProcessingRow, $this->connection->lastInsertId());
        }

        $this->removeBackup();
    }

    //########################################

    private function moveToBackup()
    {
        // required for correct work $this->connection->createTableByDdl() method
        $this->installer->getTableModifier('processing')->changeColumn(
            'model', 'VARCHAR(255) NOT NULL', NULL, 'id'
        );

        $this->moveTableToBackup('processing');

        $this->moveTableToBackup('ebay_processing_action');
        $this->moveTableToBackup('ebay_processing_action_item');

        $this->moveTableToBackup('amazon_processing_action');
        $this->moveTableToBackup('amazon_processing_action_item');
    }

    private function removeBackup()
    {
        $this->connection->dropTable($this->getBackupTableName('processing'));

        $this->connection->dropTable($this->getBackupTableName('ebay_processing_action'));
        $this->connection->dropTable($this->getBackupTableName('ebay_processing_action_item'));

        $this->connection->dropTable($this->getBackupTableName('amazon_processing_action'));
        $this->connection->dropTable($this->getBackupTableName('amazon_processing_action_item'));
    }

    private function prepareStructure()
    {
        $this->connection->dropTable($this->getTableName('processing'));
        $this->connection->dropTable($this->getTableName('ebay_processing_action'));
        $this->connection->dropTable($this->getTableName('amazon_processing_action'));

        if (!$this->installer->getTablesObject()->isExists('processing')) {
            $this->installer->run(<<<SQL

CREATE TABLE `m2epro_processing` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `model` VARCHAR(255) NOT NULL,
  `params` LONGTEXT DEFAULT NULL,
  `result_data` LONGTEXT DEFAULT NULL,
  `result_messages` LONGTEXT DEFAULT NULL,
  `is_completed` TINYINT(2) NOT NULL DEFAULT 0,
  `expiration_date` DATETIME NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `model` (`model`),
  INDEX `is_completed` (`is_completed`),
  INDEX `expiration_date` (`expiration_date`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
            );
        }

        if (!$this->installer->getTablesObject()->isExists('ebay_processing_action')) {
            $this->installer->run(<<<SQL

CREATE TABLE `m2epro_ebay_processing_action` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `processing_id` INT(11) UNSIGNED NOT NULL,
  `related_id` INT(11) UNSIGNED DEFAULT NULL,
  `type` VARCHAR(12) NOT NULL,
  `priority` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `request_timeout` INT(11) UNSIGNED DEFAULT NULL,
  `request_data` LONGTEXT NOT NULL,
  `start_date` DATETIME DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_id` (`account_id`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `processing_id` (`processing_id`),
  INDEX `related_id` (`related_id`),
  INDEX `type` (`type`),
  INDEX `priority` (`priority`),
  INDEX `start_date` (`start_date`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
            );
        }

        if (!$this->installer->getTablesObject()->isExists('amazon_processing_action')) {
            $this->installer->run(<<<SQL

CREATE TABLE `m2epro_amazon_processing_action` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `processing_id` INT(11) UNSIGNED NOT NULL,
  `request_pending_single_id` INT(11) UNSIGNED DEFAULT NULL,
  `related_id` INT(11) UNSIGNED DEFAULT NULL,
  `type` VARCHAR(12) NOT NULL,
  `request_data` LONGTEXT NOT NULL,
  `start_date` DATETIME DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_id` (`account_id`),
  INDEX `processing_id` (`processing_id`),
  INDEX `request_pending_single_id` (`request_pending_single_id`),
  INDEX `related_id` (`related_id`),
  INDEX `type` (`type`),
  INDEX `start_date` (`start_date`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
            );
        }

        $this->installer->getTableModifier('listing_product')->addColumn(
            'need_synch_rules_check', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'tried_to_list', true
        );

        $this->connection->dropTable($this->getTableName('ebay_processing_action_item'));
        $this->connection->dropTable($this->getTableName('amazon_processing_action_item'));
    }

    //########################################

    private function processEbayItemProcessing(array $oldProcessingRow)
    {
        if (!empty($oldProcessingRow['is_completed'])) {
            $isMultiple = strpos($oldProcessingRow['model'], 'Multiple') !== false;
            $oldProcessingParams = json_decode($oldProcessingRow['params'], true);

            if (!$isMultiple) {
                $listingsProductsIds = array($oldProcessingParams['listing_product_id']);
            } else {
                $listingsProductsIds = array_keys($oldProcessingParams['request_data']['items']);
            }

            foreach ($listingsProductsIds as $listingProductId) {
                $this->connection->insert(
                    $this->getTableName('processing'),
                    $this->prepareEbayItemProcessingData($oldProcessingRow, $listingProductId)
                );

                $this->updateProcessingLocks(
                    $oldProcessingRow, $this->connection->lastInsertId(), $listingProductId
                );
            }
        } else {
            $processingActionBackupTable = $this->getBackupTableName('ebay_processing_action');
            $processingActionItemBackupTable = $this->getBackupTableName('ebay_processing_action_item');
            $oldActionsData = $this->connection->query("
              SELECT `epab`.`account_id` AS `account_id`,
                     `epab`.`marketplace_id` AS `marketplace_id`,
                     `epab`.`type` AS `action_type`,
                     `epab`.`request_timeout` AS `request_timeout`,
                     `epab`.`update_date` AS `update_date`,
                     `epab`.`create_date` AS `create_date`,
                     `epaib`.`related_id` AS `related_id`,
                     `epaib`.`input_data` AS `input_data`,
                     `epaib`.`is_skipped` AS `is_skipped`
              FROM `{$processingActionItemBackupTable}` AS `epaib`
              LEFT JOIN `{$processingActionBackupTable}` AS `epab` ON `epab`.`id` = `epaib`.`action_id`
              WHERE `epab`.`processing_id` = {$oldProcessingRow['id']}
            ")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($oldActionsData as $oldActionData) {
                $this->connection->insert(
                    $this->getTableName('processing'),
                    $this->prepareEbayItemProcessingData($oldProcessingRow, $oldActionData['related_id'])
                );

                $newProcessingId = $this->connection->lastInsertId();

                $this->updateProcessingLocks(
                    $oldProcessingRow, $newProcessingId, $oldActionData['related_id']
                );

                $newActionData = array(
                    'processing_id'   => $newProcessingId,
                    'account_id'      => $oldActionData['account_id'],
                    'marketplace_id'  => $oldActionData['marketplace_id'],
                    'related_id'      => $oldActionData['related_id'],
                    'type'            => $oldActionData['action_type'],
                    'request_timeout' => $oldActionData['request_timeout'],
                    'request_data'    => $oldActionData['input_data'],
                    'start_date'      => $oldActionData['create_date'],
                    'update_date'     => $oldActionData['update_date'],
                    'create_date'     => $oldActionData['create_date'],
                );

                $this->connection->insert($this->getTableName('ebay_processing_action'), $newActionData);

                if (!empty($oldActionData['is_skipped'])) {
                    $this->connection->update(
                        $this->getTableName('listing_product'),
                        array('need_synch_rules_check' => 1),
                        array('id = ?' => $oldActionData['related_id'])
                    );
                }
            }
        }

        $this->connection->delete(
            $this->getTableName('processing_lock'),
            array('processing_id = ?' => $oldProcessingRow['id'])
        );
    }

    private function processAmazonProductProcessing(array $oldProcessingRow)
    {
        if (!empty($oldProcessingRow['is_completed'])) {
            $oldProcessingParams = json_decode($oldProcessingRow['params'], true);
            $listingsProductsIds = array_keys($oldProcessingParams['request_data']['items']);

            foreach ($listingsProductsIds as $listingProductId) {
                $this->connection->insert(
                    $this->getTableName('processing'),
                    $this->prepareAmazonProductProcessingData($oldProcessingRow, $listingProductId)
                );

                $this->updateProcessingLocks(
                    $oldProcessingRow, $this->connection->lastInsertId(), $listingProductId
                );
            }
        } else {
            $processingActionBackupTable = $this->getBackupTableName('amazon_processing_action');
            $processingActionItemBackupTable = $this->getBackupTableName('amazon_processing_action_item');

            $oldActionsData = $this->connection->query("
              SELECT `apab`.`account_id` AS `account_id`,
                     `apab`.`type` AS `action_type`,
                     `apab`.`update_date` AS `update_date`,
                     `apab`.`create_date` AS `create_date`,
                     `apaib`.`request_pending_single_id` AS `request_pending_single_id`,
                     `apaib`.`related_id` AS `related_id`,
                     `apaib`.`input_data` AS `input_data`,
                     `apaib`.`output_data` AS `output_data`,
                     `apaib`.`output_messages` AS `output_messages`,
                     `apaib`.`is_skipped` AS `is_skipped`
              FROM `{$processingActionItemBackupTable}` AS `apaib`
              LEFT JOIN `{$processingActionBackupTable}` AS `apab` ON `apab`.`id` = `apaib`.`action_id`
              WHERE `apab`.`processing_id` = {$oldProcessingRow['id']}
            ")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($oldActionsData as $oldActionData) {
                $listingProductId = $oldActionData['related_id'];

                $newProcessingData = $this->prepareAmazonProductProcessingData(
                    $oldProcessingRow, $oldActionData['related_id']
                );

                if (is_null($newProcessingData['result_data']) &&
                    !empty($oldActionData['is_completed']) &&
                    !empty($oldActionData['output_messages'])
                ) {
                    $newProcessingData['result_data'] = array(
                        'messages' => json_decode($oldActionData['output_messages'], true),
                    );

                    if (!empty($oldActionData['output_data'])) {
                        $newProcessingData['result_data'] = array_merge(
                            json_decode($oldActionData['output_data'], true), $newProcessingData['result_data']
                        );
                    }
                }

                $this->connection->insert($this->getTableName('processing'), $newProcessingData);

                $newProcessingId = $this->connection->lastInsertId();

                $this->updateProcessingLocks($oldProcessingRow, $newProcessingId, $oldActionData['related_id']);

                $newActionData = array(
                    'processing_id'             => $newProcessingId,
                    'account_id'                => $oldActionData['account_id'],
                    'request_pending_single_id' => $oldActionData['request_pending_single_id'],
                    'related_id'                => $listingProductId,
                    'type'                      => $oldActionData['action_type'],
                    'request_data'              => $oldActionData['input_data'],
                    'start_date'                => $oldActionData['create_date'],
                    'update_date'               => $oldActionData['update_date'],
                    'create_date'               => $oldActionData['create_date'],
                );

                $this->connection->insert($this->getTableName('amazon_processing_action'), $newActionData);

                if (!empty($oldActionData['is_skipped'])) {
                    $this->connection->update(
                        $this->getTableName('listing_product'),
                        array('need_synch_rules_check' => 1),
                        array('id = ?' => $listingProductId)
                    );
                }
            }
        }

        $this->connection->delete(
            $this->getTableName('processing_lock'),
            array('processing_id = ?' => $oldProcessingRow['id'])
        );
    }

    private function processAmazonOrderProcessing(array $oldProcessingRow)
    {
        if (!empty($oldProcessingRow['is_completed'])) {
            $oldProcessingParams = json_decode($oldProcessingRow['params'], true);
            if (isset($oldProcessingParams['request_data']['items'])) {
                $changesIds = array_keys($oldProcessingParams['request_data']['items']);
            } else {
                $changesIds = array_keys($oldProcessingParams['request_data']['orders']);
            }

            foreach ($changesIds as $changeId) {
                $newProcessingData = $this->prepareAmazonOrderProcessingData($oldProcessingRow, $changeId);

                $this->connection->insert($this->getTableName('processing'), $newProcessingData);

                $newProcessingParams = json_decode($newProcessingData['params'], true);
                $this->updateProcessingLocks(
                    $oldProcessingRow, $this->connection->lastInsertId(), $newProcessingParams['order_id']
                );
            }
        } else {
            $processingActionBackupTable = $this->getBackupTableName('amazon_processing_action');
            $processingActionItemBackupTable = $this->getBackupTableName('amazon_processing_action_item');

            $oldActionsData = $this->connection->query("
              SELECT `apab`.`account_id` AS `account_id`,
                     `apab`.`type` AS `action_type`,
                     `apab`.`update_date` AS `update_date`,
                     `apab`.`create_date` AS `create_date`,
                     `apaib`.`request_pending_single_id` AS `request_pending_single_id`,
                     `apaib`.`related_id` AS `related_id`,
                     `apaib`.`input_data` AS `input_data`,
                     `apaib`.`output_data` AS `output_data`,
                     `apaib`.`output_messages` AS `output_messages`,
                     `apaib`.`is_skipped` AS `is_skipped`
              FROM `{$processingActionItemBackupTable}` AS `apaib`
              LEFT JOIN `{$processingActionBackupTable}` AS `apab` ON `apab`.`id` = `apaib`.`action_id`
              WHERE `apab`.`processing_id` = {$oldProcessingRow['id']}
            ")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($oldActionsData as $oldActionData) {
                $newProcessingData = $this->prepareAmazonOrderProcessingData(
                    $oldProcessingRow, $oldActionData['related_id']
                );

                if (is_null($newProcessingData['result_data']) &&
                    !empty($oldActionData['is_completed']) &&
                    !empty($oldActionData['output_messages'])
                ) {
                    $newProcessingData['result_data'] = array(
                        'messages' => json_decode($oldActionData['output_messages'], true),
                    );

                    if (!empty($oldActionData['output_data'])) {
                        $newProcessingData['result_data'] = array_merge(
                            json_decode($oldActionData['output_data'], true), $newProcessingData['result_data']
                        );
                    }
                }

                $this->connection->insert($this->getTableName('processing'), $newProcessingData);

                $newProcessingId = $this->connection->lastInsertId();

                $newProcessingParams = json_decode($newProcessingData['params'], true);
                $this->updateProcessingLocks($oldProcessingRow, $newProcessingId, $newProcessingParams['order_id']);

                $newActionData = array(
                    'processing_id'             => $newProcessingId,
                    'account_id'                => $oldActionData['account_id'],
                    'request_pending_single_id' => $oldActionData['request_pending_single_id'],
                    'related_id'                => $oldActionData['related_id'],
                    'type'                      => $oldActionData['action_type'],
                    'request_data'              => $oldActionData['input_data'],
                    'start_date'                => $oldActionData['create_date'],
                    'update_date'               => $oldActionData['update_date'],
                    'create_date'               => $oldActionData['create_date'],
                );

                $this->connection->insert($this->getTableName('amazon_processing_action'), $newActionData);
            }
        }

        $this->connection->delete(
            $this->getTableName('processing_lock'),
            array('processing_id = ?' => $oldProcessingRow['id'])
        );
    }

    //########################################

    private function prepareEbayItemProcessingData(array $oldProcessingRow, $listingProductId)
    {
        $isMultiple = strpos($oldProcessingRow['model'], 'Multiple') !== false;

        $oldProcessingParams = json_decode($oldProcessingRow['params'], true);
        $oldResponserParams  = $oldProcessingParams['responser_params'];

        if (!$isMultiple) {
            $productResponserData = $oldResponserParams['product'];
        } else {
            $productResponserData = array_merge(
                array('id' => $listingProductId),
                $oldResponserParams['products'][$listingProductId]
            );
        }

        $newResponserParams = array(
            'is_realtime'     => $oldResponserParams['is_realtime'],
            'account_id'      => $oldResponserParams['account_id'],
            'action_type'     => $oldResponserParams['action_type'],
            'lock_identifier' => $oldResponserParams['lock_identifier'],
            'logs_action'     => $oldResponserParams['logs_action'],
            'logs_action_id'  => $oldResponserParams['logs_action_id'],
            'status_changer'  => $oldResponserParams['status_changer'],
            'params'          => $oldResponserParams['params'],
            'product'         => $productResponserData,
        );

        if (!$isMultiple) {
            $processingRequestData = $oldProcessingParams['request_data'];
            $responserModelName    = str_replace('Single', '', $oldProcessingParams['responser_model_name']);
        } else {
            $processingRequestData = $oldProcessingParams['request_data']['items'][$listingProductId];
            $responserModelName    = str_replace('Multiple', '', $oldProcessingParams['responser_model_name']);
        }

        $newProcessingParams = array(
            'component'            => $oldProcessingParams['component'],
            'server_hash'          => $oldProcessingParams['server_hash'],
            'account_id'           => $oldProcessingParams['account_id'],
            'marketplace_id'       => $oldProcessingParams['marketplace_id'],
            'request_data'         => $processingRequestData,
            'listing_product_id'   => $listingProductId,
            'lock_identifier'      => $oldProcessingParams['lock_identifier'],
            'action_type'          => $oldProcessingParams['action_type'],
            'start_date'           => $oldProcessingRow['create_date'],
            'responser_model_name' => $responserModelName,
            'responser_params'     => $newResponserParams,
        );

        $newProcessingResultData = NULL;
        if (!empty($oldProcessingRow['result_data'])) {
            $newProcessingResultData = json_decode($oldProcessingRow['result_data'], true);
            if ($isMultiple) {
                $newProcessingResultData = $newProcessingResultData['result'][$listingProductId];
            }
        }

        return array(
            'model'           => str_replace(array('Single_', 'Multiple_'), array('', ''), $oldProcessingRow['model']),
            'params'          => json_encode($newProcessingParams),
            'is_completed'    => $oldProcessingRow['is_completed'],
            'result_data'     => !is_null($newProcessingResultData) ? json_encode($newProcessingResultData) : NULL,
            'expiration_date' => $oldProcessingRow['expiration_date'],
            'update_date'     => $oldProcessingRow['update_date'],
            'create_date'     => $oldProcessingRow['create_date'],
        );
    }

    private function prepareAmazonProductProcessingData(array $oldProcessingRow, $listingProductId)
    {
        $oldProcessingParams = json_decode($oldProcessingRow['params'], true);
        $oldResponserParams  = $oldProcessingParams['responser_params'];

        $newResponserParams = array(
            'account_id'      => $oldResponserParams['account_id'],
            'action_type'     => $oldResponserParams['action_type'],
            'lock_identifier' => $oldResponserParams['lock_identifier'],
            'logs_action'     => $oldResponserParams['logs_action'],
            'logs_action_id'  => $oldResponserParams['logs_action_id'],
            'status_changer'  => $oldResponserParams['status_changer'],
            'params'          => $oldResponserParams['params'],
            'product'         => array_merge(
                array('id' => $listingProductId),
                $oldResponserParams['products'][$listingProductId]
            ),
        );

        $newProcessingParams = array(
            'component'            => $oldProcessingParams['component'],
            'server_hash'          => $oldProcessingParams['server_hash'],
            'account_id'           => $oldProcessingParams['account_id'],
            'request_data'         => $oldProcessingParams['request_data']['items'][$listingProductId],
            'listing_product_id'   => $listingProductId,
            'lock_identifier'      => $oldProcessingParams['lock_identifier'],
            'action_type'          => $oldProcessingParams['action_type'],
            'start_date'           => $oldProcessingRow['create_date'],
            'responser_model_name' => str_replace('Multiple', '', $oldProcessingParams['responser_model_name']),
            'responser_params'     => $newResponserParams,
        );

        $newProcessingResultData = NULL;
        if (!empty($oldProcessingRow['result_data'])) {
            $oldProcessingResultData = json_decode($oldProcessingRow['result_data'], true);

            $newProcessingResultData = array(
                'messages' => $oldProcessingResultData['messages'][$listingProductId],
            );

            if (isset($oldProcessingResultData['asins'][$listingProductId])) {
                $newProcessingResultData['asins'] = $oldProcessingResultData['asins'][$listingProductId];
            }
        }

        return array(
            'model'           => str_replace('Multiple', '', $oldProcessingRow['model']),
            'params'          => json_encode($newProcessingParams),
            'result_data'     => !is_null($newProcessingResultData) ? json_encode($newProcessingResultData) : NULL,
            'is_completed'    => $oldProcessingRow['is_completed'],
            'expiration_date' => $oldProcessingRow['expiration_date'],
            'update_date'     => $oldProcessingRow['update_date'],
            'create_date'     => $oldProcessingRow['create_date'],
        );
    }

    private function prepareAmazonOrderProcessingData(array $oldProcessingRow, $changeId)
    {
        $oldProcessingParams = json_decode($oldProcessingRow['params'], true);
        $oldResponserParams  = $oldProcessingParams['responser_params'];

        $orderId = NULL;
        foreach ($oldResponserParams as $responserParamsChangeId => $orderResponserParams) {
            if ($responserParamsChangeId != $changeId) {
                continue;
            }

            $orderId = $orderResponserParams['order_id'];
            break;
        }

        $newResponserParams = array(
            'order' => $oldResponserParams[$changeId],
        );

        $newProcessingParams = array(
            'component'            => $oldProcessingParams['component'],
            'server_hash'          => $oldProcessingParams['server_hash'],
            'account_id'           => $oldProcessingParams['account_id'],
            'request_data'         => isset($oldProcessingParams['request_data']['items'])
                ? $oldProcessingParams['request_data']['items'][$changeId]
                : $oldProcessingParams['request_data']['orders'][$changeId],
            'order_id'             => $orderId,
            'responser_model_name' => $oldProcessingParams['responser_model_name'],
            'responser_params'     => $newResponserParams,
        );

        $newProcessingResultData = NULL;

        if (!empty($oldProcessingRow['result_data'])) {
            $oldProcessingResultData = json_decode($oldProcessingRow['result_data'], true);

            $newProcessingResultData = array(
                'messages' => $oldProcessingResultData['messages'][$changeId],
            );
        }

        return array(
            'model'           => $oldProcessingRow['model'],
            'params'          => json_encode($newProcessingParams),
            'result_data'     => !is_null($newProcessingResultData) ? json_encode($newProcessingResultData) : NULL,
            'is_completed'    => $oldProcessingRow['is_completed'],
            'expiration_date' => $oldProcessingRow['expiration_date'],
            'update_date'     => $oldProcessingRow['update_date'],
            'create_date'     => $oldProcessingRow['create_date'],
        );
    }

    //########################################

    private function isCompleted()
    {
        return !$this->connection->isTableExists($this->getTableName('ebay_processing_action_item')) &&
            !$this->connection->isTableExists($this->getBackupTableName('processing'));
    }

    //########################################

    private function isMovedToBackup()
    {
        if (!$this->connection->isTableExists($this->getBackupTableName('ebay_processing_action_item'))) {
            return false;
        }

        $select = $this->connection->select()
            ->from($this->getBackupTableName('ebay_processing_action_item'))
            ->order('id DESC')
            ->limit(1);

        $row = $this->connection->fetchRow($select);

        if (empty($row['input_data'])) {
            return false;
        }

        $rowInputData = json_decode($row['input_data'], true);

        return !empty($rowInputData['to_651_moved_to_backup']);
    }

    private function markAsMovedToBackup()
    {
        $this->connection->insert(
            $this->getBackupTableName('ebay_processing_action_item'),
            array(
                'action_id'  => 0,
                'related_id' => 0,
                'input_data' => json_encode(array('to_651_moved_to_backup' => true)),
                'is_skipped' => 0,
            )
        );
    }

    //----------------------------------------

    private function moveTableToBackup($tableName)
    {
        if (!$this->connection->isTableExists($this->getTableName($tableName))) {
            return;
        }

        if ($this->connection->isTableExists($this->getBackupTableName($tableName))) {
            $this->connection->dropTable($this->getBackupTableName($tableName));
        }

        $this->connection->renameTable($this->getTableName($tableName), $this->getBackupTableName($tableName));
    }

    //########################################

    private function updateProcessingLocks(array $oldProcessingRow, $newProcessingId, $objectId = NULL)
    {
        $where = array(
            'processing_id = ?' => $oldProcessingRow['id'],
        );

        if (!is_null($objectId)) {
            $where['object_id = ?'] = $objectId;
        }

        $this->connection->update(
            $this->getTableName('processing_lock'),
            array('processing_id' => $newProcessingId),
            $where
        );
    }

    //########################################

    private function getTableName($table)
    {
        return $this->installer->getTable('m2epro_'.$table);
    }

    private function getBackupTableName($table)
    {
        $tableName = $this->getTableName($table).self::BACKUP_TABLE_SUFFIX;

        if (strlen($tableName) > self::BACKUP_TABLE_IDENTIFIER_MAX_LEN) {
            $tableName = 'm2epro'.'_'.sha1($tableName).self::BACKUP_TABLE_SUFFIX;
        }

        return $tableName;
    }

    //########################################
}