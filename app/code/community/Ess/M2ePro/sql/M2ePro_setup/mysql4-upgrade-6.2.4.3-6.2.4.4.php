<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

$lockItemTable = $installer->getTable('m2epro_lock_item');

$oldSkusQueueLockItems = $connection->query("
    SELECT *
    FROM `{$lockItemTable}`
    WHERE `nick` REGEXP '^(amazon|buy|play){1}_list_skus_queue_[0-9]+'
")->fetchAll();

$updatedSkusQueueLockItems   = array();
$removedSkusQueueLockItemIds = array();

foreach ($oldSkusQueueLockItems as $lockItemRow) {

    preg_match(
        '/^(?P<component>amazon|buy|play){1}_list_skus_queue_(?P<account_id>\d+)/',
        $lockItemRow['nick'],
        $matches
    );

    if (empty($matches['component']) || empty($matches['account_id'])) {
        $removedSkusQueueLockItemIds[] = $lockItemRow['id'];
        continue;
    }

    $component = $matches['component'];
    $accountId = (int)$matches['account_id'];

    $skusInQueue = $lockItemRow['data']
        ? json_decode($lockItemRow['data'], true) : array();

    if (empty($skusInQueue)) {
        $removedSkusQueueLockItemIds[] = $lockItemRow['id'];
        continue;
    }

    $processingTable = $installer->getTable('m2epro_processing_request');
    $responserModel  = 'M2ePro/Connector_'.ucfirst($component).'_Product_List_MultipleResponser';

    $processingRequests = $connection->query("
        SELECT * FROM `{$processingTable}`
        WHERE `component` = '{$component}' AND
              `responser_model` = '{$responserModel}'
    ")->fetchAll();

    if (empty($processingRequests)) {
        $removedSkusQueueLockItemIds[] = $lockItemRow['id'];
        continue;
    }

    $skusInProcessingRequests = array();

    foreach ($processingRequests as $processingRow) {

        $responserParams = $processingRow['responser_params']
            ? json_decode($processingRow['responser_params'], true) : array();

        if (empty($responserParams['account_id']) || (int)$responserParams['account_id'] != $accountId) {
            continue;
        }

        if (empty($responserParams['products']) || !is_array($responserParams['products'])) {
            continue;
        }

        foreach ($responserParams['products'] as $productData) {

            if (empty($productData['sku'])) {
                continue;
            }

            $skusInProcessingRequests[] = $productData['sku'];
        }
    }

    if (empty($skusInProcessingRequests)) {
        $removedSkusQueueLockItemIds[] = $lockItemRow['id'];
        continue;
    }

    if (!array_diff($skusInQueue, $skusInProcessingRequests)) {
        continue;
    }

    $lockItemRow['data'] = json_encode($skusInProcessingRequests);
    $updatedSkusQueueLockItems[] = $lockItemRow;
}

if (!empty($removedSkusQueueLockItemIds)) {
    $connection->delete($lockItemTable, array('id IN (?)' => $removedSkusQueueLockItemIds));
}

if (!empty($updatedSkusQueueLockItems)) {
    $connection->insertOnDuplicate($lockItemTable, $updatedSkusQueueLockItems);
}

//#############################################

$installer->endSetup();

//#############################################