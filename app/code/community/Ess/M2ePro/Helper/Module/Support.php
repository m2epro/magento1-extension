<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 * Shipping method with custom title and price
 */

class Ess_M2ePro_Helper_Module_Support extends Mage_Core_Helper_Abstract
{
    const TYPE_BRONZE  = 'bronze';
    const TYPE_SILVER  = 'silver';
    const TYPE_GOLD    = 'gold';

    //#############################################

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

    //#############################################

    public function getDocumentationUrl($component = NULL, $articleUrl = NULL)
    {
        $urlParts[] = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'documentation_url');
        $urlParts[] = 'display';

        if ($component) {

            if ($component == Ess_M2ePro_Helper_Component_Ebay::NICK) {
                $urlParts[] = 'eBayMagentoV6X';
            } elseif ($component == Ess_M2ePro_Helper_Component_Amazon::NICK) {
                $urlParts[] = 'AmazonMagentoV6X';
            } elseif ($component == Ess_M2ePro_Helper_Component_Buy::NICK) {
                $urlParts[] = 'RakutenMagentoV6X';
            } else {
                throw new Ess_M2ePro_Model_Exception_Logic('Invalid Channel.');
            }
        }

        if ($articleUrl) {
            $urlParts[] = trim($articleUrl, '/');
        }

        return implode('/', $urlParts);
    }

    public function getKnowledgeBaseUrl($articleUrl = NULL)
    {
        $urlParts[] = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'knowledge_base_url');

        if ($articleUrl) {
            $urlParts[] = 'articles';
            $urlParts[] = trim($articleUrl, '/');
        }

        return implode('/', $urlParts);
    }

    public function getVideoTutorialsUrl($component)
    {
        return $this->getDocumentationUrl($component,'Video+Tutorials');
    }

    //#############################################

    public function getMainWebsiteUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'main_website_url');
    }

    public function getClientsPortalBaseUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'clients_portal_url');
    }

    // -------------------------------------------

    public function getMainSupportUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'main_support_url');
    }

    public function getMagentoConnectUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'magento_connect_url');
    }

    //#############################################

    public function getContactEmail()
    {
        $email = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/support/', 'contact_email');

        try {

            $dispatcherObject = Mage::getModel('M2ePro/Connector_M2ePro_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('settings','get','supportEmail');
            $response = $dispatcherObject->process($connectorObj);

            if (!empty($response['email'])) {
                $email = $response['email'];
            }

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }

        return $email;
    }

    public function getType()
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();

        $type = $cacheConfig->getGroupValue('/support/premium/','type');
        $lastUpdateDate = $cacheConfig->getGroupValue('/support/premium/','last_update_time');

        if ($type && strtotime($lastUpdateDate) + 3600*24 > Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
            return $type;
        }

        $type = self::TYPE_BRONZE;

        try {

            $dispatcherObject = Mage::getModel('M2ePro/Connector_M2ePro_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('settings','get','supportType');
            $response = $dispatcherObject->process($connectorObj);

            !empty($response['type']) && $type = $response['type'];

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }

        $cacheConfig->setGroupValue('/support/premium/','type',$type);
        $cacheConfig->setGroupValue('/support/premium/','last_update_time',
                                    Mage::helper('M2ePro')->getCurrentGmtDate());

        return $type;
    }

    //---------------------------------------------

    public function isTypePremium()
    {
        return $this->getType() == self::TYPE_GOLD || $this->getType() == self::TYPE_SILVER;
    }

    //#############################################
}