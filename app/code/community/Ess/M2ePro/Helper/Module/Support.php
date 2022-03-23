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

    public function getClientsPortalUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'clients_portal_url');
    }

    public function getClientsPortalDocumentationUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()
                ->getGroupValue('/support/', 'clients_portal_url') . 'documentation/';
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

    public function getKnowledgeBaseUrl($articleUrl = null)
    {
        $urlParts[] = $this->getSupportUrl()  . '/knowledgebase';

        if ($articleUrl) {
            $urlParts[] = trim($articleUrl, '/');
        }

        return implode('/', $urlParts);
    }

    public function getHowToGuideUrl($articleUrl)
    {
        $urlParts[] = $this->getSupportUrl()  . '/how-to-guide';

        if ($articleUrl) {
            $urlParts[] = trim($articleUrl, '/');
        }

        return implode('/', $urlParts);
    }

    public function getSupportUrl($urlPart = null)
    {
        $urlParts[] = trim(
            Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'support_url'),
            '/'
        );

        if ($urlPart) {
            $urlParts[] = trim($urlPart, '/');
        }

        return implode('/', $urlParts);
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

----- MYSQL INFO -----
{$this->getMysqlInfo()}
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

        $licenseKey = Mage::helper('M2ePro/Module_License')->getKey();
        $installationKey = Mage::helper('M2ePro/Module')->getInstallationKey();

        return <<<INFO
Platform: {$platformInfo['name']} {$platformInfo['edition']} {$platformInfo['version']}
---------------------------
Extension: {$extensionInfo['name']} {$extensionInfo['version']}
---------------------------
License Key: {$licenseKey}
---------------------------
Installation Key: {$installationKey}
---------------------------
INFO;
    }

    public function getLocationInfo()
    {
        $locationInfo = array(
            'domain' => Mage::helper('M2ePro/Client')->getDomain(),
            'ip' => Mage::helper('M2ePro/Client')->getIp(),
            'directory' => Mage::helper('M2ePro/Client')->getBaseDirectory()
        );

        return <<<INFO
Domain: {$locationInfo['domain']}
---------------------------
Ip: {$locationInfo['ip']}
---------------------------
Directory: {$locationInfo['directory']}
---------------------------
INFO;
    }

    public function getPhpInfo()
    {
        $phpInfo = Mage::helper('M2ePro/Client')->getPhpSettings();
        $phpInfo['api'] = Mage::helper('M2ePro/Client')->getPhpApiName();
        $phpInfo['version'] = Mage::helper('M2ePro/Client')->getPhpVersion();
        $phpInfo['ini_file_location'] = Mage::helper('M2ePro/Client')->getPhpIniFileLoaded();

        return <<<INFO
Version: {$phpInfo['version']}
---------------------------
Api: {$phpInfo['api']}
---------------------------
Memory Limit: {$phpInfo['memory_limit']}
---------------------------
Max Execution Time: {$phpInfo['max_execution_time']}
---------------------------
PHP ini file: {$phpInfo['ini_file_location']}
---------------------------
INFO;
    }

    public function getMysqlInfo()
    {
        $mysqlInfo = Mage::helper('M2ePro/Client')->getMysqlSettings();
        $mysqlInfo['api'] = Mage::helper('M2ePro/Client')->getMysqlApiName();
        $prefix = Mage::helper('M2ePro/Magento')->getDatabaseTablesPrefix();
        $mysqlInfo['prefix'] = $prefix != '' ? $prefix : 'Disabled';
        $mysqlInfo['version'] = Mage::helper('M2ePro/Client')->getMysqlVersion();
        $mysqlInfo['database'] = Mage::helper('M2ePro/Magento')->getDatabaseName();

        return <<<INFO
Version: {$mysqlInfo['version']}
---------------------------
Api: {$mysqlInfo['api']}
---------------------------
Database: {$mysqlInfo['database']}
---------------------------
Tables Prefix: {$mysqlInfo['prefix']}
---------------------------
Connection Timeout: {$mysqlInfo['connect_timeout']}
---------------------------
Wait Timeout: {$mysqlInfo['wait_timeout']}
---------------------------
INFO;
    }

    //########################################
}
