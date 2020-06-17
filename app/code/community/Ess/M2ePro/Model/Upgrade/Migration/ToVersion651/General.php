<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

// @codingStandardsIgnoreFile

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_General extends Ess_M2ePro_Model_Upgrade_Migration_Abstract
{
    //########################################

    public function run()
    {
        $this->wizards();
        $this->listings();
        $this->orders();
        $this->templates();
        $this->instructions();
    }

    //########################################

   protected function listings()
   {
       $this->_installer->getTableModifier('stop_queue')
                        ->dropColumn('item_data')
                        ->dropColumn('account_hash')
                        ->dropColumn('marketplace_id')
                        ->addColumn('additional_data', 'TEXT', null, 'is_processed');

       if ($this->_installer->getTableModifier('listing_product')->isColumnExists('synch_reasons')) {
           $listingProductTable = $this->getFullTableName('listing_product');
        $queryStmt = $this->_installer->getConnection()->query(
            <<<SQL
SELECT `id`, `synch_reasons`
FROM {$listingProductTable}
WHERE `synch_reasons` LIKE '%shippingOverrideTemplate%';
SQL
        );

           while ($row = $queryStmt->fetch()) {
               $reasons = explode(',', $row['synch_reasons']);
               $reasons =  array_unique(array_filter($reasons));

            array_walk(
                $reasons, function (&$el){
                   $el = str_replace('shippingOverrideTemplate', 'shippingTemplate', $el);
                }
            );
               $reasons = implode(',', $reasons);

            $this->_installer->getConnection()->query(
                <<<SQL
UPDATE {$listingProductTable}
SET `synch_reasons` = '{$reasons}'
WHERE `id` = {$row['id']}
SQL
            );
           }
       }
   }

   protected function orders()
   {
       $this->_installer
            ->getTableModifier('order')
            ->addColumn(
                'magento_order_creation_failure', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'magento_order_id', true, false
            )
            ->addColumn(
                'magento_order_creation_fails_count', 'TINYINT(2) UNSIGNED NOT NULL', '0',
                'magento_order_creation_failure', true, false
            )
            ->addColumn(
                'magento_order_creation_latest_attempt_date', 'DATETIME', null,
                'magento_order_creation_fails_count', true, false
            )
            ->commit();
   }

   protected function templates()
   {
       $this->_installer->getTableModifier('template_synchronization')
                        ->dropColumn('revise_change_listing', true, false)
                        ->dropColumn('revise_change_selling_format_template', true, false)
                        ->commit();
   }

   protected function wizards()
   {
       $wizardTable = $this->getFullTableName('wizard');
       $tempQuery = <<<SQL
SELECT * FROM {$wizardTable} WHERE `nick` = 'removedEbay3rdParty';
SQL;
       $tempRow = $this->_installer->getConnection()->query($tempQuery)->fetch();
       if ($tempRow === false) {
           $wizardStatus = 3;

           if ($this->_installer->getTablesObject()->isExists('synchronization_config')) {
               $tempTable = $this->getFullTableName('synchronization_config');
            $queryStmt = $this->_installer->getConnection()->query(
                <<<SQL
SELECT `value` FROM {$tempTable} WHERE
    (`group` = '/ebay/other_listing/synchronization/' AND `key` = 'mode') OR
    (`group` = '/ebay/other_listing/source/');
SQL
            );

               while ($mode = $queryStmt->fetchColumn()) {
                   if ($mode == 1) {
                       $wizardStatus = 0;
                       break;
                   }
               }
           }

        $this->_installer->run(
            <<<SQL
INSERT INTO `{$this->_installer->getTable('m2epro_wizard')}` (`nick`, `view`, `status`, `step`, `type`, `priority`)
SELECT 'removedEbay3rdParty', 'ebay', {$wizardStatus}, NULL, 0, MAX( `priority` )+1 FROM `m2epro_wizard`;
SQL
        );
       }

       $select = $this->_installer->getConnection()->select()
                                  ->from($this->_installer->getTablesObject()->getFullName('listing_product'), 'id')
                                  ->where('component_mode = ?', 'buy')
                                  ->where('status != ?', 0);

       $result = $this->_installer->getConnection()->fetchCol($select);
       if (!empty($result)) {
           $removedBuyWizardStatus = 0;
       } else {
           $removedBuyWizardStatus = 3;
       }

       $select = $this->_installer->getConnection()->select()
                                  ->from($this->_installer->getTablesObject()->getFullName('wizard'));

       $oldWizardsData = $this->_installer->getConnection()->fetchAll($select);

       $newWizardsData = array(
           'installationEbay' => array(
               'nick'     => 'installationEbay',
               'view'     => 'ebay',
               'status'   => 0,
               'step'     => null,
               'type'     => 1,
               'priority' => 1,
           ),
           'amazon' => array(
               'nick'     => 'installationAmazon',
               'view'     => 'amazon',
               'status'   => 0,
               'step'     => null,
               'type'     => 1,
               'priority' => 2,
           ),
           'migrationNewAmazon' => array(
               'nick'     => 'migrationNewAmazon',
               'view'     => 'amazon',
               'status'   => 3,
               'step'     => null,
               'type'     => 1,
               'priority' => 3,
           ),
           'removedPlay' => array(
               'nick'     => 'removedPlay',
               'view'     => '*',
               'status'   => 3,
               'step'     => null,
               'type'     => 0,
               'priority' => 4,
           ),
           'ebayProductDetails' => array(
               'nick'     => 'ebayProductDetails',
               'view'     => 'ebay',
               'status'   => 3,
               'step'     => null,
               'type'     => 1,
               'priority' => 5,
           ),
           'fullAmazonCategories' => array(
               'nick'     => 'fullAmazonCategories',
               'view'     => 'amazon',
               'status'   => 3,
               'step'     => null,
               'type'     => 1,
               'priority' => 6,
           ),
           'removedEbay3rdParty' => array(
               'nick'     => 'removedEbay3rdParty',
               'view'     => 'ebay',
               'status'   => 3,
               'step'     => null,
               'type'     => 0,
               'priority' => 8,
           ),
           'removedBuy' => array(
               'nick'     => 'removedBuy',
               'view'     => '*',
               'status'   => $removedBuyWizardStatus,
               'step'     => null,
               'type'     => 0,
               'priority' => 9,
           ),
       );

       foreach ($oldWizardsData as $oldWizardData) {
           if (!isset($newWizardsData[$oldWizardData['nick']])) {
               continue;
           }

           $newWizardsData[$oldWizardData['nick']]['status'] = $oldWizardData['status'];
           $newWizardsData[$oldWizardData['nick']]['step']   = $oldWizardData['step'];
       }

    $this->_installer->run(
        <<<SQL
TRUNCATE TABLE `{$this->_installer->getTable('m2epro_wizard')}`;
SQL
    );

    $this->_installer->getConnection()->insertMultiple(
        $this->_installer->getTablesObject()->getFullName('wizard'),
        array_values($newWizardsData)
    );
   }

   protected function instructions()
   {
       $listingProductTable = $this->_installer->getTablesObject()->getFullName('listing_product');
       $instructionTable    = $this->_installer->getTablesObject()->getFullName('listing_product_instruction');

       if ($this->_installer->getTablesObject()->isExists('product_change')) {
           $productChangeTable = $this->_installer->getTablesObject()->getFullName('product_change');

        $changedProductsListingsProductsData = $this->_installer->getConnection()->query(
            "
SELECT `lp`.`id`, `lp`.`component_mode`, `pc`.`attribute`
FROM `{$listingProductTable}` AS `lp`
LEFT JOIN `{$productChangeTable}` AS `pc` ON `pc`.`product_id` = `lp`.`product_id`
WHERE `pc`.`product_id` IS NOT NULL
"
        )->fetchAll(PDO::FETCH_ASSOC);

           $instructionsInsertData = array();

           foreach ($changedProductsListingsProductsData as $listingProductData) {
               $instructionTypes = array(
                   'magento_product_qty_data_potentially_changed',
                   'magento_product_price_data_potentially_changed',
                   'magento_product_status_data_potentially_changed',
               );

               foreach ($instructionTypes as $instructionType) {
                   $instructionsInsertData[] = array(
                       'listing_product_id' => $listingProductData['id'],
                       'component'          => $listingProductData['component_mode'],
                       'type'               => $instructionType,
                       'priority'           => 80,
                       'create_date'        => date('Y-m-d H:i:s', gmdate('U'))
                   );
               }
           }

           $instructionsInsertDataParts = array_chunk($instructionsInsertData, 1000);

           foreach ($instructionsInsertDataParts as $instructionsInsertDataPart) {
               $this->_installer->getConnection()->insertMultiple($instructionTable, $instructionsInsertDataPart);
           }

           $this->_installer->getConnection()->dropTable($productChangeTable);
       }

       // ---------------------------------------

       if ($this->_installer->getTableModifier('listing_product')->isColumnExists('synch_status')) {
        $synchStatusNeedListingsProductsData = $this->_installer->getConnection()->query(
            "
SELECT `id`, `component_mode`
FROM `{$listingProductTable}`
WHERE `synch_status` = 1 AND `status` IN (2, 6);
"
        )->fetchAll(PDO::FETCH_ASSOC);

           $instructionsInsertData = array();

           foreach ($synchStatusNeedListingsProductsData as $listingProductData) {
               $instructionTypes = array(
                   'magento_product_qty_data_potentially_changed',
                   'magento_product_price_data_potentially_changed',
                   'magento_product_status_data_potentially_changed',
               );

               foreach ($instructionTypes as $instructionType) {
                   $instructionsInsertData[] = array(
                       'listing_product_id' => $listingProductData['id'],
                       'component'          => $listingProductData['component_mode'],
                       'type'               => $instructionType,
                       'priority'           => 60,
                       'create_date'        => date('Y-m-d H:i:s', gmdate('U'))
                   );
               }
           }

           $instructionsInsertDataParts = array_chunk($instructionsInsertData, 1000);

           foreach ($instructionsInsertDataParts as $instructionsInsertDataPart) {
               $this->_installer->getConnection()->insertMultiple($instructionTable, $instructionsInsertDataPart);
           }

           $this->_installer->getTableModifier('listing_product')
                            ->dropColumn('tried_to_list', true, false)
                            ->dropColumn('synch_status', true, false)
                            ->dropColumn('synch_reasons', true, false)
                            ->dropColumn('need_synch_rules_check', true, false)
                            ->dropColumn('synch_rules_check_data', true, false)
                            ->commit();
       }
   }

    //########################################
}
