<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_Configs extends Ess_M2ePro_Model_Upgrade_Migration_Abstract
{
    //########################################

    public function run()
    {
        $this->primaryConfig();
        $this->moduleConfig();
        $this->cacheConfig();
    }

    //########################################

    protected function primaryConfig()
    {
        $primaryConfig = $this->_installer->getPrimaryConfigModifier();

        $primaryConfig->getEntity('/M2ePro/license/', 'directory')->delete();
        $primaryConfig->getEntity('/M2ePro/license/valid/', 'directory')->delete();
        $primaryConfig->getEntity('/M2ePro/server/', 'lock')->delete();

        foreach (array('ebay', 'amazon', 'buy') as $component) {
            $tempGroup = '/M2ePro/license/' . $component . '/';

            $primaryConfig->getEntity($tempGroup, 'mode')->delete();
            $primaryConfig->getEntity($tempGroup, 'expiration_date')->delete();
            $primaryConfig->getEntity($tempGroup, 'status')->delete();
            $primaryConfig->getEntity($tempGroup, 'is_free')->delete();
        }

        $this->_installer->getPrimaryConfigModifier()->insert('/M2ePro/license/', 'status', 1);
    }

    protected function moduleConfig()
    {
        $this->_installer->getMainConfigModifier()
                         ->insert('/logs/clearing/ebay_pickup_store/', 'mode', '1', '0 - disable, \r\n1 - enable');
        $this->_installer->getMainConfigModifier()
                         ->insert('/logs/clearing/ebay_pickup_store/', 'days', '30', 'in days');
        $this->_installer->getMainConfigModifier()
                         ->insert('/logs/ebay_pickup_store/', 'last_action_id', 0);

        $this->_installer
            ->getMainConfigModifier()
            ->insert('/view/products_grid/', 'use_alternative_mysql_select', 0, "0 - disable, \r\n1 - enable");

        $this->_installer
            ->getMainConfigModifier()
            ->insert('/cron/checker/task/repair_crashed_tables/', 'interval', '3600', 'in seconds');

        $this->_installer->getMainConfigModifier()
                         ->getEntity('/support/uservoice/', 'api_url')->delete();
        $this->_installer->getMainConfigModifier()
                         ->getEntity('/support/uservoice/', 'api_client_key')->delete();

        $this->_installer
            ->getMainConfigModifier()
            ->getEntity('/support/', 'knowledge_base_url')->updateValue('https://support.m2epro.com/knowledgebase');

        $this->_installer->getMainConfigModifier()
                         ->getEntity('/support/', 'documentation_url')->updateValue('https://docs.m2epro.com');

        $this->_installer->getMainConfigModifier()
                         ->getEntity('/support/', 'main_website_url')->updateValue('https://m2epro.com/');

        $this->_installer->getMainConfigModifier()
                         ->getEntity('/support/', 'main_support_url')->updateValue('https://support.m2epro.com/');

        $magentoConnectUrl = 'https://www.magentocommerce.com/magento-connect/'
            .'ebay-amazon-rakuten-magento-integration-order-import-and-stock-level-synchronization.html';
        $this->_installer->getMainConfigModifier()
                         ->getEntity('/support/', 'magento_connect_url')->updateValue($magentoConnectUrl);

        $this->_installer->getMainConfigModifier()
                         ->insert(NULL, 'is_disabled', '0', '0 - disable, \r\n1 - enable');

        $this->_installer->getMainConfigModifier()
                         ->getEntity('/buy/template/new_sku/', 'upc_exemption')->delete();

        $this->_installer->getMainConfigModifier()
                         ->getEntity('/cron/service/', 'hostname')->updateKey('hostname_1');

        $this->_installer->getMainConfigModifier()->insert(
            '/magento/attribute/', 'price_type_converting', '0', '0 - disable, \r\n1 - enable'
        );

        $this->_installer->getMainConfigModifier()
                         ->insert('/amazon/business/', 'mode', '0', '0 - disable, \r\n1 - enable');

        $this->_installer->getMainConfigModifier()
                         ->getEntity('/ebay/motors/', 'epids_attribute')->updateKey('epids_motor_attribute');
        $this->_installer->getMainConfigModifier()
                         ->getEntity('/ebay/motors/', 'epids_uk_attribute')->insert(NULL);
        $this->_installer->getMainConfigModifier()
                         ->getEntity('/ebay/motors/', 'epids_de_attribute')->insert(NULL);

        $this->_installer->getMainConfigModifier()
                         ->delete('/view/ebay/terapeak/');

        $this->_installer->getMainConfigModifier()
                         ->delete("/ebay/selling/currency/");

        $this->_installer->getMainConfigModifier()
                         ->insert('/component/ebay/variation/', 'mpn_can_be_changed', '0');

        $value = $this->_installer->getConnection()
                                  ->select()
                                  ->from($this->_installer->getTable('core_config_data'), array('value'))
                                  ->where('path = ?', 'web/secure/use_in_frontend')
                                  ->where('scope_id = ?', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
                                  ->query()
                                  ->fetchColumn();

        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/description/', 'should_be_ulrs_secure', (int)$value, '0 - No, \r\n1 - Yes'
        );

        $this->_installer->getConnection()->delete(
            $this->_installer->getTablesObject()->getFullName('config'),
            array('`group` like ?' => '/component/buy/%')
        );

        $this->_installer->getConnection()->delete(
            $this->_installer->getTablesObject()->getFullName('config'),
            array('`group` like ?' => '/buy/%')
        );

        $this->_installer->getConnection()->delete(
            $this->_installer->getTablesObject()->getFullName('config'),
            array('`group` = ?' => '/view/common/component/')
        );

        $this->_installer->getMainConfigModifier()->updateGroup(
            '/view/amazon/autocomplete/',
            array('`group` = ?' => '/view/common/autocomplete/')
        );

        $this->_installer->getMainConfigModifier()
                         ->insert('/amazon/listing/product/action/scheduled_data/', 'limit', '20000');
        $this->_installer
            ->getMainConfigModifier()
            ->insert('/amazon/listing/product/action/processing/prepare/', 'max_listings_products_count', '2000');

        $this->_installer->getConnection()->delete(
            $this->_installer->getTablesObject()->getFullName('config'),
            array('`group` like \'/cron/task/%\'')
        );

        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/system/archive_old_orders/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()
                         ->insert('/cron/task/system/archive_old_orders/', 'interval', '3600', 'in seconds');
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/system/clear_old_logs/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()
                         ->insert('/cron/task/system/clear_old_logs/', 'interval', '86400', 'in seconds');
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/system/connector_command_pending/process_partial/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/system/connector_command_pending/process_partial/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/system/connector_command_pending/process_single/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/system/connector_command_pending/process_single/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/system/issues_resolver/remove_missed_processing_locks/',
            'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/system/issues_resolver/remove_missed_processing_locks/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/system/processing/process_result/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/system/processing/process_result/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/system/request_pending/process_partial/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/system/request_pending/process_partial/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/system/request_pending/process_single/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/system/request_pending/process_single/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/system/servicing/synchronize/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/system/servicing/synchronize/', 'interval', rand(43200, 86400), 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/magento/product/detect_directly_added/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/magento/product/detect_directly_added/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/magento/product/detect_directly_deleted/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/magento/product/detect_directly_deleted/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/inspect_direct_changes/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/inspect_direct_changes/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/process_revise_total/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/process_revise_total/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/auto_actions/process_magento_product_websites_updates/',
            'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/auto_actions/process_magento_product_websites_updates/',
            'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/stop_queue/process/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/stop_queue/process/', 'interval', '3600', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/stop_queue/remove_old/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/stop_queue/remove_old/', 'interval', '86400', 'in seconds'
        );

        $inspectorMode = 0;
        if ($this->_installer->getTablesObject()->isExists('synchronization_config')) {
            $inspectorMode = $this->_installer->getConnection()
                                              ->select()
                                              ->from($this->getFullTableName('synchronization_config'), array('value'))
                                              ->where('`group` = ?', '/global/magento_products/inspector/')
                                              ->where('`key` = ?', 'mode')
                                              ->query()
                                              ->fetchColumn();
        }

        $this->_installer->getMainConfigModifier()->insert(
            '/listing/product/inspector/', 'mode', $inspectorMode, '0 - disable, \r\n1 - enable'
        );

        $this->_installer->getMainConfigModifier()->getEntity('/cron/', 'last_executed_slow_task')->updateValue(NULL);

        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/update_accounts_preferences/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/update_accounts_preferences/', 'interval', '86400', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/template/remove_unused/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/template/remove_unused/', 'interval', '3600', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/channel/synchronize_changes/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/channel/synchronize_changes/', 'interval', '300', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/feedbacks/download_new/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/feedbacks/download_new/', 'interval', '10800', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/feedbacks/send_response/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/feedbacks/send_response/', 'interval', '10800', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/other/resolve_sku/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/other/resolve_sku/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/other/channel/synchronize_data/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/other/channel/synchronize_data/', 'interval', '86400', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/product/process_instructions/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/product/process_instructions/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/product/process_scheduled_actions/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/product/process_scheduled_actions/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/product/process_actions/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/product/process_actions/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/product/remove_potential_duplicates/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/product/remove_potential_duplicates/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/order/create_failed/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/order/create_failed/',
            'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/order/update/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/order/update/',
            'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/order/cancel/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/order/cancel/',
            'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/order/reserve_cancel/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/order/reserve_cancel/',
            'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/pickup_store/schedule_for_update/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/pickup_store/schedule_for_update/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/pickup_store/update_on_channel/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/pickup_store/update_on_channel/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/listing/product/inspector/ebay/', 'max_allowed_instructions_count', '2000', NULL
        );
        $this->_installer->getMainConfigModifier()->insert('/listing/product/revise/total/ebay/', 'mode', '0', NULL);
        $this->_installer->getMainConfigModifier()->insert(
            '/listing/product/revise/total/ebay/', 'max_allowed_instructions_count', '2000', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/instructions/cron/', 'listings_products_per_one_time', '1000', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/list/',
            'priority_coefficient', '25', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/list/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/relist/', 'priority_coefficient', '125', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/relist/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_qty/', 'priority_coefficient', '500', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_qty/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_price/', 'priority_coefficient', '250', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_price/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_title/', 'priority_coefficient', '50', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_title/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_subtitle/', 'priority_coefficient', '50', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_subtitle/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_description/', 'priority_coefficient', '50', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_description/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_images/', 'priority_coefficient', '50', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_images/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_categories/', 'priority_coefficient', '50', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_categories/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_payment/', 'priority_coefficient', '50', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_payment/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_shipping/', 'priority_coefficient', '50', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_shipping/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_return/', 'priority_coefficient', '50', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_return/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_other/', 'priority_coefficient', '50', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_other/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/stop/',
            'priority_coefficient', '1000', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/stop/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/scheduled_actions/', 'max_prepared_actions_count', '3000', NULL
        );

        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/other/resolve_title/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/other/resolve_title/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/other/channel/synchronize_data/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/other/channel/synchronize_data/', 'interval', '86400', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/other/channel/synchronize_data/blocked/',
            'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/other/channel/synchronize_data/blocked/', 'interval', '3600', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/channel/synchronize_data/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/channel/synchronize_data/', 'interval', '86400', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/channel/synchronize_data/blocked/',
            'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/channel/synchronize_data/blocked/', 'interval', '3600', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/channel/synchronize_data/defected/',
            'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/channel/synchronize_data/defected/', 'interval', '259200', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/run_variation_parent_processors/',
            'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/run_variation_parent_processors/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/process_instructions/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/process_instructions/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/process_actions/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/process_actions/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/process_actions_results/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/process_actions_results/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/receive/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/receive/',
            'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/create_failed/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/create_failed/',
            'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/update/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/update/',
            'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/update/seller_order_id/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/update/seller_order_id/', 'interval', '3600', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/refund/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/refund/',
            'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/cancel/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/cancel/',
            'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/reserve_cancel/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/reserve_cancel/',
            'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/action/process_update/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/action/process_update/', 'interval', '3600', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/action/process_refund/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/action/process_refund/', 'interval', '18000', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/action/process_cancel/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/action/process_cancel/', 'interval', '18000', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/action/process_results/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/action/process_results/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/repricing/inspect_products/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/repricing/inspect_products/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/repricing/update_settings/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/repricing/update_settings/', 'interval', '180', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/repricing/synchronize_general/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/repricing/synchronize_general/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/repricing/synchronize_actual_price/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/repricing/synchronize_actual_price/', 'interval', '60', 'in seconds'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/listing/product/inspector/amazon/', 'max_allowed_instructions_count', '2000', NULL
        );
        $this->_installer->getMainConfigModifier()->insert('/listing/product/revise/total/amazon/', 'mode', '0', NULL);
        $this->_installer->getMainConfigModifier()->insert(
            '/listing/product/revise/total/amazon/', 'max_allowed_instructions_count', '2000', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/instructions/cron/', 'listings_products_per_one_time', '1000', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/list/',
            'priority_coefficient', '25', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/list/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/list/', 'min_allowed_wait_interval', '3600', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/relist/', 'priority_coefficient', '125', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/relist/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/relist/', 'min_allowed_wait_interval', '1800', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_qty/', 'priority_coefficient', '500', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_qty/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_qty/', 'min_allowed_wait_interval', '900', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_price/', 'priority_coefficient', '250', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_price/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_price/', 'min_allowed_wait_interval', '1800', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_details/', 'priority_coefficient', '50', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_details/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_details/', 'min_allowed_wait_interval', '7200', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_images/', 'priority_coefficient', '50', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_images/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_images/', 'min_allowed_wait_interval', '7200', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/stop/', 'priority_coefficient', '1000', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/stop/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/stop/', 'min_allowed_wait_interval', '600', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/delete/', 'priority_coefficient', '1000', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/delete/', 'wait_increase_coefficient', '100', NULL
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/delete/', 'min_allowed_wait_interval', '600', NULL
        );

        $this->_installer->getMainConfigModifier()->insert(
            NULL, 'environment', 'production', "Available values:\r\nproduction\r\ndevelopment\r\ntesting"
        );

        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/receive/details/', 'mode', '0'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/receive/details/', 'interval', '7200'
        );
    }

    protected function cacheConfig()
    {
        $configTable = $this->_installer->getTable('m2epro_config');
        $cacheConfigTable = $this->_installer->getTable('m2epro_cache_config');

        $oldData = $this->_installer->getConnection()->query(
            "

SELECT * FROM `{$configTable}` WHERE
    `group` = '/view/ebay/advanced/autoaction_popup/' AND `key` = 'shown' OR
    `group` = '/view/ebay/motors_epids_attribute/' AND `key` = 'listing_notification_shown' OR
    `group` = '/view/ebay/multi_currency_marketplace_2/' AND `key` = 'notification_shown' OR
    `group` = '/view/ebay/multi_currency_marketplace_19/' AND `key` = 'notification_shown' OR
    `group` = '/view/requirements/popup/' AND `key` = 'closed'

"
        )->fetchAll();

        $insertParts = array();
        $ids = array();
        foreach ($oldData as $tempRow) {
            $insertParts[] = "(
                '{$tempRow['group']}',
                '{$tempRow['key']}',
                '{$tempRow['value']}',
                '{$tempRow['notice']}',
                '{$tempRow['update_date']}',
                '{$tempRow['create_date']}'
            )";

            $ids[] = $tempRow['id'];
        }

        if (!empty($insertParts)) {
            $insertString = implode(',', $insertParts);
            $insertSql='INSERT INTO `'.$cacheConfigTable.'` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`)
                          VALUES' . $insertString;

            $this->_installer->getConnection()->query($insertSql);

            $idsString = implode(',', $ids);

            $this->_installer->getConnection()->query(
                <<<SQL
DELETE FROM `{$configTable}` WHERE `id` IN ({$idsString});
SQL
            );
        }

        $this->_installer->run(
            <<<SQL

UPDATE `m2epro_cache_config`
SET `group` = '/view/ebay/listing/advanced/autoaction_popup/',
    `key`   = 'shown'
WHERE `group` = '/view/ebay/advanced/autoaction_popup/'
  AND `key`   = 'shown';

UPDATE `m2epro_cache_config`
SET `group` = '/view/ebay/listing/motors_epids_attribute/',
    `key`   = 'notification_shown'
WHERE `group` = '/view/ebay/motors_epids_attribute/'
  AND `key`   = 'listing_notification_shown';

UPDATE `m2epro_cache_config`
SET `group` = '/view/ebay/template/selling_format/multi_currency_marketplace_2/',
    `key`   = 'notification_shown'
WHERE `group` = '/view/ebay/multi_currency_marketplace_2/'
  AND `key`   = 'notification_shown';

UPDATE `m2epro_cache_config`
SET `group` = '/view/ebay/template/selling_format/multi_currency_marketplace_19/',
    `key`   = 'notification_shown'
WHERE `group` = '/view/ebay/multi_currency_marketplace_19/'
  AND `key`   = 'notification_shown';

SQL
        );

        $this->_installer->getCacheConfigModifier()
                         ->delete("/view/ebay/template/selling_format/multi_currency_marketplace_2/");
        $this->_installer->getCacheConfigModifier()
                         ->delete("/view/ebay/template/selling_format/multi_currency_marketplace_19/");
    }

    //########################################
}
