<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Validator
{
    /**
     * @var array
     */
    private $params = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProduct = NULL;

    /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator $configurator */
    private $configurator = NULL;

    /**
     * @var array
     */
    private $messages = array();

    /**
     * @var array
     */
    protected $data = array();

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
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     */
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

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator $configurator
     * @return $this
     */
    public function setConfigurator(Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator $configurator)
    {
        $this->configurator = $configurator;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator
     */
    protected function getConfigurator()
    {
        return $this->configurator;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        $this->getWalmartAccount()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Marketplace
     */
    protected function getWalmartMarketplace()
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
     * @return Ess_M2ePro_Model_Walmart_Account
     */
    protected function getWalmartAccount()
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
     * @return Ess_M2ePro_Model_Walmart_Listing
     */
    protected function getWalmartListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product
     */
    protected function getWalmartListingProduct()
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
     * @return Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager
     */
    protected function getVariationManager()
    {
        return $this->getWalmartListingProduct()->getVariationManager();
    }

    //########################################

    abstract public function validate();

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

    protected function validateSku()
    {
        if (!$this->getWalmartListingProduct()->getSku()) {

            // M2ePro_TRANSLATIONS
            // You have to list Item first.
            $this->addMessage('You have to list Item first.');
            return false;
        }

        $params = $this->getParams();
        if (isset($params['changed_sku'])) {

            if (preg_match('/[.\s-]+/', $params['changed_sku'])) {

                $this->addMessage(
                    'Item SKU was not updated because it contains special characters,
                    i.e. hyphen (-), space ( ), and period (.), that are not allowed by Walmart.
                    Please enter SKU in a correct format. M2E Pro will resubmit the new value automatically.'
                );
                return false;
            }

            if (strlen($params['changed_sku']) > Ess_M2ePro_Helper_Component_Walmart::SKU_MAX_LENGTH) {

                $this->addMessage('The length of SKU must be less than 50 characters.');
                return false;
            }
        }

        return true;
    }

    // ---------------------------------------

    protected function validateCategory()
    {
        if (!$this->getWalmartListingProduct()->isExistCategoryTemplate()) {

            // M2ePro_TRANSLATIONS
            // Categories Settings are not set.
            $this->addMessage('Categories Settings are not set.');

            return false;
        }

        return true;
    }

    // ---------------------------------------

    protected function validateOnlinePriceInvalidBlocked()
    {
        if ($this->getListingProduct()->isBlocked() && $this->getWalmartListingProduct()->isOnlinePriceInvalid()) {
            $message = <<<HTML
The action cannot be submitted. Your Item is in Inactive (Blocked) status because it violates Walmart pricing rules.
 Please adjust the Item Price to comply with the Walmart requirements.
 Once the changes are applied, Walmart Item will become Active automatically.
HTML;

            $this->addMessage($message);
            return false;
        }

        return true;
    }

    protected function validateMissedOnChannelBlocked()
    {
        if ($this->getListingProduct()->isBlocked() && $this->getWalmartListingProduct()->isMissedOnChannel()) {

            $message = <<<HTML
The action cannot be submitted. Your Item is in Inactive (Blocked) status because it seems that the corresponding
 Walmart Item does not exist in your Channel inventory. Please contact Walmart Support Team to resolve the issue.
HTML;

            $this->addMessage($message);
            return false;
        }

        return true;
    }

    protected function validateGeneralBlocked()
    {
        if ($this->getListingProduct()->isBlocked() &&
            !$this->getWalmartListingProduct()->isMissedOnChannel() &&
            !$this->getWalmartListingProduct()->isOnlinePriceInvalid()
        ) {
            $message = <<<HTML
The action cannot be submitted. Your Item is in Inactive (Blocked) status because some Item data may
 contradict Walmart rules. To restore the Item to Active status, please adjust the related Policy settings and
 click Reset next to that Item. M2E Pro will resubmit the Item automatically.
HTML;

            $this->addMessage($message);
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
        if ($qty <= 0) {

            if (isset($this->params['status_changer']) &&
                $this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {

                // M2ePro_TRANSLATIONS
                // You are submitting an Item with zero quantity. It contradicts Walmart requirements. Please apply the Stop Action instead.
                $message = 'You are submitting an Item with zero quantity. It contradicts Walmart requirements.';

                if ($this->getListingProduct()->isStoppable()) {
                    $message .= ' Please apply the Stop Action instead.';
                }

                $this->addMessage($message);
            } else {
                // M2ePro_TRANSLATIONS
                // Cannot submit an Item with zero quantity. It contradicts Walmart requirements. This action has been generated automatically based on your Synchronization Rule settings. The error occurs when the Stop Rules are not properly configured or disabled. Please review your settings.
                $message = 'Cannot submit an Item with zero quantity. It contradicts Walmart requirements.
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

    protected function validatePrice()
    {
        if (!$this->getConfigurator()->isPriceAllowed()) {
            return true;
        }

        $price = $this->getPrice();
        if ($price <= 0) {

            // M2ePro_TRANSLATIONS
            // The Price must be greater than 0. Please, check the Selling Policy and Product Settings.
            $this->addMessage(
                'The Price must be greater than 0. Please, check the Selling Policy and Product Settings.'
            );

            return false;
        }

        $this->data['price'] = $price;

        return true;
    }

    // ---------------------------------------

    public function validateStartEndDates()
    {
        if (!$this->getConfigurator()->isDetailsAllowed()) {
            return true;
        }

        $startDate = $this->getWalmartListingProduct()->getSellingFormatTemplateSource()->getStartDate();

        if (!empty($startDate) && !strtotime($startDate)) {
            $this->addMessage('Start Date has invalid format.');
            return false;
        }

        $endDate = $this->getWalmartListingProduct()->getSellingFormatTemplateSource()->getEndDate();

        if (!empty($endDate)) {
            if (!strtotime($endDate)) {
                $this->addMessage('End Date has invalid format.');
                return false;
            }

            if (strtotime($endDate) < Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
                $this->addMessage('End Date must be greater than current date');
                return false;
            }
        }

        return true;
    }

    // ---------------------------------------

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

    // ---------------------------------------

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

        return true;
    }

    //########################################

    protected function validateMagentoProductType()
    {
        if ($this->getMagentoProduct()->isBundleType() ||
            $this->getMagentoProduct()->isSimpleTypeWithCustomOptions() ||
            $this->getMagentoProduct()->isDownloadableTypeWithSeparatedLinks()
        ) {
            $message = <<<HTML
Magento Simple with Custom Options, Bundle and Downloadable with Separated Links Products cannot be submitted to
the Walmart marketplace. These types of Magento Variational Products contradict Walmart Variant Group parameters.
Only Product Variations created based on Magento Configurable or Grouped Product types can be sold on
the Walmart website.
HTML;
            $this->addMessage($message);
            return false;
        }

        return true;
    }

    //########################################

    protected function getPrice()
    {
        if (isset($this->data['price'])) {
            return $this->data['price'];
        }

        return $this->getWalmartListingProduct()->getPrice();
    }

    protected function getQty()
    {
        if (isset($this->data['qty'])) {
            return $this->data['qty'];
        }

        return $this->getWalmartListingProduct()->getQty();
    }

    protected function getPromotions()
    {
        if (isset($this->data['promotions'])) {
            return $this->data['promotions'];
        }

        return $this->getWalmartListingProduct()->getPromotions();
    }

    //########################################

    protected function validatePromotions()
    {
        if (!$this->getConfigurator()->isPromotionsAllowed()) {
            return true;
        }

        $requiredAttributesMap = array(
            'start_date'       => Mage::helper('M2ePro')->__('Start Date'),
            'end_date'         => Mage::helper('M2ePro')->__('End Date'),
            'price'            => Mage::helper('M2ePro')->__('Promotion Price'),
            'comparison_price' => Mage::helper('M2ePro')->__('Comparison Price'),
        );

        $promotions = $this->getPromotions();
        foreach ($promotions as $promotionIndex => $promotionRow) {

            foreach ($requiredAttributesMap as $requiredAttributeKey => $requiredAttributeTitle) {
                if (empty($promotionRow[$requiredAttributeKey])) {

                    $message = <<<HTML
Invalid Promotion #%s. The Promotion Price has no defined value.
 Please adjust Magento Attribute "%s" value set for the Promotion Price in your Selling Policy.
HTML;
                    $this->addMessage(sprintf($message, $promotionIndex + 1, $requiredAttributeTitle));
                    return false;
                }
            }

            if (!strtotime($promotionRow['start_date'])) {
                $message = <<<HTML
Invalid Promotion #%s. The Start Date has incorrect format.
 Please adjust Magento Attribute value set for the Promotion Start Date in your Selling Policy.
HTML;
                $this->addMessage(sprintf($message, $promotionIndex + 1));
                return false;
            }

            if (!strtotime($promotionRow['end_date'])) {
                $message = <<<HTML
Invalid Promotion #%s. The End Date has incorrect format.
 Please adjust Magento Attribute value set for the Promotion End Date in your Selling Policy.
HTML;
                $this->addMessage(sprintf($message, $promotionIndex + 1));
                return false;
            }

            if (strtotime($promotionRow['end_date']) < strtotime($promotionRow['start_date'])) {
                $message = <<<HTML
Invalid Promotion #%s. The Start and End Date range is incorrect.
 Please adjust the Promotion Dates set in your Selling Policy.
HTML;
                $this->addMessage(sprintf($message, $promotionIndex + 1));
                return false;
            }

            if ($promotionRow['comparison_price'] <= $promotionRow['price']) {
                $message = <<<HTML
Invalid Promotion #%s. Comparison Price must be greater than Promotion Price.
 Please adjust the Price settings for the given Promotion in your Selling Policy.
HTML;
                $this->addMessage(sprintf($message, $promotionIndex + 1));
                return false;
            }
        }

        $this->data['promotions'] = $promotions;

        return true;
    }

    //########################################

    protected function validatePriceAndPromotionsFeedBlocked()
    {
        if (is_null($this->getWalmartListingProduct()->getListDate())) {
            return true;
        }

        try {
            $borderDate = new DateTime($this->getWalmartListingProduct()->getListDate(), new DateTimeZone('UTC'));
            $borderDate->modify('+24 hours');
        } catch (\Exception $exception) {
            return true;
        }

        if ($borderDate < new DateTime('now', new DateTimeZone('UTC'))) {
            return true;
        }

        if ($this->getConfigurator()->isPromotionsAllowed()) {

            $this->getConfigurator()->disallowPromotions();
            $this->addMessage(
                'Item Promotion Price will not be submitted during this action.
                Walmart allows updating the Promotion Price information no sooner than 24 hours after the
                relevant product is listed on their website.',
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
            );
        }

        if ($this->getConfigurator()->isPriceAllowed()) {

            $this->getConfigurator()->disallowPrice();
            $this->addMessage(
                'Item Price will not be submitted during this action.
                Walmart allows updating the Price information no sooner than 24 hours after the relevant product
                is listed on their website.',
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
            );
        }

        return true;
    }

    //########################################

    protected function validateProductIds()
    {
        if (!$this->getConfigurator()->isDetailsAllowed()) {
            return true;
        }

        $isAtLeastOneSpecified = false;

        if ($gtin = $this->getGtin()) {

            if (!Mage::helper('M2ePro')->isGTIN($gtin)) {
                $this->addMessage(
                    Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'The action cannot be completed because the product GTIN has incorrect format: "%id%".
                        Please adjust the related Magento Attribute value and resubmit the action.',
                        array('!id' => $gtin)
                    )
                );
                return false;
            }

            $this->data['gtin'] = $gtin;
            $isAtLeastOneSpecified = true;
        }

        if ($upc = $this->getUpc()) {

            if (!Mage::helper('M2ePro')->isUPC($upc)) {
                $this->addMessage(
                    Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'The action cannot be completed because the product UPC has incorrect format: "%id%".
                        Please adjust the related Magento Attribute value and resubmit the action.',
                        array('!id' => $upc)
                    )
                );
                return false;
            }

            $this->data['upc'] = $upc;
            $isAtLeastOneSpecified = true;
        }

        if ($ean = $this->getEan()) {

            if (!Mage::helper('M2ePro')->isEAN($ean)) {
                $this->addMessage(
                    Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'The action cannot be completed because the product EAN has incorrect format: "%id%".
                        Please adjust the related Magento Attribute value and resubmit the action.',
                        array('!id' => $ean)
                    )
                );
                return false;
            }

            $this->data['ean'] = $ean;
            $isAtLeastOneSpecified = true;
        }

        if ($isbn = $this->getIsbn()) {

            if (!Mage::helper('M2ePro')->isISBN($isbn)) {
                $this->addMessage(
                    Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'The action cannot be completed because the product ISBN has incorrect format: "%id%".
                        Please adjust the related Magento Attribute value and resubmit the action.',
                        array('!id' => $isbn)
                    )
                );
                return false;
            }

            $this->data['isbn'] = $isbn;
            $isAtLeastOneSpecified = true;
        }

        if (!$isAtLeastOneSpecified) {

            $this->addMessage(
                'The Item was not listed because it has no defined Product ID. Walmart requires that all Items sold
                on the website have Product IDs. Please provide a valid GTIN, UPC, EAN or ISBN for the Product.
                M2E Pro will try to list the Item again.'
            );
            return false;
        }

        return true;
    }

    protected function getGtin()
    {
        if (isset($this->data['gtin'])) {
            return $this->data['gtin'];
        }

        return $this->getWalmartListingProduct()->getGtin();
    }

    protected function getUpc()
    {
        if (isset($this->data['upc'])) {
            return $this->data['upc'];
        }

        return $this->getWalmartListingProduct()->getUpc();
    }

    protected function getEan()
    {
        if (isset($this->data['ean'])) {
            return $this->data['ean'];
        }

        return $this->getWalmartListingProduct()->getEan();
    }

    protected function getIsbn()
    {
        if (isset($this->data['isbn'])) {
            return $this->data['isbn'];
        }

        return $this->getWalmartListingProduct()->getIsbn();
    }

    //########################################
}