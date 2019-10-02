<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Response
{
    /**
     * @var array
     */
    protected $_params = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $_listingProduct = null;

    /**
     * @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator
     */
    protected $_configurator = null;

    /**
     * @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_RequestData
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
    protected function getParams()
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
     * @param Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator $object
     */
    public function setConfigurator(Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator $object)
    {
        $this->_configurator = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator
     */
    protected function getConfigurator()
    {
        return $this->_configurator;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Walmart_Listing_Product_Action_RequestData $object
     */
    public function setRequestData(Ess_M2ePro_Model_Walmart_Listing_Product_Action_RequestData $object)
    {
        $this->_requestData = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product_Action_RequestData
     */
    protected function getRequestData()
    {
        return $this->_requestData;
    }

    // ---------------------------------------

    public function getRequestMetaData($key = NULL)
    {
        if ($key !== null) {
            return isset($this->_requestMetaData[$key]) ? $this->_requestMetaData[$key] : NULL;
        }

        return $this->_requestMetaData;
    }

    public function setRequestMetaData($value)
    {
        $this->_requestMetaData = $value;
        return $this;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product
     */
    protected function getWalmartListingProduct()
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
     * @return Ess_M2ePro_Model_Walmart_Listing
     */
    protected function getWalmartListing()
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
                $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
            }
        }

        if ($this->getRequestData()->hasLagTime()) {
            $data['online_lag_time'] = $this->getRequestData()->getLagTime();
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

    protected function appendPromotionsValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['promotions_data'])) {
            return $data;
        }

        $data['online_promotions'] = Mage::helper('M2ePro')->jsonEncode($requestMetadata['promotions_data']);

        return $data;
    }

    protected function appendDetailsValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['details_data'])) {
            return $data;
        }

        $data['online_details_data'] = Mage::helper('M2ePro')->jsonEncode($requestMetadata['details_data']);

        return $data;
    }

    protected function appendStartDate($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['details_data']['start_date'])) {
            return $data;
        }

        $data['online_start_date'] = $requestMetadata['details_data']['start_date'];

        return $data;
    }

    protected function appendEndDate($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['details_data']['end_date'])) {
            return $data;
        }

        $data['online_end_date'] = $requestMetadata['details_data']['end_date'];

        return $data;
    }

    protected function appendChangedSku($data)
    {
        if (!$this->getRequestData()->getIsNeedSkuUpdate()) {
            return $data;
        }

        $walmartItem = $this->getListingProduct()->getChildObject()->getWalmartItem();
        $walmartItem->setData('sku', $this->getRequestData()->getSku());
        $walmartItem->save();

        $data['sku'] = $this->getRequestData()->getSku();

        return $data;
    }

    protected function appendProductIdsData($data)
    {
        if (!$this->getRequestData()->hasProductIdsData()) {
            return $data;
        }

        $productIdsData = $this->getRequestData()->getProductIdsData();

        foreach ($productIdsData as $productIdData) {
            $data[strtolower($productIdData['type'])] = $productIdData['id'];
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
