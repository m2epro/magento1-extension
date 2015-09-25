<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Search_Custom
{
    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    private $listingProduct = null;

    private $query = null;

    // ########################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->listingProduct = $listingProduct;
        return $this;
    }

    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    // ########################################

    public function process()
    {
        $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');
        $connectorObj = $dispatcherObject->getConnector('custom', $this->getSearchMethod(), 'requester',
                                                        $this->getConnectorParams(),
                                                        $this->listingProduct->getAccount(),
                                                        'Ess_M2ePro_Model_Buy_Search');

        $searchData = $dispatcherObject->process($connectorObj);
        return $this->prepareResult($searchData);
    }

    // ########################################

    private function getConnectorParams()
    {
        $params = array(
            'query' => $this->query,
        );

        if ($this->getSearchMethod() == 'byIdentifier') {
            $params['search_type'] = $this->getSearchType();
        }

        return $params;
    }

    private function getSearchMethod()
    {
        $searchMethod = 'byQuery';

        if ($this->isGeneralId($this->query) || $this->isUpc($this->query)) {
            $searchMethod = 'byIdentifier';
        }

        return $searchMethod;
    }

    private function prepareResult($searchData)
    {
        if ($this->getSearchMethod() == 'byQuery') {
            $type = 'string';
        } else {
            $type = $this->isGeneralId($this->query) ? 'sku' : 'upc';
        }

        return array(
            'type'  => $type,
            'value' => $this->query,
            'data'  => $searchData,
        );
    }

    private function getSearchType()
    {
        if (empty($this->query)) {
            return false;
        }

        if ($this->isGeneralId($this->query)) {
            return Ess_M2ePro_Model_Connector_Buy_Search_ByIdentifier_ItemsRequester::SEARCH_TYPE_GENERAL_ID;
        }

        if ($this->isUpc($this->query)) {
            return Ess_M2ePro_Model_Connector_Buy_Search_ByIdentifier_ItemsRequester::SEARCH_TYPE_UPC;
        }

        return false;
    }

    // ########################################

    private function isGeneralId($query)
    {
        if (empty($query)) {
            return false;
        }

        return preg_match('/^\d{8,9}$/', $query);
    }

    private function isUpc($query)
    {
        return Mage::helper('M2ePro')->isUPC($query);
    }

    // ########################################
}