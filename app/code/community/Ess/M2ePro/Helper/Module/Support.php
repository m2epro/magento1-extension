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

    public function getDocumentationUrl($component = null, $articleUrl = null, $tinyLink = null)
    {
        $urlParts[] = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'documentation_url');

        if ($component || $articleUrl) {
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
        $urlParts[] = $this->getSupportUrl();

        if ($articleUrl) {
            $urlParts[] = trim($articleUrl, '/');
        }

        return implode('/', $urlParts);
    }

    public function getIdeasUrl()
    {
        return $this->getSupportUrl() . 'ideas/';
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
}