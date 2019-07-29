<?php

class Ess_M2ePro_Migration_Tool_To_Version_6_0_9
{
    const DEVELOPMENT_MODE = false;

    const PREFIX_TABLE_BACKUP = '__backup_v608';

    const TEMPLATE_MODE_PARENT   = 0;
    const TEMPLATE_MODE_CUSTOM   = 1;
    const TEMPLATE_MODE_TEMPLATE = 2;
    const TEMPLATE_MODE_POLICY   = 3;

    const RESULT_LISTINGS_KEY          = 'listings';
    const RESULT_LISTINGS_PRODUCTS_KEY = 'listings_products';
    const RESULT_CHANGED_MODES         = 'changed_modes';

    //########################################

    /** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
    private $installer = null;

    private $buffer = array();
    private $result = array();

    //########################################

    public function __construct(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->installer = $installer;
    }

    //########################################

    public function process()
    {
        if (!$this->isNeedToMigrate()) {
            return;
        }

        $this->backup();

        $affectedShippingTemplatesIds = $this->getAffectedShippingTemplatesIds();

        if (empty($affectedShippingTemplatesIds)) {
            return;
        }

        if (self::DEVELOPMENT_MODE) {
            var_dump('affected_shipping_templates_ids', $affectedShippingTemplatesIds);
        }

        $this->migrateListings($affectedShippingTemplatesIds);
        $this->migrateListingsProducts($affectedShippingTemplatesIds);

        $this->updateListingsProductsSellingTemplateModes();

        $this->updateMigratedItems(self::RESULT_LISTINGS_KEY);
        $this->updateMigratedItems(self::RESULT_LISTINGS_PRODUCTS_KEY);

        if (self::DEVELOPMENT_MODE) {
            var_dump('buffer', $this->getMigrationBuffer(), 'result', $this->getMigrationResult());
        }
    }

    //########################################

    public function getInstaller()
    {
        return $this->installer;
    }

    // ---------------------------------------

    public function getMigrationBuffer()
    {
        return $this->buffer;
    }

    // ---------------------------------------

    public function getMigrationResult($key = null)
    {
        if (is_null($key)) {
            return $this->result;
        }

        return isset($this->result[$key]) ? $this->result[$key] : array();
    }

    //########################################

    private function isNeedToMigrate()
    {
        $tempTable = $this->getInstaller()->getTable('m2epro_ebay_template_shipping');
        $connection = $this->getInstaller()->getConnection();

        return $connection->tableColumnExists($tempTable, 'vat_percent') !== false &&
            $connection->tableColumnExists($tempTable, 'tax_table_mode') !== false &&
            $connection->tableColumnExists($tempTable, 'tax_category_mode') !== false;
    }

    //########################################

    private function backup()
    {
        $oldTable = $this->getInstaller()->getTable('m2epro_ebay_template_shipping');
        $backupTable = $this->getInstaller()->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_shipping');

        $this->getInstaller()->getConnection()->multi_query(<<<SQL
DROP TABLE IF EXISTS `{$backupTable}`;
CREATE TABLE `$backupTable` SELECT * FROM `$oldTable`;
SQL
        );
    }

    // ---------------------------------------

    private function migrateListings($affectedShippingTemplatesIds)
    {
        $affectedListingsByShippingTemplatesId = $this->getAffectedListings($affectedShippingTemplatesIds);

        if (self::DEVELOPMENT_MODE) {
            var_dump('affected_listings', $affectedListingsByShippingTemplatesId);
        }

        foreach ($affectedListingsByShippingTemplatesId as $shippingTemplateId => $affectedListings) {
            foreach ($affectedListings as $listingId => $listingData) {

                $sellingTemplateMode = $listingData['template_selling_format_mode'];

                $sellingTemplateId = $sellingTemplateMode == self::TEMPLATE_MODE_CUSTOM
                    ? $listingData['template_selling_format_custom_id']
                    : $listingData['template_selling_format_id'];

                $newSellingTemplateId = $this->getNewSellingTemplateIdFromBuffer(
                    $shippingTemplateId, $sellingTemplateId
                );

                if (!$newSellingTemplateId) {
                    $newSellingTemplateId = $this->createNewSellingTemplate($sellingTemplateId, $shippingTemplateId);
                    $this->appendToBuffer($shippingTemplateId, $sellingTemplateId, $newSellingTemplateId);
                }

                $this->appendMigrationResult(
                    self::RESULT_LISTINGS_KEY, $sellingTemplateMode, $newSellingTemplateId, $listingId
                );
            }
        }
    }

    private function migrateListingsProducts($affectedShippingTemplatesIds)
    {
        $affectedListingsProductsByShippingTemplatesId = $this->arrayReplaceRecursive(
            $this->getAffectedListingsProductsShippingTemplateModeParent($affectedShippingTemplatesIds),
            $this->getAffectedListingsProductsShippingTemplateModeOther($affectedShippingTemplatesIds)
        );

        if (self::DEVELOPMENT_MODE) {
            var_dump('affected_listings_products', $affectedListingsProductsByShippingTemplatesId);
        }

        foreach ($affectedListingsProductsByShippingTemplatesId as $shippingTemplateId => $affectedListingsProducts) {
            foreach ($affectedListingsProducts as $listingProductId => $listingProductData) {

                switch ($sellingTemplateMode = $listingProductData['template_selling_format_mode']) {

                    case self::TEMPLATE_MODE_CUSTOM:
                        $sellingTemplateId = $listingProductData['template_selling_format_custom_id'];
                        break;

                    case self::TEMPLATE_MODE_TEMPLATE:
                        $sellingTemplateId = $listingProductData['template_selling_format_id'];
                        break;

                    default:
                    case self::TEMPLATE_MODE_PARENT:
                        $sellingTemplateId = $listingProductData['listing_selling_format_id'];
                        $sellingTemplateMode = self::TEMPLATE_MODE_CUSTOM;
                        $this->appendChangedModeResult($listingProductId);
                        break;
                }

                $newSellingTemplateId = $this->getNewSellingTemplateIdFromBuffer(
                    $shippingTemplateId, $sellingTemplateId
                );

                if (!$newSellingTemplateId) {
                    $newSellingTemplateId = $this->createNewSellingTemplate($sellingTemplateId, $shippingTemplateId);
                    $this->appendToBuffer($shippingTemplateId, $sellingTemplateId, $newSellingTemplateId);
                }

                $this->appendMigrationResult(
                    self::RESULT_LISTINGS_PRODUCTS_KEY, $sellingTemplateMode, $newSellingTemplateId, $listingProductId
                );
            }
        }
    }

    // ---------------------------------------

    private function updateMigratedItems($key)
    {
        $tableName = 'm2epro_ebay_listing';
        $mysqlWhereColumn = 'listing_id';

        if ($key == self::RESULT_LISTINGS_PRODUCTS_KEY) {
            $tableName = 'm2epro_ebay_listing_product';
            $mysqlWhereColumn = 'listing_product_id';
        }

        $table = $this->getInstaller()->getTable($tableName);

        foreach ($this->getMigrationResult($key) as $sellingTemplateMode => $sellingTemplatesIds) {

            $mysqlUpdateColumn = $sellingTemplateMode == self::TEMPLATE_MODE_CUSTOM
                ? 'template_selling_format_custom_id' : 'template_selling_format_id';

            foreach ($sellingTemplatesIds as $newTemplateId => $ids) {

                $sqlInCondition = implode(',', $ids);

                $this->getInstaller()->getConnection()->query(<<<SQL
UPDATE `{$table}`
SET `{$mysqlUpdateColumn}` = {$newTemplateId}
WHERE `{$mysqlWhereColumn}` IN ($sqlInCondition)
SQL
                );
            }
        }
    }

    private function updateListingsProductsSellingTemplateModes()
    {
        $changedModes = $this->getMigrationResult(self::RESULT_CHANGED_MODES);

        if (empty($changedModes)) {
            return;
        }

        foreach ($changedModes as $listingProductMode => $listingsIds) {

            $listingProductTable = $this->getInstaller()->getTable('m2epro_ebay_listing_product');
            $sqlInCondition = implode(',', $listingsIds);

            $this->getInstaller()->getConnection()->query(<<<SQL
UPDATE `{$listingProductTable}`
SET `template_selling_format_mode` = {$listingProductMode}
WHERE `listing_product_id` IN ({$sqlInCondition})
SQL
            );
        }
    }

    //########################################

    private function getAffectedShippingTemplatesIds()
    {
        $tempTable = $this->getInstaller()->getTable('m2epro_ebay_template_shipping');

        $queryStmt = $this->getInstaller()->getConnection()->query(<<<SQL
SELECT `id` FROM `{$tempTable}`
WHERE `vat_percent` > 0 OR `tax_table_mode` > 0 OR `tax_category_mode` > 0
SQL
        );
        $result = array();
        while ($id = $queryStmt->fetchColumn()) {
            $result[] = (int)$id;
        }

        return $result;
    }

    // ---------------------------------------

    private function getAffectedListings($shippingTemplatesIds)
    {
        $tempTable = $this->getInstaller()->getTable('m2epro_ebay_listing');

        $sqlInCondition = implode(',', $shippingTemplatesIds);
        $sqlModeTemplate = self::TEMPLATE_MODE_TEMPLATE;
        $sqlModeCustom   = self::TEMPLATE_MODE_CUSTOM;

        $queryStmt = $this->getInstaller()->getConnection()->query(<<<SQL
SELECT *,
       IF (`template_shipping_mode` = {$sqlModeTemplate},
           `template_shipping_id`,
           `template_shipping_custom_id`) AS `shipping_id`
FROM `{$tempTable}`
WHERE (`template_shipping_mode` = {$sqlModeTemplate} AND `template_shipping_id` IN ({$sqlInCondition}))
   OR (`template_shipping_mode` = {$sqlModeCustom} AND `template_shipping_custom_id` IN ({$sqlInCondition}))
SQL
        );
        $result = array();
        while ($row = $queryStmt->fetch()) {
            $result[$row['shipping_id']][$row['listing_id']] = $row;
        }

        return $result;
    }

    private function getAffectedListingsProductsShippingTemplateModeParent($shippingTemplatesIds)
    {
        $listingTable = $this->getInstaller()->getTable('m2epro_ebay_listing');
        $generalListingProductTable = $this->getInstaller()->getTable('m2epro_listing_product');
        $listingProductTable = $this->getInstaller()->getTable('m2epro_ebay_listing_product');

        $sqlInCondition = implode(',', $shippingTemplatesIds);

        $sqlModeTemplate = self::TEMPLATE_MODE_TEMPLATE;
        $sqlModeCustom   = self::TEMPLATE_MODE_CUSTOM;
        $sqlModeParent   = self::TEMPLATE_MODE_PARENT;

        $queryStmt = $this->getInstaller()->getConnection()->query(<<<SQL
    SELECT `{$listingProductTable}`.*,
           IF (`{$listingTable}`.`template_shipping_mode` = {$sqlModeTemplate},
               `{$listingTable}`.`template_shipping_id`,
               `{$listingTable}`.`template_shipping_custom_id`) AS `shipping_id`
    FROM `{$listingProductTable}`
    LEFT JOIN `{$generalListingProductTable}`
        ON `{$listingProductTable}`.`listing_product_id` = `{$generalListingProductTable}`.`id`
    LEFT JOIN `{$listingTable}`
        ON `{$generalListingProductTable}`.`listing_id` = `{$listingTable}`.`listing_id`
    WHERE `{$listingProductTable}`.`template_shipping_mode` = {$sqlModeParent}
        AND `{$listingProductTable}`.`template_selling_format_mode` <> {$sqlModeParent}
        AND (`{$listingTable}`.`template_shipping_mode` = {$sqlModeTemplate}
                AND `{$listingTable}`.`template_shipping_id` IN ({$sqlInCondition})
             OR `{$listingTable}`.`template_shipping_mode` = {$sqlModeCustom}
                AND `{$listingTable}`.`template_shipping_custom_id` IN ({$sqlInCondition}))
SQL
        );
        $result = array();
        while ($row = $queryStmt->fetch()) {
            $result[$row['shipping_id']][$row['listing_product_id']] = $row;
        }

        return $result;
    }

    private function getAffectedListingsProductsShippingTemplateModeOther($shippingTemplatesIds)
    {
        $listingTable = $this->getInstaller()->getTable('m2epro_ebay_listing');
        $generalListingProductTable = $this->getInstaller()->getTable('m2epro_listing_product');
        $listingProductTable = $this->getInstaller()->getTable('m2epro_ebay_listing_product');

        $sqlInCondition = implode(',', $shippingTemplatesIds);
        $sqlModeTemplate = self::TEMPLATE_MODE_TEMPLATE;
        $sqlModeCustom   = self::TEMPLATE_MODE_CUSTOM;

        $queryStmt = $this->getInstaller()->getConnection()->query(<<<SQL
SELECT `{$listingProductTable}`.*,
       IF (`{$listingProductTable}`.`template_shipping_mode` = {$sqlModeTemplate},
           `{$listingProductTable}`.`template_shipping_id`,
           `{$listingProductTable}`.`template_shipping_custom_id`) AS `shipping_id`,
       IF (`{$listingTable}`.`template_selling_format_mode` = {$sqlModeTemplate},
           `{$listingTable}`.`template_selling_format_id`,
           `{$listingTable}`.`template_selling_format_custom_id`) AS `listing_selling_format_id`
FROM `{$listingProductTable}`
LEFT JOIN `{$generalListingProductTable}`
    ON `{$listingProductTable}`.`listing_product_id` = `{$generalListingProductTable}`.`id`
LEFT JOIN `{$listingTable}`
    ON `{$generalListingProductTable}`.`listing_id` = `{$listingTable}`.`listing_id`
WHERE (`{$listingProductTable}`.`template_shipping_mode` = {$sqlModeTemplate}
          AND `{$listingProductTable}`.`template_shipping_id` IN ({$sqlInCondition}))
       OR (`{$listingProductTable}`.`template_shipping_mode` = {$sqlModeCustom}
          AND `{$listingProductTable}`.`template_shipping_custom_id` IN ({$sqlInCondition}))
SQL
        );
        $result = array();
        while ($row = $queryStmt->fetch()) {
            $result[$row['shipping_id']][$row['listing_product_id']] = $row;
        }

        return $result;
    }

    //########################################

    private function getNewSellingTemplateIdFromBuffer($shippingTemplateId, $sellingTemplateId)
    {
        $buffer = $this->getMigrationBuffer();

        return isset($buffer[$shippingTemplateId.'#'.$sellingTemplateId])
            ? $buffer[$shippingTemplateId.'#'.$sellingTemplateId] : false;
    }

    private function appendToBuffer($shippingTemplateId, $sellingTemplateId, $newSellingTemplateId)
    {
        $this->buffer[$shippingTemplateId.'#'.$sellingTemplateId] = $newSellingTemplateId;
    }

    // ---------------------------------------

    private function appendMigrationResult($key, $templateMode, $newSellingTemplateId, $targetId)
    {
        $this->result[$key][$templateMode][$newSellingTemplateId][] = $targetId;
    }

    private function appendChangedModeResult($listingProductId, $templateMode = self::TEMPLATE_MODE_CUSTOM)
    {
        $this->result[self::RESULT_CHANGED_MODES][$templateMode][] = $listingProductId;
    }

    //########################################

    private function createNewSellingTemplate($sellingTemplateId, $shippingTemplateId)
    {
        $generalSellingTable = $this->getInstaller()->getTable('m2epro_template_selling_format');
        $sellingTable = $this->getInstaller()->getTable('m2epro_ebay_template_selling_format');
        $shippingTable = $this->getInstaller()->getTable('m2epro_ebay_template_shipping');

        $this->getInstaller()->getConnection()->multi_query(<<<SQL
INSERT INTO `{$generalSellingTable}`
    (`title`, `component_mode`, `update_date`, `create_date`)
SELECT CONCAT(`{$generalSellingTable}`.`title`,
              IF (`{$shippingTable}`.`title` <> '' AND `{$generalSellingTable}`.`title` <> '',
                  CONCAT(' (',`{$shippingTable}`.`title`,')'),
                  '')),
       `{$generalSellingTable}`.`component_mode`,
       `{$generalSellingTable}`.`update_date`,
       `{$generalSellingTable}`.`create_date`
FROM `{$generalSellingTable}`
INNER JOIN `{$shippingTable}`
    ON `{$shippingTable}`.`id` = {$shippingTemplateId}
WHERE `{$generalSellingTable}`.`id` = {$sellingTemplateId}
SQL
        );
        $newTemplateId = (int)$this->getInstaller()->getConnection()->lastInsertId();

        $this->getInstaller()->getConnection()->multi_query(<<<SQL
INSERT INTO `{$sellingTable}`
SELECT {$newTemplateId},
       `{$sellingTable}`.`is_custom_template`,
       `{$sellingTable}`.`listing_type`,
       `{$sellingTable}`.`listing_type_attribute`,
       `{$sellingTable}`.`listing_is_private`,
       `{$sellingTable}`.`duration_mode`,
       `{$sellingTable}`.`duration_attribute`,
       `{$sellingTable}`.`out_of_stock_control`,
       `{$sellingTable}`.`qty_mode`,
       `{$sellingTable}`.`qty_custom_value`,
       `{$sellingTable}`.`qty_custom_attribute`,
       `{$sellingTable}`.`qty_max_posted_value_mode`,
       `{$sellingTable}`.`qty_max_posted_value`,
       `{$shippingTable}`.`vat_percent`,
       `{$shippingTable}`.`tax_table_mode`,
       `{$shippingTable}`.`tax_category_mode`,
       `{$shippingTable}`.`tax_category_value`,
       `{$shippingTable}`.`tax_category_attribute`,
       `{$sellingTable}`.`price_increase_vat_percent`,
       `{$sellingTable}`.`price_variation_mode`,
       `{$sellingTable}`.`start_price_mode`,
       `{$sellingTable}`.`start_price_coefficient`,
       `{$sellingTable}`.`start_price_custom_attribute`,
       `{$sellingTable}`.`reserve_price_mode`,
       `{$sellingTable}`.`reserve_price_coefficient`,
       `{$sellingTable}`.`reserve_price_custom_attribute`,
       `{$sellingTable}`.`buyitnow_price_mode`,
       `{$sellingTable}`.`buyitnow_price_coefficient`,
       `{$sellingTable}`.`buyitnow_price_custom_attribute`,
       `{$sellingTable}`.`price_discount_stp_mode`,
       `{$sellingTable}`.`price_discount_stp_attribute`,
       `{$sellingTable}`.`price_discount_stp_type`,
       `{$sellingTable}`.`best_offer_mode`,
       `{$sellingTable}`.`best_offer_accept_mode`,
       `{$sellingTable}`.`best_offer_accept_value`,
       `{$sellingTable}`.`best_offer_accept_attribute`,
       `{$sellingTable}`.`best_offer_reject_mode`,
       `{$sellingTable}`.`best_offer_reject_value`,
       `{$sellingTable}`.`best_offer_reject_attribute`,
       `{$sellingTable}`.`charity`,
       `{$sellingTable}`.`ignore_variations`
FROM `{$sellingTable}`
INNER JOIN `{$shippingTable}`
    ON `{$shippingTable}`.`id` = {$shippingTemplateId}
WHERE `{$sellingTable}`.`template_selling_format_id` = {$sellingTemplateId}
SQL
        );
        return $newTemplateId;
    }

    //########################################

    private function arrayReplaceRecursive($base, $replacements)
    {
        $args = func_get_args();
        foreach (array_slice($args, 1) as $replacements) {

            $bref_stack = array(&$base);
            $head_stack = array($replacements);

            do {
                end($bref_stack);

                $bref = &$bref_stack[key($bref_stack)];
                $head = array_pop($head_stack);

                unset($bref_stack[key($bref_stack)]);

                foreach (array_keys($head) as $key) {

                    if (isset($key, $bref, $bref[$key]) && is_array($bref[$key]) && is_array($head[$key])) {
                        $bref_stack[] = &$bref[$key];
                        $head_stack[] = $head[$key];
                    } else {
                        $bref[$key] = $head[$key];
                    }
                }
            } while (count($head_stack));
        }

        return $base;
    }

    //########################################
}