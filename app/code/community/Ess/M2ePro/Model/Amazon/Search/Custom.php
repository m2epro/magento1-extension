<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_Custom
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
        $this->query = (string)$query;
        return $this;
    }

    // ########################################

    public function process()
    {
        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
        $connectorObj = $dispatcherObject->getConnector('custom', $this->getSearchMethod(), 'requester',
                                                        $this->getConnectorParams(),
                                                        $this->listingProduct->getAccount(),
                                                        'Ess_M2ePro_Model_Amazon_Search');

        $searchData = $dispatcherObject->process($connectorObj);
        return $this->prepareResult($searchData);
    }

    // ########################################

    private function getConnectorParams()
    {
        $searchMethod = $this->getSearchMethod();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $this->listingProduct->getChildObject();
        $isModifyChildToSimple = !$amazonListingProduct->getVariationManager()->isRelationParentType();

        $params = array(
            'variation_bad_parent_modify_child_to_simple' => $isModifyChildToSimple,
        );

        if ($searchMethod == 'byQuery') {
            $params['query'] = $this->query;
        } else {
            $params['query'] = $this->getStrippedQuery();
        }

        if ($searchMethod == 'byIdentifier') {
            $params['query_type'] = $this->getIdentifierType();
        }

        return $params;
    }

    private function getSearchMethod()
    {
        $validationHelper = Mage::helper('M2ePro');
        $amazonHelper     = Mage::helper('M2ePro/Component_Amazon');
        $strippedQuery    = $this->getStrippedQuery();

        if ($amazonHelper->isASIN($strippedQuery)) {
            return 'byAsin';
        }

        if ($validationHelper->isEAN($strippedQuery) ||
            $validationHelper->isUPC($strippedQuery) ||
            $validationHelper->isISBN($strippedQuery)
        ) {
            return 'byIdentifier';
        }

        return 'byQuery';
    }

    private function getIdentifierType()
    {
        $query = $this->getStrippedQuery();

        $validationHelper = Mage::helper('M2ePro');

        return (Mage::helper('M2ePro/Component_Amazon')->isASIN($query) ? 'ASIN' :
               ($validationHelper->isISBN($query)                       ? 'ISBN' :
               ($validationHelper->isUPC($query)                        ? 'UPC'  :
               ($validationHelper->isEAN($query)                        ? 'EAN'  : false))));
    }

    private function prepareResult($searchData)
    {
        $connectorParams = $this->getConnectorParams();

        if ($this->getSearchMethod() == 'byQuery') {
            $type = 'string';
        } else {
            $type = $this->getIdentifierType();
        }

        if ($searchData !== false && $this->getSearchMethod() == 'byAsin') {
            if (is_null($searchData)) {
                $searchData = array();
            } else {
                $searchData = array($searchData);
            }
        }

        return array(
            'type'  => $type,
            'value' => $connectorParams['query'],
            'data'  => $searchData,
        );
    }

    private function getStrippedQuery()
    {
        return str_replace('-', '', $this->query);
    }

    // ########################################
}