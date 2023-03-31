<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module_Support extends Mage_Core_Helper_Abstract
{
    //########################################

    public function getPageUrl(array $params = array())
    {
        return Mage::helper('adminhtml')->getUrl($this->getPageRoute(), $params);
    }

    public function getPageRoute()
    {
        return 'M2ePro/'.$this->getPageControllerName().'/index';
    }

    public function getPageControllerName()
    {
        return 'adminhtml_support';
    }

    //########################################

    public function getWebsiteUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'website_url');
    }

    public function getPrivacyPolicyUrl()
    {
        return $this->getWebsiteUrl() . 'privacy';
    }

    public function getTermsAndConditionsUrl()
    {
        return $this->getWebsiteUrl() . 'terms-and-conditions';
    }

    public function getAccountsUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'accounts_url');
    }

    public function getDocumentationUrl($component = null, $articleUrl = null, $tinyLink = null)
    {
        $urlParts[] = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'documentation_url');

        if ($component) {
            $urlParts[] = 'display';
        }

        if ($component) {
            if ($component == Ess_M2ePro_Helper_Component_Ebay::NICK) {
                $urlParts[] = 'eBayMagentoV6X';
            } elseif ($component == Ess_M2ePro_Helper_Component_Amazon::NICK) {
                $urlParts[] = 'AmazonMagentoV6X';
            } elseif ($component == Ess_M2ePro_Helper_Component_Walmart::NICK) {
                $urlParts[] = 'WalmartMagentoV6X';
            } else {
                throw new Ess_M2ePro_Model_Exception_Logic('Invalid Channel.');
            }
        }

        if ($articleUrl) {
            $urlParts[] = trim($articleUrl, '/');
        }

        if ($tinyLink) {
            $urlParts[] = $tinyLink;
        }

        return implode('/', $urlParts);
    }

    public function getSupportUrl($urlPart)
    {
        $baseSupportUrl = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'support_url');
        return rtrim($baseSupportUrl, '/') . '/' . ltrim($urlPart, '/');
    }

    public function getMagentoMarketplaceUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'magento_marketplace_url');
    }

    //########################################

    public function getContactEmail()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'contact_email');
    }

    //########################################

    public function getSummaryInfo()
    {
        return <<<DATA
----- MAIN INFO -----
{$this->getMainInfo()}

---- LOCATION INFO ----
{$this->getLocationInfo()}

----- PHP INFO -----
{$this->getPhpInfo()}
DATA;
    }

    // ---------------------------------------

    public function getMainInfo()
    {
        $platformInfo = array(
            'name'    => Mage::helper('M2ePro/Magento')->getName(),
            'edition' => Mage::helper('M2ePro/Magento')->getEditionName(),
            'version' => Mage::helper('M2ePro/Magento')->getVersion()
        );

        $extensionInfo = array(
            'name'    => Mage::helper('M2ePro/Module')->getName(),
            'version' => Mage::helper('M2ePro/Module')->getPublicVersion()
        );

        return <<<INFO
Platform: {$platformInfo['name']} {$platformInfo['edition']} {$platformInfo['version']}
---------------------------
Extension: {$extensionInfo['name']} {$extensionInfo['version']}
---------------------------
INFO;
    }

    public function getLocationInfo()
    {
        $locationInfo = array(
            'domain' => Mage::helper('M2ePro/Client')->getDomain(),
            'ip' => Mage::helper('M2ePro/Client')->getIp(),
        );

        return <<<INFO
Domain: {$locationInfo['domain']}
---------------------------
Ip: {$locationInfo['ip']}
---------------------------
INFO;
    }

    public function getPhpInfo()
    {
        $phpInfo = Mage::helper('M2ePro/Client')->getPhpSettings();
        $phpInfo['api'] = Mage::helper('M2ePro/Client')->getPhpApiName();
        $phpInfo['version'] = Mage::helper('M2ePro/Client')->getPhpVersion();

        return <<<INFO
Version: {$phpInfo['version']}
---------------------------
Api: {$phpInfo['api']}
---------------------------
Memory Limit: {$phpInfo['memory_limit']}
---------------------------
Max Execution Time: {$phpInfo['max_execution_time']}
---------------------------
INFO;
    }
}
