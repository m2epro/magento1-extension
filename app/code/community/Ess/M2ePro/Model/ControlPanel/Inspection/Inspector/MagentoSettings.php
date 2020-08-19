<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_MagentoSettings
    extends Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface
{
    //########################################

    public function getTitle()
    {
        return 'Magento settings';
    }

    public function getDescription()
    {
        return <<<HTML
- Non-default Magento timezone set<br>
- GD library is installed<br>
- Has conflicted modules<br>
- Compilation is enabled<br>
- Wrong cache configuration<br>
- [APC|Memchached|Redis] Cache is enabled<br>
HTML;
    }

    public function getGroup()
    {
        return Ess_M2ePro_Model_ControlPanel_Inspection_Manager::GROUP_STRUCTURE;
    }

    public function getExecutionSpeed()
    {
        return Ess_M2ePro_Model_ControlPanel_Inspection_Manager::EXECUTION_SPEED_FAST;
    }

    //########################################

    public function process()
    {
        $issues = array();

        if (!extension_loaded('gd') || !function_exists('gd_info')) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createError(
                $this,
                'GD library is not installed.'
            );
        }

        if (Mage_Core_Model_Locale::DEFAULT_TIMEZONE !== 'UTC') {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createError(
                $this,
                'Non-default Magento timezone set.',
                Mage_Core_Model_Locale::DEFAULT_TIMEZONE
            );
        }

        if ($modules = $this->getConflictedModules()) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createError(
                $this,
                'Has conflicted modules.',
                $modules
            );
        }

        if (defined('COMPILER_INCLUDE_PATH')) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createNotice(
                $this,
                'Compilation is enabled.'
            );
        }

        if (Mage::helper('M2ePro/Client_Cache')->isWrongCacheConfiguration()) {
            $url = Mage::helper('adminhtml')->getUrl('*/adminhtml_controlPanel_inspection/cacheSettings');
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createError(
                $this,
                'Wrong cache configuration.',
                <<<HTML
<a href="{$url}" target="_blank">show Settings</a>
HTML
            );
        }

        if (Mage::helper('M2ePro/Client_Cache')->isApcEnabled()) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createNotice(
                $this,
                'APC Cache is enabled.'
            );
        }

        if (Mage::helper('M2ePro/Client_Cache')->isMemchachedEnabled()) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createNotice(
                $this,
                'Memchached Cache is enabled.'
            );
        }

        if (Mage::helper('M2ePro/Client_Cache')->isRedisEnabled()) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createNotice(
                $this,
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