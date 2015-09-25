<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

$installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS `m2epro_amazon_category_description`(
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  title_template VARCHAR(255) NOT NULL,
  brand_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  brand_template VARCHAR(255) NOT NULL,
  manufacturer_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  manufacturer_template VARCHAR(255) NOT NULL,
  manufacturer_part_number_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  manufacturer_part_number_template VARCHAR(255) NOT NULL,
  bullet_points_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  bullet_points TEXT NOT NULL,
  description_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  description_template LONGTEXT NOT NULL,
  image_main_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  image_main_attribute VARCHAR(255) NOT NULL,
  gallery_images_mode TINYINT(2) UNSIGNED NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX brand_mode (brand_mode),
  INDEX bullet_points_mode (bullet_points_mode),
  INDEX description_mode (description_mode),
  INDEX gallery_images_mode (gallery_images_mode),
  INDEX image_main_attribute (image_main_attribute),
  INDEX image_main_mode (image_main_mode),
  INDEX manufacturer_mode (manufacturer_mode),
  INDEX title_mode (title_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

//#############################################

/*
    ALTER TABLE `m2epro_amazon_category`
    CHANGE COLUMN `template_description_id` `category_description_id` INT(11) UNSIGNED NOT NULL,
    DROP INDEX `template_description_id`,
    ADD INDEX `category_description_id` (`category_description_id`);
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_category');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'template_description_id') !== false &&
    $connection->tableColumnExists($tempTable, 'category_description_id') === false) {
    $connection->changeColumn(
        $tempTable,
        'template_description_id',
        'category_description_id',
        'INT(11) UNSIGNED NOT NULL'
    );
}

if (isset($tempTableIndexList[strtoupper('template_description_id')])) {
    $connection->dropKey($tempTable, 'template_description_id');
}

if (!isset($tempTableIndexList[strtoupper('category_description_id')])) {
    $connection->addKey($tempTable, 'category_description_id', 'category_description_id');
}

//#############################################

$tempTable = $installer->getTable('m2epro_template_description');
$stmt = $connection->query("SELECT `id`
                            FROM `{$tempTable}`
                            WHERE `component_mode` = 'amazon'");

$templatesDescriptionIds = array();
while ($id = $stmt->fetchColumn()) {
    $templatesDescriptionIds[] = (int)$id;
}

if (count($templatesDescriptionIds) > 0) {

    $tempTable = $installer->getTable('m2epro_attribute_set');
    $connection->query(
        "DELETE FROM `{$tempTable}`
         WHERE `object_id` IN (".implode(',',$templatesDescriptionIds).")
         AND   `object_type` = 4"
    );
}

// --------------------------------------------

$installer->run(<<<SQL

DELETE FROM `m2epro_template_description`
WHERE `component_mode` = 'amazon';

DROP TABLE IF EXISTS m2epro_amazon_template_description;
CREATE TABLE m2epro_amazon_template_description (
  template_description_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (template_description_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

// --------------------------------------------

$tempTable = $installer->getTable('m2epro_template_description');
$currentGmtDateMySql = $connection->quote(Mage::getModel('core/date')->gmtDate(NULL));

$connection->query("INSERT INTO `{$tempTable}` (`title`,
                                                `component_mode`,
                                                `synch_date`,
                                                `update_date`,
                                                `create_date`)
                    VALUES ('Default',
                            'amazon',
                            {$currentGmtDateMySql},
                            {$currentGmtDateMySql},
                            {$currentGmtDateMySql})");

// --------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_description');
$templateDescriptionId = (int)$connection->lastInsertId();

$connection->query("INSERT INTO `{$tempTable}` (`template_description_id`)
                    VALUES ({$templateDescriptionId})");

// --------------------------------------------

$tempTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
$attributeSets = Mage::getModel('eav/entity_attribute_set')
                ->getCollection()
                ->setEntityTypeFilter($tempTypeId)
                ->toArray();

$tempTable = $installer->getTable('m2epro_attribute_set');
$currentGmtDateMySql = $connection->quote(Mage::getModel('core/date')->gmtDate(NULL));

foreach ($attributeSets['items'] as $attributeSet) {

    $attributesSetId = (int)$attributeSet['attribute_set_id'];

    $connection->query("INSERT INTO `{$tempTable}` (`object_id`,
                                                    `object_type`,
                                                    `attribute_set_id`,
                                                    `update_date`,
                                                    `create_date`)
                        VALUES ({$templateDescriptionId},
                                4,
                                {$attributesSetId},
                                {$currentGmtDateMySql},
                                {$currentGmtDateMySql})");
}

// --------------------------------------------

$tempTable = $installer->getTable('m2epro_listing');

$connection->query("UPDATE `{$tempTable}`
                    SET `template_description_id` = {$templateDescriptionId}
                    WHERE `component_mode` = 'amazon'");

//#############################################

$installer->endSetup();

//#############################################