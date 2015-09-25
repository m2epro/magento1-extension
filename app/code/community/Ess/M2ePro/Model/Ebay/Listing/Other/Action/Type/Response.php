<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Response
{
    /**
     * @var array
     */
    private $params = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Other
     */
    private $listingOther = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_Configurator
     */
    private $configurator = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_RequestData
     */
    protected $requestData = NULL;

    // ########################################

    abstract public function processSuccess(array $response, array $responseParams = array());

    // ########################################

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

    // ----------------------------------------

    public function setListingOther(Ess_M2ePro_Model_Listing_Other $object)
    {
        $this->listingOther = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Other
     */
    protected function getListingOther()
    {
        return $this->listingOther;
    }

    // ----------------------------------------

    public function setConfigurator(Ess_M2ePro_Model_Ebay_Listing_Other_Action_Configurator $object)
    {
        $this->configurator = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Action_Configurator
     */
    protected function getConfigurator()
    {
        return $this->configurator;
    }

    // ----------------------------------------

    public function setRequestData(Ess_M2ePro_Model_Ebay_Listing_Other_Action_RequestData $object)
    {
        $this->requestData = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Action_RequestData
     */
    protected function getRequestData()
    {
        return $this->requestData;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other
     */
    protected function getEbayListingOther()
    {
        return $this->getListingOther()->getChildObject();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getListingOther()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Marketplace
     */
    protected function getEbayMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getListingOther()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Account
     */
    protected function getEbayAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    protected function getMagentoProduct()
    {
        return $this->getListingOther()->getMagentoProduct();
    }

    // ########################################

    /**
     * @param $itemId
     * @return Ess_M2ePro_Model_Ebay_Item
     */
    protected function createEbayItem($itemId)
    {
        $data = array(
            'account_id' => $this->getAccount()->getId(),
            'marketplace_id' => $this->getMarketplace()->getId(),
            'item_id' => (double)$itemId,
            'product_id' => (int)$this->getListingOther()->getProductId(),
            'store_id' => (int)$this->getListingOther()->getChildObject()->getRelatedStoreId()
        );

        /** @var Ess_M2ePro_Model_Ebay_Item $object */
        $object = Mage::getModel('M2ePro/Ebay_Item');
        $object->setData($data)->save();

        return $object;
    }

    // ########################################

    protected function appendStatusChangerValue($data, $responseParams)
    {
        if (isset($this->params['status_changer'])) {
            $data['status_changer'] = (int)$this->params['status_changer'];
        }

        if (isset($responseParams['status_changer'])) {
            $data['status_changer'] = (int)$responseParams['status_changer'];
        }

        return $data;
    }

    // ----------------------------------------

    protected function appendOnlineQtyValues($data)
    {
        $data['online_qty_sold'] = 0;

        if ($this->getRequestData()->hasQty()) {
            $data['online_qty'] = $this->getRequestData()->getQty();
        }

        return $data;
    }

    protected function appendOnlinePriceValue($data)
    {
        if ($this->getRequestData()->hasPriceFixed()) {
            $data['online_price'] = $this->getRequestData()->getPriceFixed();
        }

        return $data;
    }

    protected function appendTitleValue($data)
    {
        if ($this->getRequestData()->hasTitle()) {
            $data['title'] = $this->getRequestData()->getTitle();
        }

        return $data;
    }

    // ----------------------------------------

    protected function appendStartDateEndDateValues($data, $response)
    {
        if (isset($response['ebay_start_date_raw'])) {
            $data['start_date'] = Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString(
                $response['ebay_start_date_raw']
            );
        }

        if (isset($response['ebay_end_date_raw'])) {
            $data['end_date'] = Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString(
                $response['ebay_end_date_raw']
            );
        }

        return $data;
    }

    // ########################################
}