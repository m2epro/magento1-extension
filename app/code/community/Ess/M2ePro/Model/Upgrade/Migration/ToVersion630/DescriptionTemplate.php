<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion630_DescriptionTemplate
{
    const BACKUP_PREFIX = 'bv630_';

    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    private $installer = NULL;

    private $forceAllSteps = false;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Upgrade_MySqlSetup
     */
    public function getInstaller()
    {
        return $this->installer;
    }

    /**
     * @param Ess_M2ePro_Model_Upgrade_MySqlSetup $installer
     */
    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->installer = $installer;
    }

    // ---------------------------------------

    public function setForceAllSteps($value = true)
    {
        $this->forceAllSteps = $value;
    }

    //########################################

    public function getBackupTableName($originalTableName)
    {
        $tableName = str_replace('m2epro_', '', $originalTableName);
        $tableName = 'm2epro_' . self::BACKUP_PREFIX . $tableName;

        return $this->installer->getTable($tableName);
    }

    //########################################

    /*

        ALTER TABLE m2epro_amazon_listing_product
            CHANGE COLUMN template_new_product_id template_description_id int(11) UNSIGNED DEFAULT NULL,
            DROP INDEX template_new_product_id,
            ADD INDEX template_description_id (template_description_id);

        RENAME TABLE m2epro_amazon_template_new_product TO m2epro_amazon_template_description;
        RENAME TABLE m2epro_amazon_template_new_product_description TO m2epro_amazon_template_description_definition;
        RENAME TABLE m2epro_amazon_template_new_product_specific TO m2epro_amazon_template_description_specific;

        CREATE TABLE m2epro_template_description (
            id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            component_mode varchar(10) DEFAULT NULL,
            update_date datetime DEFAULT NULL,
            create_date datetime DEFAULT NULL,
            PRIMARY KEY (id),
            INDEX component_mode (component_mode),
            INDEX title (title)
        )
        ENGINE = INNODB
        CHARACTER SET utf8
        COLLATE utf8_general_ci;

        ALTER TABLE m2epro_ebay_template_description
            DROP COLUMN title,
            DROP COLUMN create_date,
            DROP COLUMN update_date,
            CHANGE COLUMN id template_description_id int(11) UNSIGNED NOT NULL,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (template_description_id);

        ALTER TABLE m2epro_amazon_template_description
            DROP COLUMN title,
            DROP COLUMN create_date,
            DROP COLUMN update_date,
            DROP COLUMN node_title,
            DROP COLUMN xsd_hash,
            DROP COLUMN identifiers,DEFAULT NULL
            CHANGE COLUMN id template_description_id int(11) UNSIGNED NOT NULL,
            CHANGE COLUMN category_path category_path VARCHAR(255) DEFAULT NULL,
            ADD COLUMN is_new_asin_accepted TINYINT(2) UNSIGNED DEFAULT 0 AFTER marketplace_id,
            ADD COLUMN product_data_nick VARCHAR(255) DEFAULT NULL AFTER is_new_asin_accepted,
            ADD COLUMN browsenode_id DECIMAL(20, 0) UNSIGNED DEFAULT NULL AFTER category_path,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (template_description_id),
            ADD INDEX is_new_asin_accepted (is_new_asin_accepted),
            ADD INDEX product_data_nick (product_data_nick),
            ADD INDEX browsenode_id (browsenode_id);

        ALTER TABLE m2epro_amazon_template_description_definition
            CHANGE COLUMN template_new_product_id template_description_id int(11) UNSIGNED NOT NULL,
            CHANGE COLUMN brand_template brand_custom_attribute VARCHAR(255) DEFAULT NULL,
            CHANGE COLUMN manufacturer_template manufacturer_custom_attribute VARCHAR(255) DEFAULT NULL,
            CHANGE COLUMN target_audience_custom_value target_audience TEXT NOT NULL,
            ADD COLUMN brand_custom_value VARCHAR(255) DEFAULT NULL AFTER brand_mode,
            ADD COLUMN manufacturer_custom_value VARCHAR(255) DEFAULT NULL AFTER manufacturer_mode,

            ADD COLUMN item_dimensions_volume_mode TINYINT(2) UNSIGNED DEFAULT 0
                AFTER manufacturer_part_number_custom_attribute,
            ADD COLUMN item_dimensions_volume_length_custom_value VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_volume_mode,
            ADD COLUMN item_dimensions_volume_width_custom_value VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_volume_length_custom_value,
            ADD COLUMN item_dimensions_volume_height_custom_value VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_volume_width_custom_value,
            ADD COLUMN item_dimensions_volume_length_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_volume_height_custom_value,
            ADD COLUMN item_dimensions_volume_width_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_volume_length_custom_attribute,
            ADD COLUMN item_dimensions_volume_height_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_volume_width_custom_attribute,
            ADD COLUMN item_dimensions_volume_unit_of_measure_mode TINYINT(2) UNSIGNED DEFAULT 0
                AFTER item_dimensions_volume_height_custom_attribute,
            ADD COLUMN item_dimensions_volume_unit_of_measure_custom_value VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_volume_unit_of_measure_mode,
            ADD COLUMN item_dimensions_volume_unit_of_measure_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_volume_unit_of_measure_custom_value,
            ADD COLUMN item_dimensions_weight_mode TINYINT(2) UNSIGNED DEFAULT 0
                AFTER item_dimensions_volume_unit_of_measure_custom_attribute,
            ADD COLUMN item_dimensions_weight_custom_value DECIMAL(10, 2) UNSIGNED DEFAULT NULL
                AFTER item_dimensions_weight_mode,
            ADD COLUMN item_dimensions_weight_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_weight_custom_value,
            ADD COLUMN item_dimensions_weight_unit_of_measure_mode TINYINT(2) UNSIGNED DEFAULT 0
                AFTER item_dimensions_weight_custom_attribute,
            ADD COLUMN item_dimensions_weight_unit_of_measure_custom_value VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_weight_unit_of_measure_mode,
            ADD COLUMN item_dimensions_weight_unit_of_measure_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER item_dimensions_weight_unit_of_measure_custom_value,
            ADD COLUMN package_dimensions_volume_mode TINYINT(2) UNSIGNED DEFAULT 0
                AFTER item_dimensions_weight_unit_of_measure_custom_attribute,

            ADD COLUMN package_dimensions_volume_length_custom_value VARCHAR(255) DEFAULT NULL
                AFTER package_dimensions_volume_mode,
            ADD COLUMN package_dimensions_volume_width_custom_value VARCHAR(255) DEFAULT NULL
                AFTER package_dimensions_volume_length_custom_value,
            ADD COLUMN package_dimensions_volume_height_custom_value VARCHAR(255) DEFAULT NULL
                AFTER package_dimensions_volume_width_custom_value,
            ADD COLUMN package_dimensions_volume_length_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER package_dimensions_volume_height_custom_value,
            ADD COLUMN package_dimensions_volume_width_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER package_dimensions_volume_length_custom_attribute,
            ADD COLUMN package_dimensions_volume_height_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER package_dimensions_volume_width_custom_attribute,
            ADD COLUMN package_dimensions_volume_unit_of_measure_mode TINYINT(2) UNSIGNED DEFAULT 0
                AFTER package_dimensions_volume_height_custom_attribute,
            ADD COLUMN package_dimensions_volume_unit_of_measure_custom_value VARCHAR(255) DEFAULT NULL
                AFTER package_dimensions_volume_unit_of_measure_mode,
            ADD COLUMN package_dimensions_volume_unit_of_measure_custom_attribute VARCHAR(255) DEFAULT NULL
                AFTER package_dimensions_volume_unit_of_measure_custom_value,

            DROP COLUMN target_audience_custom_attribute,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (template_description_id);

        ALTER TABLE m2epro_amazon_template_description_specific
            CHANGE COLUMN template_new_product_id template_description_id int(11) UNSIGNED NOT NULL,
            DROP INDEX template_new_product_id,
            ADD INDEX template_description_id (template_description_id);

    */

    //########################################

    public function process()
    {
        if ($this->isNeedToSkip()) {
            return;
        }

        $this->saveWizardNecessaryData();
        $this->backupTables();

        $this->processListingProduct();

        $this->processGeneralTemplates();
        $this->processEbayTemplates();
        $this->processAmazonTemplates();
    }

    //########################################

    private function isNeedToSkip()
    {
        if ($this->forceAllSteps) {
            return false;
        }

        $connection = $this->installer->getConnection();

        $oldSpecific = $this->installer->getTable('m2epro_amazon_template_new_product_specific');
        $newSpecific = $this->installer->getTable('m2epro_amazon_template_description_specific');

        if (!$this->installer->tableExists($oldSpecific) &&
            $this->installer->tableExists($newSpecific) &&
            $connection->tableColumnExists($newSpecific, 'template_description_id') !== false) {
            return true;
        }

        return false;
    }

    //########################################

    private function saveWizardNecessaryData()
    {
        $marketplace = $this->installer->getTable('m2epro_marketplace');
        $templateNewProduct = $this->installer->getTable('m2epro_amazon_template_new_product');

        if (!$this->installer->tableExists($templateNewProduct)) {
            return;
        }

        $connection = $this->installer->getConnection();

        $result = $connection->query(<<<SQL

        SELECT `main_table`.`title`,
               `main_table`.`category_path`,
               `second_table`.`title` AS `marketplace_title`
        FROM `{$templateNewProduct}` as `main_table`
        INNER JOIN `{$marketplace}` AS `second_table`
        ON (`main_table`.`marketplace_id` = `second_table`.`id`);

SQL
);
        $registryKey = '/wizard/new_amazon_description_templates/';

        $dataForInsert = array();
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $dataForInsert[] = $row;
        }

        $dateForInsert = $connection->quote(date('Y-m-d H:i:s', gmdate('U')));
        $dataForInsert = $connection->quote(json_encode($dataForInsert));

        $this->installer->run(<<<SQL

        INSERT INTO `m2epro_registry` (`key`, `value`, update_date, create_date)
        VALUES ('{$registryKey}', {$dataForInsert}, {$dateForInsert}, {$dateForInsert});

SQL
);
    }

    private function backupTables()
    {
        $connection = $this->installer->getConnection();

        $originalTable = $this->installer->getTable('m2epro_ebay_template_description');
        $backupTable   = $this->getBackupTableName('m2epro_ebay_template_description');

        if ($this->installer->tableExists($originalTable) && !$this->installer->tableExists($backupTable)) {
            $connection->query("RENAME TABLE {$originalTable} TO {$backupTable}");
        }

        $originalTable = $this->installer->getTable('m2epro_amazon_template_new_product');
        $backupTable   = $this->getBackupTableName('m2epro_amazon_template_new_product');

        if ($this->installer->tableExists($originalTable) && !$this->installer->tableExists($backupTable)) {
            $connection->query("RENAME TABLE {$originalTable} TO {$backupTable}");
        }

        $originalTable = $this->installer->getTable('m2epro_amazon_template_new_product_description');
        $backupTable   = $this->getBackupTableName('m2epro_amazon_template_new_product_description');

        if ($this->installer->tableExists($originalTable) && !$this->installer->tableExists($backupTable)) {
            $connection->query("RENAME TABLE {$originalTable} TO {$backupTable}");
        }

        $originalTable = $this->installer->getTable('m2epro_amazon_template_new_product_specific');
        $backupTable   = $this->getBackupTableName('m2epro_amazon_template_new_product_specific');

        if ($this->installer->tableExists($originalTable) && !$this->installer->tableExists($backupTable)) {
            $connection->query("RENAME TABLE {$originalTable} TO {$backupTable}");
        }
    }

    //########################################

    private function processListingProduct()
    {
        $connection = $this->installer->getConnection();

        $tempTable = $this->installer->getTable('m2epro_amazon_listing_product');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (isset($tempTableIndexList[strtoupper('template_new_product_id')])) {
            $connection->dropKey($tempTable, 'template_new_product_id');
        }

        if ($connection->tableColumnExists($tempTable, 'template_new_product_id') !== false) {

            $this->installer->run(<<<SQL

    UPDATE `m2epro_amazon_listing_product`
    SET template_new_product_id = NULL;

SQL
            );
        }

        if ($connection->tableColumnExists($tempTable, 'template_new_product_id') !== false &&
            $connection->tableColumnExists($tempTable, 'template_description_id') === false) {
            $connection->changeColumn(
                $tempTable, 'template_new_product_id', 'template_description_id',
                'int(11) UNSIGNED DEFAULT NULL'
            );
        }

        if (!isset($tempTableIndexList[strtoupper('template_description_id')])) {
            $connection->addKey($tempTable, 'template_description_id', 'template_description_id');
        }
    }

    //########################################

    private function processGeneralTemplates()
    {
        $this->installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS `m2epro_template_description` (
    id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    title varchar(255) NOT NULL,
    component_mode varchar(10) DEFAULT NULL,
    update_date datetime DEFAULT NULL,
    create_date datetime DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX component_mode (component_mode),
    INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
        );
    }

    private function processEbayTemplates()
    {
        $this->installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS `m2epro_ebay_template_description` (
  template_description_id INT(11) UNSIGNED NOT NULL,
  is_custom_template TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  title_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  title_template VARCHAR(255) NOT NULL,
  subtitle_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  subtitle_template VARCHAR(255) NOT NULL,
  description_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  description_template LONGTEXT NOT NULL,
  condition_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_value INT(11) UNSIGNED NOT NULL DEFAULT 0,
  condition_attribute VARCHAR(255) NOT NULL,
  condition_note_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_note_template TEXT NOT NULL,
  product_details TEXT DEFAULT NULL,
  cut_long_titles TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  hit_counter VARCHAR(255) NOT NULL,
  editor_type TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  enhancement VARCHAR(255) NOT NULL,
  gallery_type TINYINT(2) UNSIGNED NOT NULL DEFAULT 4,
  image_main_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  image_main_attribute VARCHAR(255) NOT NULL,
  gallery_images_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  gallery_images_limit TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  gallery_images_attribute VARCHAR(255) NOT NULL,
  default_image_url VARCHAR(255) DEFAULT NULL,
  variation_configurable_images VARCHAR(255) NOT NULL,
  use_supersize_images TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  watermark_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  watermark_image LONGBLOB DEFAULT NULL,
  watermark_settings TEXT DEFAULT NULL,
  PRIMARY KEY (template_description_id),
  INDEX is_custom_template (is_custom_template)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
        );

        $descriptionTable     = $this->installer->getTable('m2epro_template_description');
        $ebayDescriptionTable = $this->installer->getTable('m2epro_ebay_template_description');
        $backupTable          = $this->getBackupTableName('m2epro_ebay_template_description');

        if (!$this->installer->tableExists($backupTable) ||
            !$this->installer->tableExists($ebayDescriptionTable) ||
            !$this->installer->tableExists($descriptionTable)) {

            return;
        }

        $tempQuery = "SELECT * FROM `{$descriptionTable}` WHERE `component_mode` = 'ebay'";
        $tempRow = $this->installer->getConnection()
                                   ->query($tempQuery)
                                   ->fetch();

        if ($tempRow !== false) {
            return;
        }

        $this->installer->run(<<<SQL

INSERT INTO `m2epro_template_description`
SELECT
    `id`,
    `title`,
    'ebay',
    `create_date`,
    `update_date`
FROM {$backupTable};

INSERT INTO `m2epro_ebay_template_description`
SELECT
    `id`,
    `is_custom_template`,
    `title_mode`,
    `title_template`,
    `subtitle_mode`,
    `subtitle_template`,
    `description_mode`,
    `description_template`,
    `condition_mode`,
    `condition_value`,
    `condition_attribute`,
    `condition_note_mode`,
    `condition_note_template`,
    `product_details`,
    `cut_long_titles`,
    `hit_counter`,
    `editor_type`,
    `enhancement`,
    `gallery_type`,
    `image_main_mode`,
    `image_main_attribute`,
    `gallery_images_mode`,
    `gallery_images_limit`,
    `gallery_images_attribute`,
    `default_image_url`,
    `variation_configurable_images`,
    `use_supersize_images`,
    `watermark_mode`,
    `watermark_image`,
    `watermark_settings`
FROM {$backupTable};

SQL
        );
    }

    private function processAmazonTemplates()
    {
        $this->installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS `m2epro_amazon_template_description` (
  template_description_id INT(11) UNSIGNED NOT NULL,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  is_new_asin_accepted TINYINT(2) UNSIGNED DEFAULT 0,
  product_data_nick VARCHAR(255) DEFAULT NULL,
  category_path VARCHAR(255) DEFAULT NULL,
  browsenode_id DECIMAL(20, 0) UNSIGNED DEFAULT NULL,
  registered_parameter VARCHAR(25) DEFAULT NULL,
  worldwide_id_mode TINYINT(2) UNSIGNED DEFAULT 0,
  worldwide_id_custom_attribute VARCHAR(255) DEFAULT NULL,
  item_package_quantity_mode TINYINT(2) UNSIGNED DEFAULT 0,
  item_package_quantity_custom_value VARCHAR(255) DEFAULT NULL,
  item_package_quantity_custom_attribute VARCHAR(255) DEFAULT NULL,
  number_of_items_mode TINYINT(2) UNSIGNED DEFAULT 0,
  number_of_items_custom_value VARCHAR(255) DEFAULT NULL,
  number_of_items_custom_attribute VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (template_description_id),
  INDEX marketplace_id (marketplace_id),
  INDEX is_new_asin_accepted (is_new_asin_accepted),
  INDEX product_data_nick (product_data_nick),
  INDEX browsenode_id (browsenode_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_amazon_template_description_definition` (
  template_description_id INT(11) UNSIGNED NOT NULL,
  title_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  title_template VARCHAR(255) NOT NULL,
  brand_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  brand_custom_value VARCHAR(255) DEFAULT NULL,
  brand_custom_attribute VARCHAR(255) DEFAULT NULL,
  manufacturer_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  manufacturer_custom_value VARCHAR(255) DEFAULT NULL,
  manufacturer_custom_attribute VARCHAR(255) DEFAULT NULL,
  manufacturer_part_number_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  manufacturer_part_number_custom_value VARCHAR(255) NOT NULL,
  manufacturer_part_number_custom_attribute VARCHAR(255) NOT NULL,
  item_dimensions_volume_mode TINYINT(2) UNSIGNED DEFAULT 0,
  item_dimensions_volume_length_custom_value VARCHAR(255) DEFAULT NULL,
  item_dimensions_volume_width_custom_value VARCHAR(255) DEFAULT NULL,
  item_dimensions_volume_height_custom_value VARCHAR(255) DEFAULT NULL,
  item_dimensions_volume_length_custom_attribute VARCHAR(255) DEFAULT NULL,
  item_dimensions_volume_width_custom_attribute VARCHAR(255) DEFAULT NULL,
  item_dimensions_volume_height_custom_attribute VARCHAR(255) DEFAULT NULL,
  item_dimensions_volume_unit_of_measure_mode TINYINT(2) UNSIGNED DEFAULT 0,
  item_dimensions_volume_unit_of_measure_custom_value VARCHAR(255) DEFAULT NULL,
  item_dimensions_volume_unit_of_measure_custom_attribute VARCHAR(255) DEFAULT NULL,
  item_dimensions_weight_mode TINYINT(2) UNSIGNED DEFAULT 0,
  item_dimensions_weight_custom_value DECIMAL(10, 2) UNSIGNED DEFAULT NULL,
  item_dimensions_weight_custom_attribute VARCHAR(255) DEFAULT NULL,
  item_dimensions_weight_unit_of_measure_mode TINYINT(2) UNSIGNED DEFAULT 0,
  item_dimensions_weight_unit_of_measure_custom_value VARCHAR(255) DEFAULT NULL,
  item_dimensions_weight_unit_of_measure_custom_attribute VARCHAR(255) DEFAULT NULL,
  package_dimensions_volume_mode TINYINT(2) UNSIGNED DEFAULT 0,
  package_dimensions_volume_length_custom_value VARCHAR(255) DEFAULT NULL,
  package_dimensions_volume_width_custom_value VARCHAR(255) DEFAULT NULL,
  package_dimensions_volume_height_custom_value VARCHAR(255) DEFAULT NULL,
  package_dimensions_volume_length_custom_attribute VARCHAR(255) DEFAULT NULL,
  package_dimensions_volume_width_custom_attribute VARCHAR(255) DEFAULT NULL,
  package_dimensions_volume_height_custom_attribute VARCHAR(255) DEFAULT NULL,
  package_dimensions_volume_unit_of_measure_mode TINYINT(2) UNSIGNED DEFAULT 0,
  package_dimensions_volume_unit_of_measure_custom_value VARCHAR(255) DEFAULT NULL,
  package_dimensions_volume_unit_of_measure_custom_attribute VARCHAR(255) DEFAULT NULL,
  shipping_weight_mode TINYINT(2) UNSIGNED DEFAULT 0,
  shipping_weight_custom_value DECIMAL(10, 2) UNSIGNED DEFAULT NULL,
  shipping_weight_custom_attribute VARCHAR(255) DEFAULT NULL,
  shipping_weight_unit_of_measure_mode TINYINT(2) UNSIGNED DEFAULT 1,
  shipping_weight_unit_of_measure_custom_value VARCHAR(255) DEFAULT NULL,
  shipping_weight_unit_of_measure_custom_attribute VARCHAR(255) DEFAULT NULL,
  package_weight_mode TINYINT(2) UNSIGNED DEFAULT 0,
  package_weight_custom_value DECIMAL(10, 2) UNSIGNED DEFAULT NULL,
  package_weight_custom_attribute VARCHAR(255) DEFAULT NULL,
  package_weight_unit_of_measure_mode TINYINT(2) UNSIGNED DEFAULT 1,
  package_weight_unit_of_measure_custom_value VARCHAR(255) DEFAULT NULL,
  package_weight_unit_of_measure_custom_attribute VARCHAR(255) DEFAULT NULL,
  target_audience_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  target_audience TEXT NOT NULL,
  search_terms_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  search_terms TEXT NOT NULL,
  bullet_points_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  bullet_points TEXT NOT NULL,
  description_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  description_template LONGTEXT NOT NULL,
  image_main_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  image_main_attribute VARCHAR(255) NOT NULL,
  gallery_images_mode TINYINT(2) UNSIGNED NOT NULL,
  gallery_images_limit TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  gallery_images_attribute VARCHAR(255) NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (template_description_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_amazon_template_description_specific` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  template_description_id INT(11) UNSIGNED NOT NULL,
  xpath VARCHAR(255) NOT NULL,
  mode VARCHAR(25) NOT NULL,
  recommended_value VARCHAR(255) DEFAULT NULL,
  custom_value VARCHAR(255) DEFAULT NULL,
  custom_attribute VARCHAR(255) DEFAULT NULL,
  type VARCHAR(25) DEFAULT NULL,
  attributes TEXT DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX template_description_id (template_description_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
        );
    }

    //########################################
}