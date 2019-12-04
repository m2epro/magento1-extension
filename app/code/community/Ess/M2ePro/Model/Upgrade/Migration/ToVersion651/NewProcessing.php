<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

// @codingStandardsIgnoreFile

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_NewProcessing extends Ess_M2ePro_Model_Upgrade_Migration_Abstract
{
    //########################################

    public function run()
    {
        if ($this->isCompleted()) {
            return;
        }

        $this->createStructure();
        $this->migrateProcessings();

        $this->fillUpGroupHash();

        if ($this->_installer->getTablesObject()->isExists('processing_request')) {
            $this->_installer->run("DROP TABLE `m2epro_processing_request`");
        }

        $this->_installer->getTableModifier('processing_lock')
                         ->dropColumn('related_hash', true, true);

        $this->_installer->getConnection()->delete(
            $this->_installer->getTablesObject()->getFullName('processing_lock'),
            array('processing_id = ?' => 0)
        );
    }

    //########################################

    public function isCompleted()
    {
        return !$this->_installer->getTablesObject()->isExists('processing_request');
    }

    //########################################

    protected function createStructure()
    {
        if ($this->_installer->getTablesObject()->isExists('locked_object') &&
            !$this->_installer->getTablesObject()->isExists('processing_lock')
        ) {
            $this->_installer->run(
                <<<SQL
RENAME TABLE `m2epro_locked_object` TO `m2epro_processing_lock`;
SQL
            );
        }

        $this->_installer->getTableModifier('processing_lock')
                         ->addColumn('processing_id', 'INT(11) UNSIGNED NOT NULL', null, 'id', true, false)
                         ->dropColumn('description', true, false)
                         ->commit();

        $this->_installer->getTableModifier('lock_item')
                         ->dropColumn('kill_now');
    }

    protected function migrateProcessings()
    {
        $processingLockTable    = $this->_installer->getTablesObject()->getFullName('processing_lock');
        $processingRequestTable = $this->_installer->getTablesObject()->getFullName('processing_request');

        $performTypeSingle = 1;
        $processingRequestsStmt = $this->_installer->getConnection()->query("SELECT * FROM {$processingRequestTable}");

        while ($prRow = $processingRequestsStmt->fetch()) {
            if ($prRow['perform_type'] != $performTypeSingle) {
                $this->_installer->getConnection()->delete(
                    $processingRequestTable, array('id = ?' => $prRow['id'])
                );
                $this->_installer->getConnection()->delete(
                    $processingLockTable, array('related_hash = ?' => $prRow['hash'])
                );
                continue;
            }

            // -- Request Pending Single
            $this->_installer->getConnection()->insert(
                $this->getFullTableName('request_pending_single'),
                array(
                    'component'       => $prRow['component'],
                    'is_completed'    => 0,
                    'server_hash'     => $prRow['processing_hash'],
                    'expiration_date' => $prRow['expiration_date'],
                    'update_date'     => $prRow['update_date'],
                    'create_date'     => $prRow['create_date'],
                )
            );
            $requestPendingSingleId = $this->_installer->getConnection()->lastInsertId(
                $this->getFullTableName('request_pending_single')
            );
            // --

            if (preg_match('/^M2ePro\/Connector_Amazon_Product_/', $prRow['responser_model'])) {
                $this->migrateAmazonProductProcessing($prRow, $requestPendingSingleId);
            }

            if (preg_match('/^M2ePro\/Connector_Amazon_Orders_(Update|Cancel|Refund)_/', $prRow['responser_model'])) {
                $this->migrateAmazonOrderProcessing($prRow, $requestPendingSingleId);
            }

            $this->_installer->getConnection()->delete($processingRequestTable, array('id = ?' => $prRow['id']));
        }
    }

    // ---------------------------------------

    protected function migrateAmazonProductProcessing($processingRow, $requestPendingSingleId)
    {
        $responserParams = (array)json_decode($processingRow['responser_params'], true);
        $modelName = strpos($processingRow['responser_model'], 'List') !== false
            ? 'M2ePro/Amazon_Connector_Product_List_ProcessingRunner'
            : 'M2ePro/Amazon_Connector_Product_ProcessingRunner';

        $processingActionType = null;
        switch ($responserParams['action_type']) {
            case 1:
                $processingActionType = 'add';
                break;

            case 2:
            case 3:
            case 4:
                $processingActionType = 'update';
                break;

            case 5:
                $processingActionType = 'delete';
                break;
        }

        $responserModel = str_replace('Connector_Amazon', 'Amazon_Connector', $processingRow['responser_model']);
        $responserModel = str_replace('Multiple', '', $responserModel);

        foreach ($responserParams['products'] as $listingProductId => $productData) {
            //--
            $configurator = $productData['configurator'];

            if (isset($configurator['mode'])) {
                $configurator['mode'] == 'full' ? $configurator['mode'] = 'excluding'
                                                : $configurator['mode'] = 'including';
            }

            if (isset($configurator['allowed_data_types'])) {
                $allowedDataTypes = $configurator['allowed_data_types'];
                $priceDataTypeIndex = array_search('price', $allowedDataTypes);
                if ($priceDataTypeIndex !== false) {
                    unset($allowedDataTypes[$priceDataTypeIndex]);
                    $allowedDataTypes[] = 'regular_price';
                }

                $configurator['allowed_data_types'] = $allowedDataTypes;
            }

            //--

            //--
            $request = $productData['request'];
            $request['id'] = $listingProductId;
            //--

            $newResponserParams = $responserParams;
            unset($newResponserParams['products']);
            $newResponserParams['product']['request'] = $request;
            $newResponserParams['product']['request_metadata'] = array();
            $newResponserParams['product']['configurator'] = $configurator;
            $newResponserParams['product']['id'] = $listingProductId;

            // -- Processing
            $processingData = array(
                'model'  => $modelName,
                'params' => json_encode(
                    array(
                    'component'            => 'Amazon',
                    'server_hash'          => null,
                    'account_id'           => $responserParams['account_id'],
                    'request_data'         => $request,
                    'configurator'         => $configurator,
                    'listing_product_id'   => $listingProductId,
                    'lock_identifier'      => $responserParams['lock_identifier'],
                    'action_type'          => $responserParams['action_type'],
                    'requester_params'     => $responserParams['params'],
                    'responser_model_name' => $responserModel,
                    'responser_params'     => $newResponserParams
                    )
                ),
                'type'            => 1,
                'is_completed'    => 0,
                'expiration_date' => $processingRow['expiration_date'],
                'update_date'     => $processingRow['update_date'],
                'create_date'     => $processingRow['create_date'],
            );

            $this->_installer->getConnection()->insert($this->getFullTableName('processing'), $processingData);
            $processingId = $this->_installer->getConnection()->lastInsertId($this->getFullTableName('processing'));
            // --

            // -- Processing Product Action
            $this->_installer->getConnection()->insert(
                $this->getFullTableName('amazon_listing_product_action_processing'),
                array(
                    'listing_product_id'        => $listingProductId,
                    'processing_id'             => $processingId,
                    'request_pending_single_id' => $requestPendingSingleId,
                    'type'                      => $processingActionType,
                    'is_prepared'               => 1,
                    'group_hash'                => null,
                    'request_data'              => json_encode($request),
                    'update_date'               => $processingRow['update_date'],
                    'create_date'               => $processingRow['create_date'],
                )
            );
            // --

            // -- Processing lock
            $this->_installer->getConnection()->update(
                $this->_installer->getTablesObject()->getFullName('processing_lock'),
                array('processing_id' => $processingId),
                array(
                    'related_hash = ?' => $processingRow['hash'],
                    'object_id = ?'    => $listingProductId
                )
            );
            // --
        }
    }

    protected function migrateAmazonOrderProcessing($processingRow, $requestPendingSingleId)
    {
        $requestBody     = (array)json_decode($processingRow['request_body'], true);
        $requestBody     = (array)json_decode($requestBody['data'], true);
        $responserParams = (array)json_decode($processingRow['responser_params'], true);

        if (empty($requestBody['items'])) {
            return;
        }

        switch ($processingRow['responser_model']) {
            case 'M2ePro/Connector_Amazon_Orders_Refund_ItemsResponser':
                $actionType         = 'refund';
                $lockName           = 'refund_order';
                $responserModelName = 'M2ePro/Cron_Task_Amazon_Order_Refund_Responser';
                break;

            case 'M2ePro/Connector_Amazon_Orders_Cancel_ItemsResponser':
                $actionType         = 'cancel';
                $lockName           = 'cancel_order';
                $responserModelName = 'M2ePro/Cron_Task_Amazon_Order_Cancel_Responser';
                break;

            case 'M2ePro/Connector_Amazon_Orders_Update_ItemsResponser':
                $actionType         = 'update';
                $lockName           = 'update_shipping_status';
                $responserModelName = 'M2ePro/Cron_Task_Amazon_Order_Update_Responser';
                break;

            default:
                $actionType = null;
                $lockName = null;
                $responserModelName = null;
        }

        $ordersIds = array();
        foreach ($responserParams as $responserParamsItem) {
            $ordersIds[] = $responserParamsItem['order_id'];
        }

        $ordersIds = implode(',', $ordersIds);

        $accountIdsDataStmt = $this->_installer->getConnection()->query(
            "
SELECT `id`, `account_id`
FROM `{$this->getFullTableName('order')}`
WHERE `id` IN ({$ordersIds});
"
        );

        $orderToAccountMap = array();
        while ($row = $accountIdsDataStmt->fetch()) {
            $orderToAccountMap[$row['id']] = $row['account_id'];
        }

        foreach ($requestBody['items'] as $requestItem) {
            $changeId = $requestItem['id'];
            $responserParamsItem = isset($responserParams[$changeId]) ? $responserParams[$changeId]
                                                                      : array();

            $accountId = isset($orderToAccountMap[$responserParamsItem['order_id']])
                ? $orderToAccountMap[$responserParamsItem['order_id']]
                : null;

            // -- Processing
            $processingData = array(
                'model'  => 'M2ePro/Amazon_Connector_Orders_ProcessingRunner',
                'params' => json_encode(
                    array(
                    'component'    => 'Amazon',
                    'server_hash'  => null,
                    'account_id'   => $accountId,
                    'request_data' => $requestItem,
                    'order_id'     => $responserParamsItem['order_id'],
                    'change_id'    => $changeId,
                    'action_type'  => $actionType,
                    'lock_name'    => $lockName,
                    'start_date'   => $processingRow['create_date'],

                    'responser_model_name' => $responserModelName,
                    'responser_params'     => array(
                        'order'      => $responserParamsItem,
                        'account_id' => $accountId
                    ),
                    )
                ),
                'type'            => 1,
                'is_completed'    => 0,
                'expiration_date' => $processingRow['expiration_date'],
                'update_date'     => $processingRow['update_date'],
                'create_date'     => $processingRow['create_date'],
            );

            $this->_installer->getConnection()->insert($this->getFullTableName('processing'), $processingData);
            $processingId = $this->_installer->getConnection()->lastInsertId($this->getFullTableName('processing'));
            // --

            // -- Processing Order Action
            $this->_installer->getConnection()->insert(
                $this->getFullTableName('amazon_order_action_processing'),
                array(
                    'order_id'                  => $responserParamsItem['order_id'],
                    'processing_id'             => $processingId,
                    'request_pending_single_id' => $requestPendingSingleId,
                    'type'                      => $actionType,
                    'request_data'              => json_encode($requestItem),
                    'update_date'               => $processingRow['update_date'],
                    'create_date'               => $processingRow['create_date'],
                )
            );
            // --

            // -- Processing Lock
            $this->_installer->getConnection()->update(
                $this->_installer->getTablesObject()->getFullName('processing_lock'),
                array('processing_id' => $processingId),
                array(
                    'related_hash = ?' => $processingRow['hash'],
                    'object_id = ?'    => $responserParamsItem['order_id']
                )
            );
            // --
        }
    }

    // ---------------------------------------

    protected function fillUpGroupHash()
    {
        $stmt = $this->_installer->getConnection()->select()
            ->from(
                array('alpap' => $this->_installer->getFullTableName('amazon_listing_product_action_processing')),
                array('id', 'listing_product_id', 'type')
            )
            ->joinLeft(
                array('lp' => $this->_installer->getFullTableName('listing_product')),
                'lp.id = alpap.listing_product_id',
                array()
            )
            ->joinLeft(
                array('l' => $this->_installer->getFullTableName('listing')),
                'l.id = lp.listing_id',
                array('account_id')
            )
            ->where('alpap.group_hash IS NULL')->query();

        $updateLpIds = array();
        while ($actionData = $stmt->fetch()) {
            $updateLpIds[$actionData['account_id']][$actionData['type']][] = $actionData['listing_product_id'];
        }

        foreach ($updateLpIds as $accountId => $accountActionsData) {
            foreach ($accountActionsData as $actionType => $listingProductIds) {
                if ($actionType == 'delete') {
                    $maxGroupSize = 10000;
                } else {
                    $maxGroupSize = 1000;
                }

                $listingProductIdsGroups = array_chunk($listingProductIds, $maxGroupSize);

                foreach ($listingProductIdsGroups as $listingProductIdsGroup) {
                    $groupHash = sha1(microtime());

                    $this->_installer->getConnection()->update(
                        $this->_installer->getFullTableName('amazon_listing_product_action_processing'),
                        array('group_hash' => $groupHash),
                        array('listing_product_id IN (?)' => $listingProductIdsGroup)
                    );
                }
            }
        }
    }

    //########################################
}
