<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Validator
{
    /**
     * @var array
     */
    private $params = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProduct = NULL;

    /** @var Ess_M2ePro_Model_Buy_Listing_Product_Action_Configurator $configurator */
    private $configurator = NULL;

    /**
     * @var array
     */
    private $messages = array();

    /**
     * @var array
     */
    protected $data = array();

    // ########################################

    public function setParams(array $params)
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

    // ----------------------------------------

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->listingProduct = $listingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->listingProduct;
    }

    // ----------------------------------------

    public function setConfigurator(Ess_M2ePro_Model_Buy_Listing_Product_Action_Configurator $configurator)
    {
        $this->configurator = $configurator;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Listing_Product_Action_Configurator
     */
    protected function getConfigurator()
    {
        return $this->configurator;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return Mage::helper('M2ePro/Component_Buy')->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Marketplace
     */
    protected function getBuyMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getListing()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Account
     */
    protected function getBuyAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    protected function getListing()
    {
        return $this->getListingProduct()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Listing
     */
    protected function getBuyListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Buy_Listing_Product
     */
    protected function getBuyListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    protected function getMagentoProduct()
    {
        return $this->getListingProduct()->getMagentoProduct();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Buy_Listing_Product_Variation_Manager
     */
    protected function getVariationManager()
    {
        return $this->getBuyListingProduct()->getVariationManager();
    }

    // ########################################

    abstract public function validate();

    protected function addMessage($message, $type = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR)
    {
        $this->messages[] = array(
            'text' => $message,
            'type' => $type,
        );
    }

    // ----------------------------------------

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    // ----------------------------------------

    /**
     * @param $key
     * @return array
     */
    public function getData($key = null)
    {
        if (is_null($key)) {
            return $this->data;
        }

        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    // ########################################

    protected function validateSku()
    {
        if (!$this->getBuyListingProduct()->getSku()) {

            // M2ePro_TRANSLATIONS
            // You have to list Item first.
            $this->addMessage('You have to list Item first.');

            return false;
        }

        return true;
    }

    // ########################################

    protected function validateVariationProductMatching()
    {
        if (!$this->getVariationManager()->isVariationProductMatched()) {
            // M2ePro_TRANSLATIONS
            // You have to select Magento Variation.
            $this->addMessage('You have to select Magento Variation.');

            return false;
        }

        return true;
    }

    // ----------------------------------------

    protected function validateQty()
    {
        if (!$this->getConfigurator()->isQtyAllowed()) {
            return true;
        }

        $qty = $this->getQty();
        if ($qty <= 0) {

            // M2ePro_TRANSLATIONS
            // The Quantity must be greater than 0. Please, check the Selling Format Policy and Product Settings.
            $this->addMessage(
                'The Quantity must be greater than 0. Please, check the Selling Format Policy and Product Settings.'
            );

            return false;
        }

        $this->data['qty'] = $qty;

        return true;
    }

    protected function validatePrice()
    {
        if (!$this->getConfigurator()->isPriceAllowed()) {
            return true;
        }

        $price = $this->getPrice();
        if ($price <= 0) {

            // M2ePro_TRANSLATIONS
            // The Price must be greater than 0. Please, check the Selling Format Policy and Product Settings.
            $this->addMessage(
                'The Price must be greater than 0. Please, check the Selling Format Policy and Product Settings.'
            );

            return false;
        }

        $this->data['price'] = $price;

        return true;
    }

    // ########################################

    protected function getPrice()
    {
        if (isset($this->data['price'])) {
            return $this->data['price'];
        }

        return $this->getBuyListingProduct()->getPrice();
    }

    protected function getQty()
    {
        if (isset($this->data['qty'])) {
            return $this->data['qty'];
        }

        return $this->getBuyListingProduct()->getQty();
    }

    protected function getCondition()
    {
        if (isset($this->data['condition'])) {
            return $this->data['condition'];
        }

        return $this->getBuyListingProduct()->getListingSource()->getCondition();
    }

    // ########################################
}