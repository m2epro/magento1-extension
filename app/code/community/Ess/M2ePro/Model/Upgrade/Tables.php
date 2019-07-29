<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Tables
{
    const M2E_PRO_TABLE_PREFIX = 'm2epro_';

    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    private $installer = NULL;

    /** @var Varien_Db_Adapter_Pdo_Mysql */
    private $connection = NULL;

    /**
     * @var string[]
     */
    private $entities = array();

    //########################################

    /**
     * @param Ess_M2ePro_Model_Upgrade_MySqlSetup $installer
     * @return $this
     */
    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->installer = $installer;
        $this->connection = $installer->getConnection();
        return $this;
    }

    /**
     * return $this
     */
    public function initialize()
    {
        $oldTables = array(
            'ebay_listing_auto_filter',
            'synchronization_run',
            'ebay_listing_auto_category',
            'ebay_dictionary_policy',
            'ebay_template_policy',
            'ebay_account_policy',
            'play_listing_auto_category_group',
            'ess_config',
            'order_repair',
            'exceptions_filters',
            'attribute_set',
            'listing_category',
            'template_general',
            'translation_custom_suggestion',
            'translation_language',
            'translation_text',
            'amazon_template_general',
            'amazon_template_new_product',
            'amazon_template_new_product_description',
            'amazon_template_new_product_specific',
            'amazon_template_shipping',
            'ebay_dictionary_shipping_category',
            'ebay_message',
            'ebay_motor_specific',
            'ebay_dictionary_motor_specific',
            'ebay_template_general',
            'ebay_template_general_calculated_shipping',
            'ebay_template_general_payment',
            'ebay_template_general_shipping',
            'ebay_template_general_specific',
            'buy_account',
            'buy_item',
            'buy_listing',
            'buy_listing_auto_category_group',
            'buy_listing_other',
            'buy_listing_product',
            'buy_listing_product_variation',
            'buy_listing_product_variation_option',
            'buy_marketplace',
            'buy_order',
            'buy_order_item',
            'buy_template_selling_format',
            'buy_template_synchronization',
            'buy_template_description',
            'buy_template_general',
            'play_account',
            'play_item',
            'play_listing',
            'play_listing_other',
            'play_listing_product',
            'play_listing_product_variation',
            'play_listing_product_variation_option',
            'play_marketplace',
            'play_order',
            'play_order_item',
            'play_processed_inventory',
            'play_template_description',
            'play_template_general',
            'play_template_selling_format',
            'play_template_synchronization',
            'amazon_category',
            'amazon_category_description',
            'amazon_category_specific',
            'amazon_processed_inventory',
            'processing_request',
            'locked_object',
            'buy_dictionary_category',
            'buy_template_new_product',
            'buy_template_new_product_core',
            'buy_template_new_product_attribute',
            'ebay_processing_action_item',
            'amazon_processing_action_item',
            'indexer_listing_product_parent',
            'amazon_dictionary_shipping_override',
            'amazon_template_shipping_override',
            'amazon_template_shipping_override_service',
            'amazon_template_shipping_template',
            'product_change',
            'synchronization_config',
            'ebay_processing_action',
            'amazon_processing_action',
            'connector_pending_requester_single',
            'connector_pending_requester_partial',
            'amazon_processing_action_list_sku',
            'listing_product_synchronization_instruction',
            'walmart_order_action_processing',
            'walmart_template_selling_format_shipping_override_service'
        );

        $currentTables = array();
        foreach (Mage::helper('M2ePro/Module_Database_Structure')->getMySqlTables() as $tableName) {
            $currentTables[] = str_replace('m2epro_', '', $tableName);
        }
        $allTables = array_values(array_unique(array_merge($oldTables, $currentTables)));

        usort($allTables, function ($a,$b) {
            return strlen($b) - strlen($a);
        });

        foreach ($allTables as $table) {
            if ($table == 'ess_config') {
                $this->entities[$table] = $this->getInstaller()->getTable($table);
                continue;
            }

            $this->entities[$table] = $this->getInstaller()->getTable(self::M2E_PRO_TABLE_PREFIX . $table);
        }

        return $this;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Upgrade_MySqlSetup
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function getInstaller()
    {
        if (is_null($this->installer)) {
            throw new Ess_M2ePro_Model_Exception_Setup("Installer does not exist.");
        }

        return $this->installer;
    }

    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function getConnection()
    {
        if (is_null($this->connection)) {
            throw new Ess_M2ePro_Model_Exception_Setup("Connection does not exist.");
        }

        return $this->connection;
    }

    //########################################

    public function getCurrentEntities()
    {
        $result = array();
        $currentTables = Mage::helper('M2ePro/Module_Database_Structure')->getMySqlTables();

        foreach ($currentTables as $table) {
            $result[$table] = $this->entities[$table];
        }

        return $result;
    }

    public function getAllHistoryEntities()
    {
        return $this->entities;
    }

    // ---------------------------------------

    public function getCurrentConfigEntities()
    {
        $result = array();

        $currentConfigTables = array(
            'primary_config',
            'config',
            'cache_config',
            'synchronization_config'
        );

        foreach ($currentConfigTables as $table) {
            $result[$table] = $this->entities[$table];
        }

        return $result;
    }

    public function getAllHistoryConfigEntities()
    {
        return array_merge($this->getCurrentConfigEntities(),
                           array('ess_config' => $this->entities['ess_config']));
    }

    //########################################

    public function isExists($tableName)
    {
        return $this->getInstaller()->tableExists($this->getFullName($tableName));
    }

    public function getFullName($tableName)
    {
        if (!isset($this->entities[$tableName])) {
            throw new Ess_M2ePro_Model_Exception_Setup("Table '{$tableName}' does not exist.");
        }

        return $this->entities[$tableName];
    }

    //########################################
}