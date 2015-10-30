<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Response
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
     * @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator
     */
    private $configurator = NULL;

    /**
     * @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_RequestData
     */
    protected $requestData = NULL;

    //########################################

    abstract public function processSuccess($params = array());

    //########################################

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
     * @param Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator $object
     */
    public function setConfigurator(Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator $object)
    {
        $this->configurator = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator
     */
    protected function getConfigurator()
    {
        return $this->configurator;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Amazon_Listing_Product_Action_RequestData $object
     */
    public function setRequestData(Ess_M2ePro_Model_Amazon_Listing_Product_Action_RequestData $object)
    {
        $this->requestData = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_RequestData
     */
    protected function getRequestData()
    {
        return $this->requestData;
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
        if (isset($this->params['status_changer'])) {
            $data['status_changer'] = (int)$this->params['status_changer'];
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

        $data['online_sale_price'] = NULL;
        $data['online_sale_price_start_date'] = NULL;
        $data['online_sale_price_end_date'] = NULL;

        if ($this->getRequestData()->hasSalePrice()) {

            $salePrice = (float)$this->getRequestData()->getSalePrice();

            if ($salePrice > 0) {
                $data['online_sale_price']            = $salePrice;
                $data['online_sale_price_start_date'] = $this->getRequestData()->getSalePriceStartDate();
                $data['online_sale_price_end_date']   = $this->getRequestData()->getSalePriceEndDate();
            } else {
                $data['online_sale_price'] = 0;
            }
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