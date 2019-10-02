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

    public function getDocumentationUrl($component = NULL, $articleUrl = NULL, $tinyLink = NULL)
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

    public function getKnowledgeBaseUrl($articleUrl = NULL)
    {
        $urlParts[] = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'knowledge_base_url');

        if ($articleUrl) {
            $urlParts[] = trim($articleUrl, '/');
        }

        return implode('/', $urlParts);
    }

    public function getVideoTutorialsUrl($component)
    {
        return $this->getDocumentationUrl($component, 'Video+Tutorials');
    }

    //########################################

    public function getMainWebsiteUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'main_website_url');
    }

    public function getClientsPortalBaseUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'clients_portal_url');
    }

    // ---------------------------------------

    public function getMainSupportUrl($urlPart = null)
    {
        $urlParts[] = trim(
            Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'main_support_url'),
            '/'
        );

        if ($urlPart) {
            $urlParts[] = trim($urlPart, '/');
        }

        return implode('/', $urlParts);
    }

    public function getMagentoConnectUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'magento_connect_url');
    }

    //########################################

    public function getContactEmail()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'contact_email');
    }

    //########################################
}