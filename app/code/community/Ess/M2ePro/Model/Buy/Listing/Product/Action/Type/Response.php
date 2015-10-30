<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Response
{
    /**
     * @var array
     */
    private $params = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProduct = NULL;

    /**
     * @var Ess_M2ePro_Model_Buy_Listing_Product_Action_Configurator
     */
    private $configurator = NULL;

    /**
     * @var Ess_M2ePro_Model_Buy_Listing_Product_Action_RequestData
     */
    protected $requestData = NULL;

    //########################################

    abstract public function processSuccess($params = array());

    //########################################

    /**
     * @param array $params
     */
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

    /**
     * @param Ess_M2ePro_Model_Listing_Product $object
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $object)
    {
        $this->listingProduct = $object;
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
     * @param Ess_M2ePro_Model_Buy_Listing_Product_Action_Configurator $object
     */
    public function setConfigurator(Ess_M2ePro_Model_Buy_Listing_Product_Action_Configurator $object)
    {
        $this->configurator = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Listing_Product_Action_Configurator
     */
    protected function getConfigurator()
    {
        return $this->configurator;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Buy_Listing_Product_Action_RequestData $object
     */
    public function setRequestData(Ess_M2ePro_Model_Buy_Listing_Product_Action_RequestData $object)
    {
        $this->requestData = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Listing_Product_Action_RequestData
     */
    protected function getRequestData()
    {
        return $this->requestData;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Buy_Listing_Product
     */
    protected function getBuyListingProduct()
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
     * @return Ess_M2ePro_Model_Buy_Listing
     */
    protected function getBuyListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ---------------------------------------

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

    // ---------------------------------------

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
        if (isset($this->params['status_changer'])) {
            $data['status_changer'] = (int)$this->params['status_changer'];
        }

        return $data;
    }

    // ---------------------------------------

    protected function appendConditionValues($data)
    {
        if ($this->getRequestData()->hasCondition()) {
            $data['condition'] = $this->getRequestData()->getCondition();
        }

        if ($this->getRequestData()->hasConditionNote()) {
            $data['condition_note'] = $this->getRequestData()->getConditionNote();
        }

        return $data;
    }

    // ---------------------------------------

    protected function appendQtyValues($data)
    {
        if (!$this->getRequestData()->hasQty()) {
            return $data;
        }

        $data['online_qty'] = (int)$this->getRequestData()->getQty();

        if ((int)$data['online_qty'] > 0) {
            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
        } else {
            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
        }

        return $data;
    }

    protected function appendPriceValues($data)
    {
        if (!$this->getRequestData()->hasPrice()) {
            return $data;
        }

        $data['online_price'] = (float)$this->getRequestData()->getPrice();

        return $data;
    }

    // ---------------------------------------

    protected function appendShippingValues($data)
    {
        if (!$this->getRequestData()->hasShippingData()) {
            return $data;
        }

        if ($this->getRequestData()->hasShippingStandardRate()) {
            $data['shipping_standard_rate'] = $this->getRequestData()->getShippingStandardRate();
        }

        if ($this->getRequestData()->hasShippingExpeditedMode()) {
            $data['shipping_expedited_mode'] = $this->getRequestData()->getShippingExpeditedMode();
        }

        if ($this->getRequestData()->hasShippingExpeditedRate()) {
            $data['shipping_expedited_rate'] = $this->getRequestData()->getShippingExpeditedRate();
        }

        if ($this->getRequestData()->hasShippingOneDayMode()) {
            $data['shipping_one_day_mode'] = $this->getRequestData()->getShippingOneDayMode();
        }

        if ($this->getRequestData()->hasShippingOneDayRate()) {
            $data['shipping_one_day_rate'] = $this->getRequestData()->getShippingOneDayRate();
        }

        if ($this->getRequestData()->hasShippingTwoDayMode()) {
            $data['shipping_two_day_mode'] = $this->getRequestData()->getShippingTwoDayMode();
        }

        if ($this->getRequestData()->hasShippingTwoDayRate()) {
            $data['shipping_two_day_rate'] = $this->getRequestData()->getShippingTwoDayRate();
        }

        return $data;
    }

    //########################################

    protected function setLastSynchronizationDates()
    {
        if (!$this->getConfigurator()->isQtyAllowed() && !$this->getConfigurator()->isPriceAllowed()) {
            return;
        }

        $additionalData = $this->getListingProduct()->getAdditionalData();

        if ($this->getConfigurator()->isQtyAllowed()) {
            $additionalData['last_synchronization_dates']['qty'] = Mage::helper('M2ePro')->getCurrentGmtDate();
        }

        if ($this->getConfigurator()->isPriceAllowed()) {
            $additionalData['last_synchronization_dates']['price'] = Mage::helper('M2ePro')->getCurrentGmtDate();
        }

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
    }

    //########################################
}