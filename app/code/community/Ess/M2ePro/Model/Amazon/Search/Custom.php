<?php

class Ess_M2ePro_Model_Amazon_Search_Custom
{
    const SEARCH_METHOD_BY_IDENTIFIER = 'byIdentifier';
    const SEARCH_METHOD_BY_ASIN = 'byAsin';

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    private $listingProduct = null;
    /** @var string */
    private $query = null;

    /**
     * @return $this
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->listingProduct = $listingProduct;

        return $this;
    }

    /**
     * @param $query
     * @return $this
     */
    public function setQuery($query)
    {
        $this->query = (string)$query;

        return $this;
    }

    public function process()
    {
        /** @var Ess_M2ePro_Model_Amazon_Connector_Dispatcher $dispatcherObject */
        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');

        $searchMethod = $this->getSearchMethod();
        if ($searchMethod === self::SEARCH_METHOD_BY_IDENTIFIER) {
            /** @var Ess_M2ePro_Model_Amazon_Search_Custom_ByIdentifier_Requester $connectorObj */
            $connectorObj = $dispatcherObject->getCustomConnector(
                'Amazon_Search_Custom_ByIdentifier_Requester',
                $this->getConnectorParams(),
                $this->listingProduct->getAccount()
            );
        } else {
            /** @var Ess_M2ePro_Model_Amazon_Search_Custom_ByAsin_Requester $connectorObj */
            $connectorObj = $dispatcherObject->getCustomConnector(
                'Amazon_Search_Custom_ByAsin_Requester',
                $this->getConnectorParams(),
                $this->listingProduct->getAccount()
            );
        }

        $dispatcherObject->process($connectorObj);

        return $this->prepareResult($connectorObj->getPreparedResponseData());
    }

    protected function getConnectorParams()
    {
        $searchMethod = $this->getSearchMethod();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $this->listingProduct->getChildObject();
        $isModifyChildToSimple = !$amazonListingProduct->getVariationManager()->isRelationParentType();

        $params = array(
            'variation_bad_parent_modify_child_to_simple' => $isModifyChildToSimple,
        );

        $params['query'] = $this->getStrippedQuery();

        if ($searchMethod == self::SEARCH_METHOD_BY_IDENTIFIER) {
            $params['query_type'] = $this->getIdentifierType();
        }

        return $params;
    }

    /**
     * @return string
     */
    private function getSearchMethod()
    {
        $validationHelper = Mage::helper('M2ePro');
        $amazonHelper     = Mage::helper('M2ePro/Component_Amazon');
        $strippedQuery    = $this->getStrippedQuery();

        if ($amazonHelper->isASIN($strippedQuery)) {
            return self::SEARCH_METHOD_BY_ASIN;
        }

        if ($validationHelper->isEAN($strippedQuery) ||
            $validationHelper->isUPC($strippedQuery) ||
            $validationHelper->isISBN($strippedQuery)
        ) {
            return self::SEARCH_METHOD_BY_IDENTIFIER;
        }

        throw new LogicException('Identifier has unresolved type');
    }

    /**
     * @return string|false
     */
    protected function getIdentifierType()
    {
        $query = $this->getStrippedQuery();
        $identifierType = $this->resolveIdentifierType($query);

        return $identifierType !== null ? $identifierType : false;
    }

    /**
     * @param string $identifier
     * @return string|null
     */
    public function resolveIdentifierType($identifier)
    {
        if (Mage::helper('M2ePro/Component_Amazon')->isASIN($identifier)) {
            return 'ASIN';
        }

        /** @var Ess_M2ePro_Helper_Data $validationHelper */
        $validationHelper = Mage::helper('M2ePro');
        if ($validationHelper->isISBN($identifier)) {
            return 'ISBN';
        }

        if ($validationHelper->isUPC($identifier)) {
            return 'UPC';
        }

        if ($validationHelper->isEAN($identifier)) {
            return 'EAN';
        }

        return null;
    }

    protected function prepareResult($searchData)
    {
        $connectorParams = $this->getConnectorParams();

        if (
            $searchData !== false
            && $this->getSearchMethod() == self::SEARCH_METHOD_BY_ASIN
        ) {
            if (is_array($searchData) && !empty($searchData)) {
                $searchData = array($searchData);
            } else if ($searchData === null) {
                $searchData = array();
            }
        }

        $type = $this->getIdentifierType();

        return array(
            'type'  => $type,
            'value' => $connectorParams['query'],
            'data'  => $searchData,
        );
    }

    protected function getStrippedQuery()
    {
        return str_replace('-', '', $this->query);
    }
}