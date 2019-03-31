<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Validator
{
    /**
     * @var array
     */
    private $params = array();

    /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator $configurator */
    private $configurator = NULL;

    /**
     * @var array
     */
    private $messages = array();

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProduct = NULL;

    //########################################

    /**
     * @param array $params
     */
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

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator $configurator
     * @return $this
     */
    public function setConfigurator(Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator $configurator)
    {
        $this->configurator = $configurator;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
     */
    protected function getConfigurator()
    {
        return $this->configurator;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return $this
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->listingProduct = $listingProduct;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->listingProduct;
    }

    //########################################

    abstract public function validate();

    //########################################

    protected function addMessage($message, $type = Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR)
    {
        $this->messages[] = array(
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
        return $this->messages;
    }

    // ---------------------------------------

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

    /**
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getListingProduct()->getMarketplace();
    }

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
    protected function getAccount()
    {
        return $this->getListing()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Account
     */
    protected function getEbayAccount()
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
     * @return Ess_M2ePro_Model_Ebay_Listing
     */
    protected function getEbayListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product
     */
    protected function getEbayListingProduct()
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

    //########################################

    protected function validateCategory()
    {
        if (!$this->getEbayListingProduct()->isSetCategoryTemplate()) {

            // M2ePro_TRANSLATIONS
            // Categories Settings are not set
            $this->addMessage('Categories Settings are not set');

            return false;
        }

        return true;
    }

    // ---------------------------------------

    protected function validatePrice()
    {
        if ($this->getEbayListingProduct()->isVariationsReady()) {

            if (!$this->validateVariationsFixedPrice()) {
                return false;
            }

            return true;
        }

        if ($this->getEbayListingProduct()->isListingTypeAuction()) {

            if (!$this->validateStartPrice()) {
                return false;
            }

            if (!$this->validateReservePrice()) {
                return false;
            }

            if (!$this->validateBuyItNowPrice()) {
                return false;
            }

            return true;

        } else {

            if (!$this->validateFixedPrice()) {
                return false;
            }
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
        if ($qty <= 0) {

            if (isset($this->params['status_changer']) &&
                $this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {

                // M2ePro_TRANSLATIONS
                // 'You are submitting an Item with zero quantity. It contradicts eBay requirements. Please apply the Stop Action instead.'
                $message = 'You are submitting an Item with zero quantity. It contradicts eBay requirements.';

                if ($this->getListingProduct()->isStoppable()) {
                    $message .= ' Please apply the Stop Action instead.';
                }

                $this->addMessage($message);
            } else {
                // M2ePro_TRANSLATIONS
                // 'Cannot submit an Item with zero quantity. It contradicts eBay requirements. This action has been generated automatically based on your Synchronization Rule settings. The error occurs when the Stop Rules are not properly configured or disabled. Please review your settings.'
                $message = 'Cannot submit an Item with zero quantity. It contradicts eBay requirements.
                            This action has been generated automatically based on your Synchronization Rule settings. ';

                if ($this->getListingProduct()->isStoppable()) {
                    $message .= 'The error occurs when the Stop Rules are not properly configured or disabled. ';
                }

                $message .= 'Please review your settings.';

                $this->addMessage($message);
            }

            return false;
        }

        $this->data['qty'] = $qty;

        return true;
    }

    // ---------------------------------------

    protected function validateIsVariationProductWithoutVariations()
    {
        if ($this->getEbayListingProduct()->isVariationMode() &&
            !$this->getEbayListingProduct()->isVariationsReady())
        {
            // M2ePro_TRANSLATIONS
            // M2E Pro identifies this Product as a Variational one. But no Variations can be obtained from it. The problem could be related to the fact that Product Variations are not assigned to Magento Store View your M2E Pro Listing is created for. In order to be processed, the Product data should be available within Website that M2E Pro appeals to. Another possible reason is an impact of the external plugins. The 3rd party tools override Magento core functionality, therefore, prevent M2E Pro from processing the Product data correctly. Make sure you have selected an appropriate Website in each Associated Product and no 3rd party extension overrides your settings. Otherwise, contact M2E Pro Support Team to resolve the issue.
            $this->addMessage(
                'M2E Pro identifies this Product as a Variational one. But no Variations can be obtained from it.
                The problem could be related to the fact that Product Variations are not assigned to Magento Store
                View your M2E Pro Listing is created for. In order to be processed, the Product data should be
                available within Website that M2E Pro appeals to.
                Another possible reason is an impact of the external plugins. The 3rd party tools override
                Magento core functionality, therefore, prevent M2E Pro from processing the Product data correctly.
                Make sure you have selected an appropriate Website in each Associated Product and no 3rd party
                extension overrides your settings. Otherwise, contact M2E Pro Support Team to resolve the issue.'
            );

            return false;
        }

        return true;
    }

    protected function validateVariationsOptions()
    {
        $totalVariationsCount = 0;
        $totalDeletedVariationsCount = 0;
        $uniqueAttributesValues = array();

        foreach ($this->getEbayListingProduct()->getVariations(true) as $variation) {
            /** @var Ess_M2ePro_Model_Listing_Product_Variation $variation */
            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */

            $ebayVariation = $variation->getChildObject();

            foreach ($variation->getOptions(true) as $option) {
                /** @var Ess_M2ePro_Model_Listing_Product_Variation_Option $option */

                $uniqueAttributesValues[$option->getAttribute()][$option->getOption()] = true;

                // Max 5 pair attribute-option:
                // Color: Blue, Size: XL, ...
                if (count($uniqueAttributesValues) > 5) {

                    // M2ePro_TRANSLATIONS
                    // Variations of this Magento Product are out of the eBay Variational Item limits. Its number of Variational Attributes is more than 5. That is why, this Product cannot be updated on eBay. Please, decrease the number of Attributes to solve this issue.
                    $this->addMessage(
                        'Variations of this Magento Product are out of the eBay Variational Item limits.
                        Its number of Variational Attributes is more than 5.
                        That is why, this Product cannot be updated on eBay.
                        Please, decrease the number of Attributes to solve this issue.'
                    );
                    return false;
                }

                // Maximum 60 options by one attribute:
                // Color: Red, Blue, Green, ...
                if (count($uniqueAttributesValues[$option->getAttribute()]) > 60) {

                    // M2ePro_TRANSLATIONS
                    // Variations of this Magento Product are out of the eBay Variational Item limits. Its number of Options for some Variational Attribute(s) is more than 60. That is why, this Product cannot be updated on eBay. Please, decrease the number of Options to solve this issue.
                    $this->addMessage(
                        'Variations of this Magento Product are out of the eBay Variational Item limits.
                        Its number of Options for some Variational Attribute(s) is more than 60.
                        That is why, this Product cannot be updated on eBay.
                        Please, decrease the number of Options to solve this issue.'
                    );
                    return false;
                }
            }

            $totalVariationsCount++;
            $ebayVariation->isDelete() && $totalDeletedVariationsCount++;

            // Not more that 250 possible variations
            if ($totalVariationsCount > 250) {

                // M2ePro_TRANSLATIONS
                // Variations of this Magento Product are out of the eBay Variational Item limits. The Number of Variations is more than 250. That is why, this Product cannot be updated on eBay. Please, decrease the number of Variations to solve this issue.
                $this->addMessage(
                    'Variations of this Magento Product are out of the eBay Variational Item limits.
                    The Number of Variations is more than 250. That is why, this Product cannot be updated on eBay.
                    Please, decrease the number of Variations to solve this issue.'
                );
                return false;
            }
        }

        if ($totalVariationsCount == $totalDeletedVariationsCount) {

            // M2ePro_TRANSLATIONS
            // This Product was listed to eBay as Variational Item. Changing of the Item type from Variational to Non-Variational during Revise/Relist actions is restricted by eBay. At the moment this Product is considered as Simple without any Variations, that does not allow updating eBay Variational Item.
            $this->addMessage(
                'This Product was listed to eBay as Variational Item.
                Changing of the Item type from Variational to Non-Variational during Revise/Relist
                actions is restricted by eBay.
                At the moment this Product is considered as Simple without any Variations,
                that does not allow updating eBay Variational Item.'
            );
            return false;
        }

        return true;
    }

    protected function validateVariationsFixedPrice()
    {
        if (!$this->getConfigurator()->isPriceAllowed() ||
            !$this->getEbayListingProduct()->isListingTypeFixed() ||
            !$this->getEbayListingProduct()->isVariationsReady()
        ) {
            return true;
        }

        foreach ($this->getEbayListingProduct()->getVariations(true) as $variation) {
            /** @var Ess_M2ePro_Model_Listing_Product_Variation $variation */

            if ($variation->getChildObject()->isDelete()) {
                continue;
            }

            if (isset($this->data['variation_fixed_price_'.$variation->getId()])) {
                $variationPrice = $this->data['variation_fixed_price_'.$variation->getId()];
            } else {
                $variationPrice = $variation->getChildObject()->getPrice();
            }

            if ($variationPrice < 0.99) {

                // M2ePro_TRANSLATIONS
                // The Fixed Price must be greater than 0.99. Please, check the Selling Policy and Product Settings.
                $this->addMessage(
                    'The Fixed Price must be greater than 0.99. Please, check the Selling Policy and Product Settings.'
                );

                return false;
            }

            $this->data['variation_fixed_price_'.$variation->getId()] = $variationPrice;
        }

        return true;
    }

    protected function validateFixedPrice()
    {
        if (!$this->getConfigurator()->isPriceAllowed() ||
            !$this->getEbayListingProduct()->isListingTypeFixed() ||
            $this->getEbayListingProduct()->isVariationsReady()
        ) {
            return true;
        }

        $price = $this->getFixedPrice();
        if ($price < 0.99) {

            // M2ePro_TRANSLATIONS
            // The Fixed Price must be greater than 0.99. Please, check the Selling Policy and Product Settings.
            $this->addMessage(
                'The Fixed Price must be greater than 0.99. Please, check the Selling Policy and Product Settings.'
            );

            return false;
        }

        $this->data['price_fixed'] = $price;

        return true;
    }

    protected function validateStartPrice()
    {
        if (!$this->getConfigurator()->isPriceAllowed() || !$this->getEbayListingProduct()->isListingTypeAuction()) {
            return true;
        }

        $price = $this->getStartPrice();
        if ($price < 0.99) {

            // M2ePro_TRANSLATIONS
            // The Start Price must be greater than 0.99. Please, check the Selling Policy and Product Settings.
            $this->addMessage(
                'The Start Price must be greater than 0.99. Please, check the Selling Policy and Product Settings.'
            );

            return false;
        }

        $this->data['price_start'] = $price;

        return true;
    }

    protected function validateReservePrice()
    {
        if (!$this->getConfigurator()->isPriceAllowed() || !$this->getEbayListingProduct()->isListingTypeAuction()) {
            return true;
        }

        if ($this->getEbayListingProduct()->getEbaySellingFormatTemplate()->isReservePriceModeNone()) {
            return true;
        }

        $price = $this->getReservePrice();
        if ($price < 0.99) {

            // M2ePro_TRANSLATIONS
            // The Reserve Price must be greater than 0.99. Please, check the Selling Policy and Product Settings.
            $this->addMessage(
                'The Reserve Price must be greater than 0.99. Please, check the Selling Policy and Product Settings.'
            );

            return false;
        }

        $this->data['price_reserve'] = $price;

        return true;
    }

    protected function validateBuyItNowPrice()
    {
        if (!$this->getConfigurator()->isPriceAllowed() || !$this->getEbayListingProduct()->isListingTypeAuction()) {
            return true;
        }

        if ($this->getEbayListingProduct()->getEbaySellingFormatTemplate()->isBuyItNowPriceModeNone()) {
            return true;
        }

        $price = $this->getBuyItNowPrice();
        if ($price < 0.99) {

            // M2ePro_TRANSLATIONS
            // The Buy It Now Price must be greater than 0.99. Please, check the Selling Policy and Product Settings.
            $this->addMessage(
                'The Buy It Now Price must be greater than 0.99.
                 Please, check the Selling Policy and Product Settings.'
            );

            return false;
        }

        $this->data['price_buyitnow'] = $price;

        return true;
    }

    //########################################

    protected function getQty()
    {
        if (isset($this->data['qty'])) {
            return $this->data['qty'];
        }

        return $this->getEbayListingProduct()->getQty();
    }

    protected function getFixedPrice()
    {
        if (isset($this->data['price_fixed'])) {
            return $this->data['price_fixed'];
        }

        return $this->getEbayListingProduct()->getFixedPrice();
    }

    protected function getStartPrice()
    {
        if (!empty($this->data['price_start'])) {
            return $this->data['price_start'];
        }

        return $this->getEbayListingProduct()->getStartPrice();
    }

    protected function getReservePrice()
    {
        if (!empty($this->data['price_reserve'])) {
            return $this->data['price_reserve'];
        }

        return $this->getEbayListingProduct()->getReservePrice();
    }

    protected function getBuyItNowPrice()
    {
        if (!empty($this->data['price_buyitnow'])) {
            return $this->data['price_buyitnow'];
        }

        return $this->getEbayListingProduct()->getBuyItNowPrice();
    }

    //########################################
}