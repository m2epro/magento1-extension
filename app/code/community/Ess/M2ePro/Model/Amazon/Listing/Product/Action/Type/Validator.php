<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Validator
{
    /**
     * @var array
     */
    private $params = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProduct = NULL;

    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator $configurator */
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

    public function setConfigurator(Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator $configurator)
    {
        $this->configurator = $configurator;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator
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
        $this->getAmazonAccount()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Marketplace
     */
    protected function getAmazonMarketplace()
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
     * @return Ess_M2ePro_Model_Amazon_Account
     */
    protected function getAmazonAccount()
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
     * @return Ess_M2ePro_Model_Amazon_Listing
     */
    protected function getAmazonListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product
     */
    protected function getAmazonListingProduct()
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
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager
     */
    protected function getVariationManager()
    {
        return $this->getAmazonListingProduct()->getVariationManager();
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
        if (!$this->getAmazonListingProduct()->getSku()) {

            // M2ePro_TRANSLATIONS
            // You have to list Item first.
            $this->addMessage('You have to list Item first.');

            return false;
        }

        return true;
    }

    // ----------------------------------------

    protected function validateBlocked()
    {
        if ($this->getListingProduct()->isBlocked()) {

// M2ePro_TRANSLATIONS
// The Action can not be executed as the Item was Closed, Incomplete or Blocked on Amazon. Please, go to Amazon Seller Central and activate the Item. After the next Synchronization the Item will be available.
            $this->addMessage(
                'The Action can not be executed as the Item was Closed, Incomplete or Blocked on Amazon.
                 Please, go to Amazon Seller Central and activate the Item.
                 After the next Synchronization the Item will be available.'
            );

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

    // ----------------------------------------

    protected function validateLogicalUnit()
    {
        if (!$this->getVariationManager()->isLogicalUnit()) {

            // M2ePro_TRANSLATIONS
            // Only logical Products can be processed.
            $this->addMessage('Only logical Products can be processed.');

            return false;
        }

        return true;
    }

    // ----------------------------------------

    protected function validateParentListingProductFlags()
    {
        if ($this->getListingProduct()->getData('no_child_for_processing')) {
// M2ePro_TRANSLATIONS
// This Parent has no Child Products on which the chosen Action can be performed.
            $this->addMessage('This Parent has no Child Products on which the chosen Action can be performed.');
            return false;
        }
// M2ePro_TRANSLATIONS
// This Action cannot be fully performed because there are different actions in progress on some Child Products
        if ($this->getListingProduct()->getData('child_locked')) {
            $this->addMessage('This Action cannot be fully performed because there are
                                different Actions in progress on some Child Products');
            return false;
        }

        return true;
    }

    // ----------------------------------------

    protected function validatePhysicalUnitAndSimple()
    {
        if (!$this->getVariationManager()->isPhysicalUnit() && !$this->getVariationManager()->isSimpleType()) {

            // M2ePro_TRANSLATIONS
            // Only physical Products can be processed.
            $this->addMessage('Only physical Products can be processed.');

            return false;
        }

        return true;
    }

    protected function validatePhysicalUnitMatching()
    {
        if (!$this->getVariationManager()->getTypeModel()->isVariationProductMatched()) {

            // M2ePro_TRANSLATIONS
            // You have to select Magento Variation.
            $this->addMessage('You have to select Magento Variation.');

            return false;
        }

        if ($this->getVariationManager()->isIndividualType()) {
            return true;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
        $typeModel = $this->getVariationManager()->getTypeModel();

        if (!$this->getAmazonListingProduct()->isGeneralIdOwner() && !$typeModel->isVariationChannelMatched()) {

            // M2ePro_TRANSLATIONS
            // You have to select Channel Variation.
            $this->addMessage('You have to select Channel Variation.');

            return false;
        }

        return true;
    }

    // ########################################

    protected function getPrice()
    {
        if (isset($this->data['price'])) {
            return $this->data['price'];
        }

        return $this->getAmazonListingProduct()->getPrice();
    }

    protected function getQty()
    {
        if (isset($this->data['qty'])) {
            return $this->data['qty'];
        }

        return $this->getAmazonListingProduct()->getQty();
    }

    // ########################################
}