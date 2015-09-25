<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Request
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Request
{
    /**
     * @var array
     */
    protected $validatorsData = array();

    /**
     * @var array
     */
    private $requestsTypes = array(
        'details',
        'shipping',
        'selling',
        'newProduct',
    );

    /**
     * @var Ess_M2ePro_Model_Buy_Listing_Product_Action_Request_Abstract[]
     */
    private $requests = array();

    // ########################################

    public function setValidatorsData(array $data)
    {
        $this->validatorsData = $data;
    }

    /**
     * @return array
     */
    public function getValidatorsData()
    {
        return $this->validatorsData;
    }

    // ########################################

    public function getData()
    {
        $this->beforeBuildDataEvent();
        $data = $this->getActionData();

        $data = $this->prepareFinalData($data);
        $this->collectRequestsWarningMessages();

        return $data;
    }

    // ########################################

    protected function beforeBuildDataEvent() {}

    abstract protected function getActionData();

    // -----------------------------------------

    protected function prepareFinalData(array $data)
    {
        if (!isset($data['sku'])) {
            $data['sku'] = $this->getBuyListingProduct()->getSku();
        }

        if (!isset($data['product_id'])) {
            $data['product_id'] = $this->getBuyListingProduct()->getGeneralId();
        }

        if (!isset($data['product_id_type'])) {
            $data['product_id_type'] = 0; // BUY SKU
        }

        if (!isset($data['price'])) {
            $data['price'] = $this->getBuyListingProduct()->getOnlinePrice();
        }

        if (!isset($data['qty'])) {
            $data['qty'] = $this->getBuyListingProduct()->getOnlineQty();
        }

        if (!isset($data['condition'])) {
            $data['condition'] = $this->getBuyListingProduct()->getCondition();
        }

        if (!isset($data['condition_note'])) {
            $data['condition_note'] = $this->getBuyListingProduct()->getConditionNote();
        }

        if (!isset($data['shipping_standard_rate'])) {
            $data['shipping_standard_rate'] = $this->getBuyListingProduct()->getShippingStandardRate();
            is_null($data['shipping_standard_rate']) && $data['shipping_standard_rate'] = '';
        }

        if (!isset($data['shipping_expedited_mode'])) {
            $data['shipping_expedited_mode'] = $this->getBuyListingProduct()->getShippingExpeditedMode();
            is_null($data['shipping_expedited_mode']) && $data['shipping_expedited_mode'] = 0;
        }

        if (!isset($data['shipping_expedited_rate'])) {
            $data['shipping_expedited_rate'] = $this->getBuyListingProduct()->getShippingExpeditedRate();
            is_null($data['shipping_expedited_rate']) && $data['shipping_expedited_rate'] = '';
        }

        if (!isset($data['shipping_one_day_mode'])) {
            $data['shipping_one_day_mode'] = '';
        }

        if (!isset($data['shipping_one_day_rate'])) {
            $data['shipping_one_day_rate'] = '';
        }

        if (!isset($data['shipping_two_day_mode'])) {
            $data['shipping_two_day_mode'] = '';
        }

        if (!isset($data['shipping_two_day_rate'])) {
            $data['shipping_two_day_rate'] = '';
        }

        return $data;
    }

    protected function collectRequestsWarningMessages()
    {
        foreach ($this->requestsTypes as $requestType) {

            $messages = $this->getRequest($requestType)->getWarningMessages();

            foreach ($messages as $message) {
                $this->addWarningMessage($message);
            }
        }
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Buy_Listing_Product_Action_Request_Details
     */
    public function getRequestDetails()
    {
        return $this->getRequest('details');
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Listing_Product_Action_Request_Shipping
     */
    public function getRequestShipping()
    {
        return $this->getRequest('shipping');
    }

    // -----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Buy_Listing_Product_Action_Request_Selling
     */
    public function getRequestSelling()
    {
        return $this->getRequest('selling');
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Listing_Product_Action_Request_NewProduct
     */
    public function getRequestNewProduct()
    {
        return $this->getRequest('newProduct');
    }

    // ########################################

    /**
     * @param $type
     * @return Ess_M2ePro_Model_Buy_Listing_Product_Action_Request_Abstract
     */
    private function getRequest($type)
    {
        if (!isset($this->requests[$type])) {

            /** @var Ess_M2ePro_Model_Buy_Listing_Product_Action_Request_Abstract $request */
            $request = Mage::getModel('M2ePro/Buy_Listing_Product_Action_Request_'.ucfirst($type));

            $request->setParams($this->getParams());
            $request->setListingProduct($this->getListingProduct());
            $request->setConfigurator($this->getConfigurator());
            $request->setValidatorsData($this->getValidatorsData());

            $this->requests[$type] = $request;
        }

        return $this->requests[$type];
    }

    // ########################################
}