<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Template_ChangeProcessor_Abstract as ChangeProcessor;

abstract class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Response
{
    const INSTRUCTION_INITIATOR = 'action_response';

    /**
     * @var array
     */
    protected $_params = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $_listingProduct = null;

    /**
     * @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator
     */
    protected $_configurator = null;

    /**
     * @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_RequestData
     */
    protected $_requestData = null;

    /**
     * @var array
     */
    protected $_requestMetaData = array();

    //########################################

    abstract public function processSuccess($params = array());

    //########################################

    public function setParams(array $params = array())
    {
        $this->_params = $params;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $object
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $object)
    {
        $this->_listingProduct = $object;
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
     * @param Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator $object
     */
    public function setConfigurator(Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator $object)
    {
        $this->_configurator = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator
     */
    protected function getConfigurator()
    {
        return $this->_configurator;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Amazon_Listing_Product_Action_RequestData $object
     */
    public function setRequestData(Ess_M2ePro_Model_Amazon_Listing_Product_Action_RequestData $object)
    {
        $this->_requestData = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_RequestData
     */
    protected function getRequestData()
    {
        return $this->_requestData;
    }

    // ---------------------------------------

    public function getRequestMetaData()
    {
        return $this->_requestMetaData;
    }

    public function setRequestMetaData($value)
    {
        $this->_requestMetaData = $value;
        return $this;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product
     */
    protected function getAmazonListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
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
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getListing()->getMarketplace();
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
     * @return Ess_M2ePro_Model_Magento_Product
     */
    protected function getMagentoProduct()
    {
        return $this->getListingProduct()->getMagentoProduct();
    }

    //########################################

    protected function appendStatusChangerValue($data)
    {
        if (isset($this->_params['status_changer'])) {
            $data['status_changer'] = (int)$this->_params['status_changer'];
        }

        return $data;
    }

    // ---------------------------------------

    protected function appendQtyValues($data)
    {
        if ($this->getRequestData()->hasQty()) {
            $data['online_qty'] = (int)$this->getRequestData()->getQty();

            if ((int)$data['online_qty'] > 0) {
                $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
            } else {
                $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE;
            }
        }

        $data['online_handling_time'] = $this->getRequestData()->getHandlingTime();

        if ($this->getRequestData()->hasRestockDate()) {
            $data['online_restock_date'] = $this->getRequestData()->getRestockDate();
        }

        return $data;
    }

    protected function appendRegularPriceValues($data)
    {
        if (!$this->getRequestData()->hasRegularPrice()) {
            return $data;
        }

        $data['online_regular_price'] = (float)$this->getRequestData()->getRegularPrice();

        $data['online_regular_sale_price'] = null;
        $data['online_regular_sale_price_start_date'] = null;
        $data['online_regular_sale_price_end_date'] = null;

        if ($this->getRequestData()->hasRegularSalePrice()) {
            $salePrice = (float)$this->getRequestData()->getRegularSalePrice();

            if ($salePrice > 0) {
                $data['online_regular_sale_price'] = $salePrice;
                $data['online_regular_sale_price_start_date'] = $this->getRequestData()->getRegularSalePriceStartDate();
                $data['online_regular_sale_price_end_date'] = $this->getRequestData()->getRegularSalePriceEndDate();
            } else {
                $data['online_regular_sale_price'] = 0;
            }
        }

        return $data;
    }

    protected function appendBusinessPriceValues($data)
    {
        if (!$this->getRequestData()->hasBusinessPrice()) {
            return $data;
        }

        $data['online_business_price'] = (float)$this->getRequestData()->getBusinessPrice();

        if ($this->getRequestData()->hasBusinessDiscounts()) {
            $businessDiscounts = $this->getRequestData()->getBusinessDiscounts();
            $data['online_business_discounts'] = Mage::helper('M2ePro')->jsonEncode($businessDiscounts['values']);
        }

        return $data;
    }

    protected function appendDetailsValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['details_data'])) {
            return $data;
        }

        $data['online_details_data'] = Mage::helper('M2ePro')->hashString(
            Mage::helper('M2ePro')->jsonEncode($requestMetadata['details_data']),
            'md5'
        );

        return $data;
    }

    protected function appendImagesValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['images_data'])) {
            return $data;
        }

        $data['online_images_data'] = Mage::helper('M2ePro')->hashString(
            Mage::helper('M2ePro')->jsonEncode($requestMetadata['images_data']),
            'md5'
        );

        return $data;
    }

    /**
     * @param array $data
     * @param bool $value
     * @return array
     */
    protected function appendIsStoppedManually($data, $value)
    {
        $data[Ess_M2ePro_Model_Resource_Amazon_Listing_Product::IS_STOPPED_MANUALLY_FIELD] = $value;
        return $data;
    }

    //########################################

    protected function appendGiftSettingsStatus($data)
    {
        if (!$this->getRequestData()->hasGiftWrap() && !$this->getRequestData()->hasGiftMessage()) {
            return $data;
        }

        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        if (!$this->getRequestData()->getGiftWrap() && !$this->getRequestData()->getGiftMessage()) {
            $data['additional_data']['online_gift_settings_disabled'] = true;
        } else {
            $data['additional_data']['online_gift_settings_disabled'] = false;
        }

        return $data;
    }

    //########################################

    protected function setLastSynchronizationDates()
    {
        if (!$this->getConfigurator()->isQtyAllowed() && !$this->getConfigurator()->isRegularPriceAllowed()) {
            return;
        }

        $additionalData = $this->getListingProduct()->getAdditionalData();

        if ($this->getConfigurator()->isQtyAllowed()) {
            $additionalData['last_synchronization_dates']['qty'] = Mage::helper('M2ePro')->getCurrentGmtDate();
        }

        if ($this->getConfigurator()->isRegularPriceAllowed()) {
            $additionalData['last_synchronization_dates']['price'] = Mage::helper('M2ePro')->getCurrentGmtDate();
        }

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
    }

    //########################################

    public function throwRepeatActionInstructions()
    {
        $instructions = array();

        if ($this->getConfigurator()->isQtyAllowed()) {
            $instructions[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => ChangeProcessor::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 80
            );
        }

        if ($this->getConfigurator()->isRegularPriceAllowed() || $this->getConfigurator()->isBusinessPriceAllowed()) {
            $instructions[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => ChangeProcessor::INSTRUCTION_TYPE_PRICE_DATA_CHANGED,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 80
            );
        }

        if ($this->getConfigurator()->isDetailsAllowed()) {
            $instructions[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => ChangeProcessor::INSTRUCTION_TYPE_DETAILS_DATA_CHANGED,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 60
            );
        }

        if ($this->getConfigurator()->isImagesAllowed()) {
            $instructions[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => ChangeProcessor::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 30
            );
        }

        Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add($instructions);
    }

    //########################################
}
