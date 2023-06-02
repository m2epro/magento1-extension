<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Validator
{
    /**
     * @var array
     */
    protected $_params = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $_listingProduct = null;

    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator $_configurator */
    protected $_configurator = null;

    /**
     * @var array
     */
    protected $_messages = array();

    /**
     * @var array
     */
    protected $_data = array();

    //########################################

    /**
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
    }

    /**
     * @return array
     */
    protected function getParams()
    {
        return $this->_params;
    }

    protected function isChangerUser()
    {
        $params = $this->getParams();
        if (!array_key_exists('status_changer', $params)) {
            return false;
        }

        return (int)$params['status_changer'] === Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->_listingProduct = $listingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->_listingProduct;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator $configurator
     * @return $this
     */
    public function setConfigurator(Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator $configurator)
    {
        $this->_configurator = $configurator;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator
     */
    protected function getConfigurator()
    {
        return $this->_configurator;
    }

    //########################################

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

    // ---------------------------------------

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

    // ---------------------------------------

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

    // ---------------------------------------

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

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager
     */
    protected function getVariationManager()
    {
        return $this->getAmazonListingProduct()->getVariationManager();
    }

    //########################################

    abstract public function validate();

    protected function addMessage($message, $type = Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR)
    {
        $this->_messages[] = array(
            'text' => $message,
            'type' => $type,
        );
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    // ---------------------------------------

    /**
     * @param $key
     * @return array
     */
    public function getData($key = null)
    {
        if ($key === null) {
            return $this->_data;
        }

        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    //########################################

    protected function validateSku()
    {
        if (!$this->getAmazonListingProduct()->getSku()) {
            $this->addMessage('You have to list Item first.');
            return false;
        }

        return true;
    }

    // ---------------------------------------

    protected function validateBlocked()
    {
        if ($this->isChangerUser()) {
            return true;
        }

        if ($this->getListingProduct()->isBlocked()) {
            $this->addMessage(
                'The Action can not be executed as the Item was Closed, Incomplete or Blocked on Amazon.
                 Please, go to Amazon Seller Central and activate the Item.
                 After the next Synchronization the Item will be available.'
            );

            return false;
        }

        return true;
    }

    // ---------------------------------------

    protected function validateQty()
    {
        if (!$this->getConfigurator()->isQtyAllowed()) {
            return true;
        }

        $qty = $this->getQty();
        $clearQty = $this->getClearQty();

        if ($clearQty > 0 && $qty <= 0) {
            $message = 'You’re submitting an item with QTY contradicting the QTY settings in your Selling Policy. 
            Please check Minimum Quantity to Be Listed and Quantity Percentage options.';

            $this->addMessage($message);

            return false;
        }

        if ($qty <= 0) {
            if (isset($this->_params['status_changer']) &&
                $this->_params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
                $message = 'You are submitting an Item with zero quantity. It contradicts Amazon requirements.';

                if ($this->getListingProduct()->isStoppable()) {
                    $message .= ' Please apply the Stop Action instead.';
                }

                $this->addMessage($message);
            } else {
                $message = 'Cannot submit an Item with zero quantity. It contradicts Amazon requirements.
                            This action has been generated automatically based on your Synchronization Rule settings. ';

                if ($this->getListingProduct()->isStoppable()) {
                    $message .= 'The error occurs when the Stop Rules are not properly configured or disabled. ';
                }

                $message .= 'Please review your settings.';

                $this->addMessage($message);
            }

            return false;
        }

        $this->_data['qty'] = $qty;
        $this->_data['clear_qty'] = $clearQty;

        return true;
    }

    protected function validateRegularPrice()
    {
        if (!$this->getConfigurator()->isRegularPriceAllowed()) {
            return true;
        }

        if (!$this->getAmazonListingProduct()->isAllowedForRegularCustomers()) {
            $this->getConfigurator()->disallowRegularPrice();

            if ($this->getAmazonListingProduct()->getOnlineRegularPrice()) {
                $this->addMessage(
                    'B2C Price can not be disabled by Revise/Relist action due to Amazon restrictions.
                    Both B2C and B2B Price values will be available on the Channel.',
                    Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
                );
            }

            return true;
        }

        if ($this->getAmazonListingProduct()->isRepricingManaged()) {
            $this->getConfigurator()->disallowRegularPrice();

            $this->addMessage(
                'Price of this Product is managed by Amazon Repricer, it isn\'t updated by M2E Pro.',
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_NOTICE
            );

            return true;
        }

        $regularPrice = $this->getRegularPrice();
        if ($regularPrice <= 0) {
            $this->addMessage(
                'The Price must be greater than 0. Please, check the Selling Policy and Product Settings.'
            );
            return false;
        }

        $this->_data['regular_price'] = $regularPrice;

        return true;
    }

    protected function validateBusinessPrice()
    {
        if (!$this->getConfigurator()->isBusinessPriceAllowed()) {
            return true;
        }

        if (!$this->getAmazonListingProduct()->isAllowedForBusinessCustomers()) {
            $this->getConfigurator()->disallowBusinessPrice();

            if ($this->getAmazonListingProduct()->getOnlineBusinessPrice()) {
                $this->addMessage(
                    'B2B Price can not be disabled by Revise/Relist action due to Amazon restrictions.
                    Both B2B and B2C Price values will be available on the Channel.',
                    Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
                );
            }

            return true;
        }

        $businessPrice = $this->getBusinessPrice();
        if ($businessPrice <= 0) {
            $this->addMessage(
                'The Business Price must be greater than 0. Please, check the Selling Policy and Product Settings.'
            );
            return false;
        }

        $this->_data['business_price'] = $businessPrice;

        return true;
    }

    // ---------------------------------------

    protected function validateParentListingProduct()
    {
        if ($this->getListingProduct()->getData('no_child_for_processing')) {
            $this->addMessage('This Parent has no Child Products on which the chosen Action can be performed.');
            return false;
        }

        if ($this->getListingProduct()->getData('child_locked')) {
            $this->addMessage(
                'This Action cannot be fully performed because there are
                                different Actions in progress on some Child Products'
            );
            return false;
        }

        return true;
    }

    // ---------------------------------------

    protected function validatePhysicalUnitAndSimple()
    {
        if (!$this->getVariationManager()->isPhysicalUnit() && !$this->getVariationManager()->isSimpleType()) {
            $this->addMessage('Only physical Products can be processed.');
            return false;
        }

        return true;
    }

    protected function validatePhysicalUnitMatching()
    {
        if (!$this->getVariationManager()->getTypeModel()->isVariationProductMatched()) {
            $this->addMessage('You have to select Magento Variation.');
            return false;
        }

        if ($this->getVariationManager()->isIndividualType()) {
            return true;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
        $typeModel = $this->getVariationManager()->getTypeModel();

        if (!$this->getAmazonListingProduct()->isGeneralIdOwner() && !$typeModel->isVariationChannelMatched()) {
            $this->addMessage('You have to select Channel Variation.');
            return false;
        }

        return true;
    }

    //########################################

    protected function getRegularPrice()
    {
        if (isset($this->_data['regular_price'])) {
            return $this->_data['regular_price'];
        }

        return $this->getAmazonListingProduct()->getRegularPrice();
    }

    protected function getBusinessPrice()
    {
        if (isset($this->_data['business_price'])) {
            return $this->_data['business_price'];
        }

        return $this->getAmazonListingProduct()->getBusinessPrice();
    }

    protected function getQty()
    {
        if (isset($this->_data['qty'])) {
            return $this->_data['qty'];
        }

        return $this->getAmazonListingProduct()->getQty();
    }

    protected function getClearQty()
    {
        if (isset($this->_data['clear_qty'])) {
            return $this->_data['clear_qty'];
        }

        return $this->getAmazonListingProduct()->getQty(true);
    }

    //########################################
}
