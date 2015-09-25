<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion630_Processing
{
    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    private $installer = NULL;

    //####################################

    public function getInstaller()
    {
        return $this->installer;
    }

    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->installer = $installer;
    }

    //####################################

    public function process()
    {
        $this->clearUnnecessaryLockedObjects();
        $this->processReceiveItems();
        $this->processSearch();
        $this->processProductActions();
    }

    //####################################

    private function clearUnnecessaryLockedObjects()
    {
        $this->installer->run(<<<SQL

DELETE FROM `m2epro_locked_object`
  WHERE model_name = 'M2ePro/Account'
  OR model_name = 'M2ePro/Marketplace';

SQL
        );
    }

    private function processReceiveItems()
    {
        $connection = $this->installer->getConnection();

        $processingTable = $this->installer->getTable('m2epro_processing_request');

        $responserModels = array(
            '\'M2ePro/Amazon_Synchronization_Defaults_UpdateListingsProducts_Responser\'',
            '\'M2ePro/Amazon_Synchronization_OtherListings_Responser\'',
            '\'M2ePro/Play_Synchronization_Defaults_UpdateListingsProducts_Responser\'',
            '\'M2ePro/Play_Synchronization_OtherListings_Responser\'',
        );

        $oldProcessingRows = $connection->query("
            SELECT * FROM `{$processingTable}`
            WHERE responser_model IN (".implode(',', $responserModels).")
        ")->fetchAll();

        $newProcessingRows = array();
        foreach ($oldProcessingRows as $row) {
            $responserParams = @json_decode($row['responser_params'], true);
            $responserParams['processed_inventory_hash'] = $row['hash'];
            $row['responser_params'] = json_encode($responserParams);

            $newProcessingRows[] = $row;
        }

        if (!empty($newProcessingRows)) {
            $connection->insertOnDuplicate($processingTable, $newProcessingRows);
        }
    }

    private function processSearch()
    {
        $connection = $this->installer->getConnection();

        $processingTable = $this->installer->getTable('m2epro_processing_request');

        $processingRows = $connection->query("
            SELECT `hash` FROM `{$processingTable}`
            WHERE responser_model LIKE 'M2ePro/Amazon_Search_%'
        ")->fetchAll();

        $hashes = array();
        foreach ($processingRows as $row) {
            $hashes[] = '\''.$row['hash'].'\'';
        }

        if (empty($hashes)) {
            return;
        }

        $hashes = implode(',', $hashes);

        $this->getInstaller()->run(<<<SQL

    DELETE FROM `m2epro_locked_object`
      WHERE `related_hash` IN ($hashes);

    DELETE FROM `m2epro_processing_request`
      WHERE `hash` IN ($hashes);

SQL

        );
    }

    private function processProductActions()
    {
        $connection = $this->installer->getConnection();

        $processingTable = $this->installer->getTable('m2epro_processing_request');

        $oldProcessingRows = $connection->query("
            SELECT * FROM `{$processingTable}`
            WHERE responser_model REGEXP '^M2ePro\/Connector_(Amazon|Buy|Play){1}_Product_*'
        ")->fetchAll();

        $newProcessingRows = array();

        foreach ($oldProcessingRows as $row) {

            $responserParams = $row['responser_params'] ? json_decode($row['responser_params'], true) : array();

            if (empty($responserParams)) {
                $newProcessingRows[] = $row;
                continue;
            }

            if (!isset($responserParams['action_identifier'], $responserParams['listing_log_action'])) {
                $newProcessingRows[] = $row;
                continue;
            }

            $responserParams['lock_identifier'] = $responserParams['action_identifier'];
            $responserParams['action_type'] = $responserParams['lock_identifier'];

            $responserParams['action_type'] == 'stop_and_remove' && $responserParams['action_type'] = 'stop';
            $responserParams['action_type'] == 'delete_and_remove' && $responserParams['action_type'] = 'delete';

            $responserParams['action_type'] = $this->getActionType($responserParams['action_type']);

            unset($responserParams['action_identifier']);

            $responserParams['logs_action'] = $responserParams['listing_log_action'];
            unset($responserParams['listing_log_action']);

            if (empty($responserParams['products']) || !is_array($responserParams['products'])) {
                $row['responser_params'] = json_encode($responserParams);
                $newProcessingRows[] = $row;
                continue;
            }

            $newProducts = array();
            foreach ($responserParams['products'] as $id => $product) {
                $newProducts[$id] = $product['request']['sended_data'];
            }

            $responserParams['products'] = $newProducts;
            $row['responser_params'] = json_encode($responserParams);

            $newProcessingRows[] = $row;
        }

        if (!empty($newProcessingRows)) {
            $connection->insertOnDuplicate($processingTable, $newProcessingRows);
        }
    }

    //####################################

    private function getActionType($actionIdentifier)
    {
        switch (strtolower($actionIdentifier)) {
            case 'list':
                return 1;

            case 'relist':
                return 2;

            case 'revise':
                return 3;

            case 'stop':
                return 4;

            case 'delete':
                return 5;

            default:
                return null;
        }
    }

    //####################################
}