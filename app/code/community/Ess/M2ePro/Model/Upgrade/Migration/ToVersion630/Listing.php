<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion630_Listing
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

    /*

        ALTER TABLE `m2epro_amazon_listing`
            DROP COLUMN `condition_note_custom_attribute`;

        ALTER TABLE `m2epro_buy_listing`
            DROP COLUMN `condition_note_custom_attribute`;

        ALTER TABLE `m2epro_play_listing`
            DROP COLUMN `condition_note_custom_attribute`;

     */

    //####################################

    public function process()
    {
        $this->processSku();
        $this->processCondition();
        $this->processConditionNote();
        $this->processBuyShipping();
    }

    //####################################

    private function processSku()
    {
        $this->installer->run(<<<SQL

    UPDATE `m2epro_amazon_listing`
    SET sku_mode = 1
    WHERE sku_mode = 0;

    UPDATE `m2epro_buy_listing`
    SET sku_mode = 1
    WHERE sku_mode = 0;

    UPDATE `m2epro_play_listing`
    SET sku_mode = 1
    WHERE sku_mode = 0;

SQL
        );
    }

    private function processCondition()
    {
        $this->installer->run(<<<SQL

    UPDATE `m2epro_amazon_listing`
    SET condition_mode = 1,
        condition_value = 'New'
    WHERE condition_mode = 0;

    UPDATE `m2epro_buy_listing`
    SET condition_mode = 1,
        condition_value = 1
    WHERE condition_mode = 0;

    UPDATE `m2epro_play_listing`
    SET condition_mode = 1,
        condition_value = 'New'
    WHERE condition_mode = 0;

SQL
        );
    }

    private function processConditionNote()
    {
        $connection = $this->getInstaller()->getConnection();

        $tempTable = $this->getInstaller()->getTable('m2epro_amazon_listing');

        if ($connection->tableColumnExists($tempTable, 'condition_note_custom_attribute')) {
            $this->getInstaller()->run(<<<SQL

    UPDATE `m2epro_amazon_listing`
    SET    `condition_note_value` = CONCAT('#', `condition_note_custom_attribute`, '#'),
           `condition_note_mode` = 1
    WHERE  `condition_note_mode` = 2;

    UPDATE `m2epro_buy_listing`
    SET    `condition_note_value` = CONCAT('#', `condition_note_custom_attribute`, '#'),
           `condition_note_mode` = 1
    WHERE  `condition_note_mode` = 2;

    UPDATE `m2epro_play_listing`
    SET    `condition_note_value` = CONCAT('#', `condition_note_custom_attribute`, '#'),
           `condition_note_mode` = 1
    WHERE  `condition_note_mode` = 2;

SQL
            );
        }

        $this->getInstaller()->run(<<<SQL

    UPDATE `m2epro_amazon_listing`
    SET condition_note_mode = 3
    WHERE condition_note_mode = 0;

    UPDATE `m2epro_buy_listing`
    SET condition_note_mode = 3
    WHERE condition_note_mode = 0;

    UPDATE `m2epro_play_listing`
    SET condition_note_mode = 3
    WHERE condition_note_mode = 0;

SQL
        );

        $tempTable = $this->getInstaller()->getTable('m2epro_amazon_listing');

        if ($connection->tableColumnExists($tempTable, 'condition_note_custom_attribute') !== false) {
            $connection->dropColumn($tempTable, 'condition_note_custom_attribute');
        }

        $tempTable = $this->getInstaller()->getTable('m2epro_buy_listing');

        if ($connection->tableColumnExists($tempTable, 'condition_note_custom_attribute') !== false) {
            $connection->dropColumn($tempTable, 'condition_note_custom_attribute');
        }

        $tempTable = $this->getInstaller()->getTable('m2epro_play_listing');

        if ($connection->tableColumnExists($tempTable, 'condition_note_custom_attribute') !== false) {
            $connection->dropColumn($tempTable, 'condition_note_custom_attribute');
        }
    }

    private function processBuyShipping()
    {
        $this->installer->run(<<<SQL

UPDATE `m2epro_buy_listing`
SET shipping_standard_mode = 3
WHERE shipping_standard_mode = 0;

UPDATE `m2epro_buy_listing`
SET shipping_expedited_mode = 3
WHERE shipping_expedited_mode = 0;

UPDATE `m2epro_buy_listing`
SET shipping_one_day_mode = 3
WHERE shipping_one_day_mode = 0;

UPDATE `m2epro_buy_listing`
SET shipping_two_day_mode = 3
WHERE shipping_two_day_mode = 0;

SQL
        );
    }

    //####################################
}