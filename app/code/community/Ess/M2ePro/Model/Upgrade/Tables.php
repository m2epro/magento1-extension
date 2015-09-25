<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_Tables
{
    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    private $installer = NULL;

    private $entities = array();

    //####################################

    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->installer = $installer;
        return $this;
    }

    public function getInstaller()
    {
        if (is_null($this->installer)) {
            throw new Zend_Db_Exception("Installer is not exists.");
        }

        return $this->installer;
    }

    //####################################

    public function getCurrentEntities()
    {
        if (empty($this->entities)) {
            $this->prepareEntities();
        }

        $currentTables = Mage::helper('M2ePro/Module_Database_Structure')->getMySqlTables();
        $preparedTables = array();

        foreach ($currentTables as $table) {
            $preparedTables[$table] = $this->entities[$table];
        }

        return $preparedTables;
    }

    public function getAllHistoryEntities()
    {
        if (empty($this->entities)) {
            $this->prepareEntities();
        }

        return $this->entities;
    }

    // ----------------------------------

    public function getCurrentConfigEntities()
    {
        if (empty($this->entities)) {
            $this->prepareEntities();
        }

        $currentConfigTables = array(
            'm2epro_primary_config',
            'm2epro_config',
            'm2epro_cache_config',
            'm2epro_synchronization_config'
        );
        $preparedCurrentConfigTables = array();

        foreach ($currentConfigTables as $currentConfigTable) {
            $preparedCurrentConfigTables[$currentConfigTable] = $this->entities[$currentConfigTable];
        }

        return $preparedCurrentConfigTables;
    }

    public function getAllHistoryConfigEntities()
    {
        if (empty($this->entities)) {
            $this->prepareEntities();
        }

        return array_merge($this->getCurrentConfigEntities(),
                           array('ess_config' => $this->entities['ess_config']));
    }

    //####################################

    private function prepareEntities()
    {
        $mysqlTables = Mage::helper('M2ePro/Module_Database_Structure')->getMySqlTables();

        $oldTables = array(
            'm2epro_ebay_listing_auto_filter',
            'm2epro_synchronization_run',
            'm2epro_ebay_listing_auto_category',
            'm2epro_ebay_dictionary_policy',
            'm2epro_ebay_template_policy',
            'm2epro_ebay_account_policy',
            'm2epro_play_listing_auto_category_group',
            'ess_config',
            'm2epro_order_repair',
            'm2epro_exceptions_filters',
            'm2epro_attribute_set',
            'm2epro_listing_category',
            'm2epro_template_general',
            'm2epro_translation_custom_suggestion',
            'm2epro_translation_language',
            'm2epro_translation_text',
            'm2epro_amazon_template_general',
            'm2epro_amazon_template_new_product',
            'm2epro_amazon_template_new_product_description',
            'm2epro_amazon_template_new_product_specific',
            'm2epro_ebay_dictionary_shipping_category',
            'm2epro_ebay_message',
            'm2epro_ebay_motor_specific',
            'm2epro_ebay_template_general',
            'm2epro_ebay_template_general_calculated_shipping',
            'm2epro_ebay_template_general_payment',
            'm2epro_ebay_template_general_shipping',
            'm2epro_ebay_template_general_specific',
            'm2epro_buy_template_description',
            'm2epro_buy_template_general',
            'm2epro_play_account',
            'm2epro_play_item',
            'm2epro_play_listing',
            'm2epro_play_listing_other',
            'm2epro_play_listing_product',
            'm2epro_play_listing_product_variation',
            'm2epro_play_listing_product_variation_option',
            'm2epro_play_marketplace',
            'm2epro_play_order',
            'm2epro_play_order_item',
            'm2epro_play_processed_inventory',
            'm2epro_play_template_description',
            'm2epro_play_template_general',
            'm2epro_play_template_selling_format',
            'm2epro_play_template_synchronization',
            'm2epro_amazon_category',
            'm2epro_amazon_category_description',
            'm2epro_amazon_category_specific',
            'm2epro_primary_config',
            'm2epro_cache_config',
            'm2epro_synchronization_config'
        );

        $allTables = array_merge($oldTables, $mysqlTables);
        $allTables = array_values(array_unique($allTables));

        //sort table by length
        do {
            $hasChanges = false;
            for ($i = 0; $i < count($allTables) - 1; $i++) {
                if (strlen($allTables[$i]) < strlen($allTables[$i+1])) {
                    $temp = $allTables[$i];
                    $allTables[$i] = $allTables[$i+1];
                    $allTables[$i+1] = $temp;
                    $hasChanges = true;
                }
            }
        } while ($hasChanges);

        foreach ($allTables as $tableName) {
            $this->entities[$tableName] = $this->getInstaller()->getTable($tableName);
        }
    }

    //####################################
}