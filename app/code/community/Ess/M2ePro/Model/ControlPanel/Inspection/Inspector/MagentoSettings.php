<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_MagentoSettings
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface
{
    //########################################

    public function process()
    {
        $issues = array();

        if (!extension_loaded('gd') || !function_exists('gd_info')) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'GD library is not installed.'
            );
        }

        if (Mage_Core_Model_Locale::DEFAULT_TIMEZONE !== 'UTC') {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'Non-default Magento timezone set.',
                Mage_Core_Model_Locale::DEFAULT_TIMEZONE
            );
        }

        if ($modules = $this->getConflictedModules()) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'Has conflicted modules.',
                $modules
            );
        }

        if (defined('COMPILER_INCLUDE_PATH')) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'Compilation is enabled.'
            );
        }

        if (Mage::helper('M2ePro/Client_Cache')->isWrongCacheConfiguration()) {
            $url = Mage::helper('adminhtml')->getUrl('*/adminhtml_controlPanel_inspection/cacheSettings');
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'Wrong cache configuration.',
                <<<HTML
<a href="{$url}" target="_blank">show Settings</a>
HTML
            );
        }

        if (Mage::helper('M2ePro/Client_Cache')->isApcEnabled()) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'APC Cache is enabled.'
            );
        }

        if (Mage::helper('M2ePro/Client_Cache')->isMemchachedEnabled()) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'Memchached Cache is enabled.'
            );
        }

        if (Mage::helper('M2ePro/Client_Cache')->isRedisEnabled()) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'Redis Cache is enabled.'
            );
        }

        return $issues;
    }

    //########################################

    protected function getConflictedModules()
    {
        $conflictedModules = array(
            '/TBT_Enhancedgrid/i' => '',
            '/warp/i' => '',
            '/Auctionmaid_/i' => '',

            '/Exactor_Tax/i' => '',
            '/Exactory_Core/i' => '',
            '/Exactor_ExactorSettings/i' => '',
            '/Exactor_Sales/i' => '',
            '/Aoe_AsyncCache/i' => '',
            '/Idev_OneStepCheckout/i' => '',

            '/Mercent_Sales/i' => '',
            '/Webtex_Fba/i' => 'Breaks creation Amazon Fba orders.',

            '/MW_FreeGift/i' => 'last item in combined amazon orders has zero price (sales_quote_product_add_after)',

            '/Unirgy_Dropship/i' => 'Rewrites stock item and in some cases return always in stock for all products',

            '/Aitoc_Aitquantitymanager/i' => 'Stock management conflicts. Wrong statuses, "In\OUT Stock". During Auto.',

            '/Eternalsoft_Ajaxcart/i' => 'Broke some ajax responses.',
            '/Amasty_Shiprestriction/i' => '"Please specify a shipping method" error for some orders.',
            '/RicoNeitzel_PaymentFilter/i' => '"The requested payment method is not available" error',
            '/Mxperts_NoRegion/i' => 'Error about empty billing address information',
            '/MageWorx_DeliveryZone/i' => 'Shipping price is 0 in magento order',

            '/Netzarbeiter_Cache/i' => 'Adding product step by circle.',

            '/Netzarbeiter_LoginCatalog/i' => 'Cron problem. [Model_Observer->_redirectToLoginPage()]',
            '/Elsner_Loginonly/i'          => 'Cron problem. [Model_Observer->_redirectToLoginPage()]'
        );

        $result = array();
        $modules = Mage::getConfig()->getNode('modules')->asArray();

        foreach ($conflictedModules as $expression => $description) {
            foreach ($modules as $module => $data) {
                if (!preg_match($expression, $module)) {
                    continue;
                }

                $version  = isset($data['version']) ? $data['version'] : '';
                $codePool = isset($data['codePool']) ? $data['codePool'] : '';
                $active = !empty($data['active']) ? 'active' : 'not-active';

                $html = <<<HTML
<b>{$module}</b> <span>{$codePool} {$version} {$active}</span>
HTML;
                if ($description) {
                    $html .= <<<HTML
<br><span style="color: grey;">{$description}</span>
HTML;
                }

                $result[] = $html;
            }
        }

        return $result;
    }

    //########################################
}