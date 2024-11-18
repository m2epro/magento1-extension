<?php

class Ess_M2ePro_Sql_Update_y24_m10_AddAmazonProductTypes
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    const WIZARD_STATUS_ACTIVE = 1;
    const WIZARD_STATUS_SKIPPED = 3;

    public function execute()
    {
        $this->updateDictionaryMarketplaceTable();

        $this->addProductTypeToListingProductTable();

        $this->createDictionaryProductTypeTable();
        $this->createTemplateProductTypeTable();
        $this->createTemplateProductTypeAttributeMappingTable();
        $this->createTemplateProductTypeValidationTable();

        $this->addAutoActionFields();

        $this->addMigrationToProductTypeWizard();

        $this->insertProductIdentifiersConfigs();

        $this->removeUnusedData();
        $this->removeUnusedTablesAndColumns();
        $this->removeImagesTagFromScheduledActions();
    }

    /**
     * @return void
     */
    private function updateDictionaryMarketplaceTable()
    {
        $this->_installer
            ->getTableModifier('amazon_dictionary_marketplace')
            ->dropColumn('client_details_last_update_date');

        $this->_installer
            ->getTableModifier('amazon_dictionary_marketplace')
            ->dropColumn('product_data');

        $this->_installer
            ->getTableModifier('amazon_dictionary_marketplace')
            ->dropColumn('server_details_last_update_date');

        $this->_installer
            ->getTableModifier('amazon_dictionary_marketplace')
            ->addColumn(
                Ess_M2ePro_Model_Resource_Amazon_Dictionary_Marketplace::COLUMN_PRODUCT_TYPES,
                'LONGTEXT',
                'NULL'
            );
    }

    // ---------------------------------------

    /**
     * @return void
     */
    private function addProductTypeToListingProductTable()
    {
        $this->_installer->getTableModifier('amazon_listing_product')
                         ->addColumn(
                             Ess_M2ePro_Model_Resource_Amazon_Listing_Product::COLUMN_TEMPLATE_PRODUCT_TYPE_ID,
                             'INT(11) UNSIGNED',
                             'NULL',
                             'template_description_id',
                             true
                         );
    }

    // ---------------------------------------

    /**
     * @return void
     */
    private function createDictionaryProductTypeTable()
    {
        $this->_installer->run(
            <<<SQL

    DROP TABLE IF EXISTS `{$this->_installer->getFullTableName('amazon_dictionary_product_type')}`;
    CREATE TABLE `{$this->_installer->getFullTableName('amazon_dictionary_product_type')}` (
      `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `marketplace_id` INT(11) UNSIGNED NOT NULL,
      `nick` VARCHAR(255) NOT NULL,
      `title` VARCHAR(255) NOT NULL,
      `scheme` LONGTEXT NOT NULL,
      `variation_themes` LONGTEXT DEFAULT NULL,
      `attributes_groups` LONGTEXT DEFAULT NULL,
      `client_details_last_update_date` DATETIME DEFAULT NULL,
      `server_details_last_update_date` DATETIME DEFAULT NULL,
      `invalid` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
      PRIMARY KEY (`id`),
      INDEX `marketplace_id_nick` (`marketplace_id`, `nick`)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

SQL
        );
    }

    /**
     * @return void
     */
    private function createTemplateProductTypeTable()
    {
        $this->_installer->run(
            <<<SQL

    DROP TABLE IF EXISTS `{$this->_installer->getFullTableName('amazon_template_product_type')}`;
    CREATE TABLE `{$this->_installer->getFullTableName('amazon_template_product_type')}` (
      `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `title` VARCHAR(255) DEFAULT NULL,
      `view_mode` SMALLINT UNSIGNED NOT NULL,
      `dictionary_product_type_id` INT(11) UNSIGNED NOT NULL,
      `settings` LONGTEXT NOT NULL,
      `update_date` DATETIME DEFAULT NULL,
      `create_date` DATETIME DEFAULT NULL,
      PRIMARY KEY (`id`),
      INDEX `dictionary_product_type_id` (`dictionary_product_type_id`)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

SQL
        );
    }

    /**
     * @return void
     */
    private function createTemplateProductTypeAttributeMappingTable()
    {
        $this->_installer->run(
            <<<SQL

        DROP TABLE IF EXISTS `{$this->_installer->getFullTableName('amazon_product_type_validation')}`;
        CREATE TABLE `{$this->_installer->getFullTableName('amazon_product_type_validation')}` (
         `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
         `listing_product_id` INT(11) UNSIGNED NOT NULL,
         `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
         `error_messages` TEXT DEFAULT NULL,
         `create_date` DATETIME DEFAULT NULL,
         `update_date` DATETIME DEFAULT NULL,
         PRIMARY KEY (`id`),
        INDEX `listing_product_id` (`listing_product_id`)
        )
        ENGINE = INNODB
        CHARACTER SET utf8
        COLLATE utf8_general_ci;

SQL
        );
    }

    /**
     * @return void
     */
    private function createTemplateProductTypeValidationTable()
    {
        $this->_installer->run(
            <<<SQL

        DROP TABLE IF EXISTS `{$this->_installer->getFullTableName('amazon_product_type_attribute_mapping')}`;
        CREATE TABLE `{$this->_installer->getFullTableName('amazon_product_type_attribute_mapping')}` (
          `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `product_type_attribute_code` VARCHAR(255) NOT NULL,
          `product_type_attribute_name` VARCHAR(255) NOT NULL,
          `magento_attribute_code` VARCHAR(255) NOT NULL DEFAULT '',
          PRIMARY KEY (`id`)
        )
        ENGINE = INNODB
        CHARACTER SET utf8
        COLLATE utf8_general_ci;
        
SQL
        );
    }

    // ---------------------------------------

    /**
     * @return void
     */
    private function addAutoActionFields()
    {
        $this->_installer->getTableModifier('amazon_listing')
                         ->addColumn(
                             'auto_global_adding_product_type_template_id',
                             'INT(11) UNSIGNED',
                             'NULL',
                             'auto_global_adding_description_template_id',
                             true,
                             false
                         )
                         ->addColumn(
                             'auto_website_adding_product_type_template_id',
                             'INT(11) UNSIGNED',
                             'NULL',
                             'auto_website_adding_description_template_id',
                             true,
                             false
                         )->commit();

        $this->_installer->getTableModifier('amazon_listing_auto_category_group')
                         ->addColumn(
                             'adding_product_type_template_id',
                             'INT(11) UNSIGNED',
                             'NULL',
                             'adding_description_template_id',
                             true,
                             false
                         )->commit();
    }

    // ---------------------------------------

    /**
     * @return void
     */
    private function addMigrationToProductTypeWizard()
    {
        $query = $this->_installer->getConnection()
                                  ->select()
                                  ->from($this->_installer->getFullTableName('wizard'))
                                  ->where('nick = ?', 'amazonMigrationToProductTypes')
                                  ->query();

        $row = $query->fetch();
        if ($row) {
            return;
        }

        $query = $this->_installer->getConnection()
                                  ->select()
                                  ->from($this->_installer->getFullTableName('marketplace'))
                                  ->where('component_mode = "amazon" AND status = 1')
                                  ->query();

        $row = $query->fetch();
        $status = $row ? self::WIZARD_STATUS_ACTIVE : self::WIZARD_STATUS_SKIPPED;

        $this->_installer->getConnection()->insert(
            $this->_installer->getFullTableName('wizard'),
            array(
                'nick' => 'amazonMigrationToProductTypes',
                'view' => 'amazon',
                'status' => $status,
                'step' => null,
                'type' => 1,
                'priority' => 6,
            )
        );
    }

    // ---------------------------------------

    /**
     * @return void
     */
    private function insertProductIdentifiersConfigs()
    {
        if (
            !$this->_installer->tableExists(
                $this->_installer->getFullTableName('amazon_template_description')
            )
        ) {
            return;
        }

        $configsFromTemplate = array(
            'worldwide_id_mode' => 0,
            'worldwide_id_custom_attribute' => null,
        );

        $templateDescriptionId = $this->getIdOfMostPopularTemplateDescription();
        $descriptionTableModifier = $this->_installer->getTableModifier('amazon_template_description');
        if (
            $templateDescriptionId !== null
            && $descriptionTableModifier->isColumnExists('worldwide_id_mode')
            && $descriptionTableModifier->isColumnExists('worldwide_id_custom_attribute')
        ) {
            $query = $this->_installer->getConnection()
                                      ->select()
                                      ->from(
                                          $this->_installer->getFullTableName('amazon_template_description'),
                                          array(
                                              'template_description_id',
                                              'worldwide_id_mode',
                                              'worldwide_id_custom_attribute'
                                          )
                                      )
                                      ->where('template_description_id = ?', $templateDescriptionId)
                                      ->query();

            $row = $query->fetch();

            if (isset($row['worldwide_id_mode'])) {
                $configsFromTemplate['worldwide_id_mode'] = $row['worldwide_id_mode'];
            }

            if (isset($row['worldwide_id_custom_attribute'])) {
                $configsFromTemplate['worldwide_id_custom_attribute'] = $row['worldwide_id_custom_attribute'];
            }
        }

        $configGroup = '/amazon/configuration/';

        $configModifier = $this->_installer->getMainConfigModifier();
        $configModifier->insert(
            $configGroup,
            'worldwide_id_mode',
            $configsFromTemplate['worldwide_id_mode']
        );
        $configModifier->insert(
            $configGroup,
            'worldwide_id_custom_attribute',
            $configsFromTemplate['worldwide_id_custom_attribute']
        );
        $configModifier->insert(
            $configGroup,
            'general_id_mode',
            0
        );
        $configModifier->insert(
            $configGroup,
            'general_id_custom_attribute'
        );
    }

    /**
     * @return int|null
     */
    private function getIdOfMostPopularTemplateDescription()
    {
        $query = $this->_installer->getConnection()
                                  ->select()
                                  ->from(
                                      $this->_installer->getFullTableName('amazon_listing_product'),
                                      array('template_description_id', 'COUNT(*) AS count')
                                  )
                                  ->group('template_description_id')
                                  ->order('count DESC')
                                  ->query();

        /** @var array $row */
        $row = $query->fetch();

        if (!isset($row['template_description_id'])) {
            return null;
        }

        return (int)$row['template_description_id'];
    }

    // ---------------------------------------

    /**
     * @return void
     */
    private function removeUnusedData()
    {
        $registryTable = $this->_installer->getFullTableName('registry');

        $this->_installer->getConnection()->delete(
            $registryTable,
            array('`key` = ?' => '/amazon/category/recent/')
        );
    }

    /**
     * @return void
     */
    private function removeUnusedTablesAndColumns()
    {
        $this->_installer->getTableModifier('amazon_listing')
                         ->dropColumn('auto_global_adding_description_template_id', true, false)
                         ->dropColumn('auto_website_adding_description_template_id', true, false)
                         ->dropColumn('image_main_mode', true, false)
                         ->dropColumn('image_main_attribute', true, false)
                         ->dropColumn('gallery_images_mode', true, false)
                         ->dropColumn('gallery_images_limit', true, false)
                         ->dropColumn('gallery_images_attribute', true, false)
                         ->commit();

        $this->_installer->getTableModifier('amazon_marketplace')
                         ->dropColumn('is_new_asin_available');

        $this->_installer->getTableModifier('amazon_listing_auto_category_group')
                         ->dropColumn('adding_description_template_id');

        $this->_installer->getTableModifier('amazon_listing_product')
                         ->dropColumn('template_description_id')
                         ->dropColumn('online_images_data');

        $this->_installer->getTableModifier('amazon_template_synchronization')
                         ->dropColumn('revise_update_images');

        $this->_installer->getMainConfigModifier()
                         ->delete(
                             '/amazon/listing/product/action/revise_images/',
                             'min_allowed_wait_interval'
                         );

        $dropTables = array(
            'amazon_template_description',
            'amazon_template_description_definition',
            'amazon_dictionary_category',
            'amazon_dictionary_specific',
            'amazon_template_description_specific',
            'amazon_dictionary_category_product_data',
        );

        $connection = $this->_installer->getConnection();
        foreach ($dropTables as $tableName) {
            $connection->dropTable(
                $this->_installer->getFullTableName($tableName)
            );
        }
    }

    private function removeImagesTagFromScheduledActions()
    {
        $scheduledAction = $this->_installer->getFullTableName('listing_product_scheduled_action');

        $stmt = $this->_installer->getConnection()->select()
            ->from(
                $scheduledAction,
                array('id', 'tag')
            )
            ->where('component = ?', 'amazon')
            ->where('tag LIKE ?', '%images%')
            ->query();

        while ($row = $stmt->fetch()) {
            $tags = array_filter(
                explode('/', $row['tag']),
                function ($tag) {
                    return !empty($tag) && $tag !== 'images';
                }
            );

            if (!empty($tags)) {
                $tags = '/' . implode('/', $tags) . '/';
                $this->_installer->getConnection()->update(
                    $scheduledAction,
                    array('tag' => $tags),
                    array('id = ?' => (int)$row['id'])
                );
            } else {
                $this->_installer->getConnection()->delete(
                    $scheduledAction,
                    array('id = ?' => (int)$row['id'])
                );
            }
        }
    }
}