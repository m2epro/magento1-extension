<?php

class Ess_M2ePro_Sql_Upgrade_v6_3_7__v6_3_8_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->installer;

        $descriptionModifier = $installer->getTableModifier('amazon_template_description');
        $definitionModifier  = $installer->getTableModifier('amazon_template_description_definition');

        $definitionModifier->addColumn('item_package_quantity_mode', 'TINYINT(2) UNSIGNED', 0,
                                       'manufacturer_part_number_custom_attribute', false, false)
            ->addColumn('item_package_quantity_custom_value', 'VARCHAR(255)', 'NULL',
                        'item_package_quantity_mode', false, false)
            ->addColumn('item_package_quantity_custom_attribute', 'VARCHAR(255)', 'NULL',
                        'item_package_quantity_custom_value', false, false)
            ->addColumn('number_of_items_mode', 'TINYINT(2) UNSIGNED', 0,
                        'item_package_quantity_custom_attribute', false, false)
            ->addColumn('number_of_items_custom_value', 'VARCHAR(255)', 'NULL',
                        'number_of_items_mode', false, false)
            ->addColumn('number_of_items_custom_attribute', 'VARCHAR(255)', 'NULL',
                        'number_of_items_custom_value', false, false)
            ->commit();

        // ---------------------------------------

        if ($descriptionModifier->isColumnExists('item_package_quantity_mode') &&
            $definitionModifier->isColumnExists('item_package_quantity_mode')) {

            $installer->run(<<<SQL
UPDATE `m2epro_amazon_template_description_definition` dd
  LEFT JOIN `m2epro_amazon_template_description` d
    ON `d`.`template_description_id` = `dd`.`template_description_id`
SET
    `dd`.`item_package_quantity_mode` = `d`.`item_package_quantity_mode`,
    `dd`.`item_package_quantity_custom_value` = `d`.`item_package_quantity_custom_value`,
    `dd`.`item_package_quantity_custom_attribute` = `d`.`item_package_quantity_custom_attribute`,
    `dd`.`number_of_items_mode` = `d`.`number_of_items_mode`,
    `dd`.`number_of_items_custom_value` = `d`.`number_of_items_custom_value`,
    `dd`.`number_of_items_custom_attribute` = `d`.`number_of_items_custom_attribute`;
SQL
            );
        }

        // ---------------------------------------

        $descriptionModifier->dropColumn('item_package_quantity_mode', false, false)
            ->dropColumn('item_package_quantity_custom_value', false, false)
            ->dropColumn('item_package_quantity_custom_attribute', false, false)
            ->dropColumn('number_of_items_mode', false, false)
            ->dropColumn('number_of_items_custom_value', false, false)
            ->dropColumn('number_of_items_custom_attribute', false, false)
            ->commit();

        // ---------------------------------------

        $installer->getTableModifier('amazon_order')
            ->addColumn('shipping_dates', 'TEXT NULL', 'NULL', 'shipping_price');

        //########################################

        ### Fix the Amazon variations structure

        $installer->run(<<<SQL
DELETE `ai`
FROM `m2epro_amazon_item` `ai`,
  (
     SELECT
       `ml`.`account_id`, `ml`.`marketplace_id`, `malp`.`sku`
     FROM `m2epro_listing_product` `mlp`
        INNER JOIN `m2epro_amazon_listing_product` `malp` ON `malp`.`listing_product_id` = `mlp`.`id`
        INNER JOIN `m2epro_listing` `ml` ON `mlp`.`listing_id` = `ml`.`id`
        LEFT JOIN `m2epro_listing_product_variation` `mlpv` ON `mlpv`.`listing_product_id` = `mlp`.`id`
     WHERE `malp`.`sku` IS NOT NULL AND
           `malp`.`is_variation_product` = 1 AND
           `malp`.`is_variation_product_matched` = 1 AND
           `mlpv`.`id` IS NULL
  ) AS `temp_table`
WHERE `ai`.`sku` = `temp_table`.`sku`
AND `ai`.`account_id` = `temp_table`.`account_id`
AND `ai`.`marketplace_id` = `temp_table`.`marketplace_id`;

UPDATE `m2epro_amazon_listing_product` `malp`
   LEFT JOIN `m2epro_listing_product_variation` `mlpv` ON `mlpv`.`listing_product_id` = `malp`.`listing_product_id`
SET `malp`.`is_variation_product_matched` = 0
WHERE `malp`.`is_variation_product` = 1 AND
      `malp`.`is_variation_product_matched` = 1 AND
      `mlpv`.`id` IS NULL;
SQL
        );
    }

    //########################################
}