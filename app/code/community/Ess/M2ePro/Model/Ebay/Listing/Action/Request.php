<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Action_Request
{
    /**
     * @var array
     */
    private $params = array();

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Action_Configurator
     */
    private $configurator = NULL;

    /**
     * @var array
     */
    private $warningMessages = array();

    //########################################

    abstract public function getData();

    //########################################

    public function setParams(array $params = array())
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    protected function getParams()
    {
        return $this->params;
    }

    // ---------------------------------------

    public function setConfigurator(Ess_M2ePro_Model_Ebay_Listing_Action_Configurator $object)
    {
        $this->configurator = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Action_Configurator
     */
    protected function getConfigurator()
    {
        return $this->configurator;
    }

    //########################################

    protected function addWarningMessage($message)
    {
        $this->warningMessages[] = $message;
    }

    /**
     * @return array
     */
    public function getWarningMessages()
    {
        return $this->warningMessages;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    abstract protected function getMarketplace();

    /**
     * @return Ess_M2ePro_Model_Ebay_Marketplace
     */
    protected function getEbayMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Account
     */
    abstract protected function getAccount();

    /**
     * @return Ess_M2ePro_Model_Ebay_Account
     */
    protected function getEbayAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    abstract protected function getMagentoProduct();

    //########################################
}