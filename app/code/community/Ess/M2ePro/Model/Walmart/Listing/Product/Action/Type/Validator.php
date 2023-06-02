<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Component_Walmart_Configuration as ConfigurationHelper;

abstract class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Validator
{
    /**
     * @var array
     */
    protected $_params = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $_listingProduct = null;

    /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator $_configurator */
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
     * @param Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator $configurator
     * @return $this
     */
    public function setConfigurator(Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator $configurator)
    {
        $this->_configurator = $configurator;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator
     */
    protected function getConfigurator()
    {
        return $this->_configurator;
    }

    //########################################

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
        if (!$this->getWalmartListingProduct()->getSku()) {
            $this->addMessage('You have to list Item first.');
            return false;
        }

        $params = $this->getParams();
        if (isset($params['changed_sku'])) {
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
The action cannot be submitted. Your Item is in Incomplete status because it violates Walmart pricing rules.
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
The action cannot be submitted. Your Item is in Incomplete status because it seems that the corresponding
 Walmart Item does not exist in your Channel inventory. Please contact Walmart Support Team to resolve the issue.
HTML;

            $this->addMessage($message);
            return false;
        }

        return true;
    }

    protected function validateGeneralBlocked()
    {
        if ($this->isChangerUser()) {
            return true;
        }

        if ($this->getListingProduct()->isBlocked() &&
            !$this->getWalmartListingProduct()->isMissedOnChannel() &&
            !$this->getWalmartListingProduct()->isOnlinePriceInvalid()
        ) {
            $message = <<<HTML
The action cannot be submitted. Your Item is in Incomplete status because some Item data may
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
                $message = 'You are submitting an Item with zero quantity. It contradicts Walmart requirements.';

                if ($this->getListingProduct()->isStoppable()) {
                    $message .= ' Please apply the Stop Action instead.';
                }

                $this->addMessage($message);
            } else {
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

        $this->_data['qty'] = $qty;
        $this->_data['clear_qty'] = $clearQty;

        return true;
    }

    protected function validatePrice()
    {
        if (!$this->getConfigurator()->isPriceAllowed()) {
            return true;
        }

        $price = $this->getPrice();
        if ($price <= 0) {
            $this->addMessage(
                'The Price must be greater than 0. Please, check the Selling Policy and Product Settings.'
            );

            return false;
        }

        $this->_data['price'] = $price;

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

            /** @var Ess_M2ePro_Helper_Data $helper */
            $helper = Mage::helper('M2ePro');
            if ((int)$helper->createGmtDateTime($endDate)->format('U') < $helper->getCurrentGmtDate(true)) {
                $this->addMessage('End Date must be greater than current date');
                return false;
            }
        }

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
        if (isset($this->_data['price'])) {
            return $this->_data['price'];
        }

        return $this->getWalmartListingProduct()->getPrice();
    }

    protected function getQty()
    {
        if (isset($this->_data['qty'])) {
            return $this->_data['qty'];
        }

        return $this->getWalmartListingProduct()->getQty();
    }

    protected function getClearQty()
    {
        if (isset($this->_data['clear_qty'])) {
            return $this->_data['clear_qty'];
        }

        return $this->getWalmartListingProduct()->getQty(true);
    }

    protected function getPromotionsMessages()
    {
        if (isset($this->_data['promotions_messages'])) {
            return $this->_data['promotions_messages'];
        }

        return $this->getWalmartListingProduct()->getPromotionsErrorMessages();
    }

    //########################################

    protected function validatePromotions()
    {
        if (!$this->getConfigurator()->isPromotionsAllowed()) {
            return true;
        }

        $messages = $this->getPromotionsMessages();
        foreach ($messages as $message) {
            $this->addMessage($message);
        }

        $this->_data['promotions_messages'] = $messages;

        return true;
    }

    //########################################

    protected function validatePriceAndPromotionsFeedBlocked()
    {
        if ($this->getWalmartListingProduct()->getListDate() === null) {
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

    protected function validateProductId()
    {
        if (!$this->getConfigurator()->isDetailsAllowed()) {
            return true;
        }

        if ($this->getWalmartListingProduct()->getWpid()) {
            return true;
        }

        $identifier = $this->getIdentifierFromConfiguration();

        if (empty($identifier)) {
            return false;
        }

        if (strtoupper($identifier) === ConfigurationHelper::PRODUCT_ID_OVERRIDE_CUSTOM_CODE) {
            $identifierType = Ess_M2ePro_Helper_Data::GTIN;
        } else {
            $identifierType = Mage::helper('M2ePro')->getIdentifierType($identifier);
        }

        if ($identifierType === null) {
            $this->addMessage(
                Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'The action cannot be completed because the product Identifier has incorrect format: "%id%".
                        Please adjust the related Magento Attribute value and resubmit the action.',
                    array('!id' => $identifier)
                )
            );

            return false;
        }

        $this->_data['identifier'] = array(
            'type' => $identifierType,
            'id' => $identifier,
        );

        return true;
    }

    //########################################
}
