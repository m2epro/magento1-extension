<?php

class Ess_M2ePro_Model_Amazon_Search_Settings
{
    const STEP_GENERAL_ID    = 1;
    const STEP_WORLDWIDE_ID  = 2;

    protected $_step = null;

    protected $_stepData = array();

    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct = null;

    //----------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return $this
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->_listingProduct = $listingProduct;
        return $this;
    }

    /**
     * @param $step
     * @return $this
     */
    public function setStep($step)
    {
        $this->_step = $step;
        return $this;
    }

    /**
     * @return bool
     */
    public function setNextStep()
    {
        $nextStep = (int)$this->_step + 1;

        if (!in_array($nextStep, $this->getAllowedSteps())) {
            return false;
        }

        $this->_step = $nextStep;
        return true;
    }

    /**
     * @return $this
     */
    public function resetStep()
    {
        $this->_step = null;
        return $this;
    }

    /**
     * @param array $result
     * @return $this
     */
    public function setStepData(array $result)
    {
        $this->_stepData = $result;
        return $this;
    }

    //----------------------------------

    protected function getListingProduct()
    {
        return $this->_listingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product
     */
    protected function getAmazonListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    protected function getVariationManager()
    {
        return $this->getAmazonListingProduct()->getVariationManager();
    }

    protected function getAllowedSteps()
    {
        return array(
            self::STEP_GENERAL_ID,
            self::STEP_WORLDWIDE_ID,
        );
    }

    /**
     * @return bool
     */
    public function isIdentifierValid()
    {
        $listingSource = $this->getAmazonListingProduct()->getListingSource();
        $searchGeneralId = $listingSource->getSearchGeneralId();
        $searchWorldwideId = $listingSource->getSearchWorldwideId();

        if (!$this->getIdentifierType($searchGeneralId) && !$this->getIdentifierType($searchWorldwideId)) {
            return false;
        }

        return true;
    }

    //----------------------------------

    public function process()
    {
        if (!$this->validate()) {
            return false;
        }

        if (!empty($this->_stepData['result'])) {
            $this->processResult();
            return true;
        }

        $this->_stepData = array();

        if (!$this->setNextStep()) {
            $this->setNotFoundSearchStatus();
            return true;
        }

        $query = $this->getQueryParam();

        if (empty($query)) {
            return $this->process();
        }

        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getCustomConnector(
            'Amazon_Search_Settings_'.ucfirst($this->getSearchMethod()).'_Requester',
            $this->getConnectorParams(),
            $this->getListingProduct()->getAccount()
        );

        $dispatcherObject->process($connectorObj);

        return $connectorObj->getPreparedResponseData();
    }

    //----------------------------------

    protected function processResult()
    {
        $result = $this->_stepData['result'];
        $params = $this->_stepData['params'];

        $params['search_method'] == 'byAsin' && $result = array($result);

        $searchSettingsData = array(
            'type'  => $this->getIdentifierType($params['query']),
            'value' => $params['query'],
        );

        if ($this->canPutResultToSuggestData($result)) {
            $searchSettingsData['data'] = $result;
            $this->saveStatus(
                Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_ACTION_REQUIRED,
                $searchSettingsData
            );

            return;
        }

        $result = reset($result);

        $generalId = $this->getGeneralIdFromResult($result);

        if ($this->_step == self::STEP_GENERAL_ID && $generalId !== $params['query'] &&
            (!Mage::helper('M2ePro')->isISBN($generalId) || !Mage::helper('M2ePro')->isISBN($params['query']))) {
            $this->setNotFoundSearchStatus();
            return;
        }

        $generalIdSearchInfo = array(
            'is_set_automatic' => true,
            'type'  => $searchSettingsData['type'],
            'value' => $searchSettingsData['value'],
        );

        $dataForUpdate = array(
            'general_id' => $generalId,
            'general_id_search_info' => Mage::helper('M2ePro')->jsonEncode($generalIdSearchInfo),
            'is_isbn_general_id' => Mage::helper('M2ePro')->isISBN($generalId),
            'search_settings_status' => null,
            'search_settings_data'   => null,
        );

        $this->getListingProduct()->addData($dataForUpdate)->save();

        if ($this->getVariationManager()->isRelationParentType()) {
            $this->processParentResult($result);
        }
    }

    protected function processParentResult(array $result)
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $typeModel */
        $typeModel = $this->getVariationManager()->getTypeModel();

        $attributeMatcher = $this->getAttributeMatcher($result);
        if ($attributeMatcher->isAmountEqual() && $attributeMatcher->isFullyMatched()) {
            $typeModel->setMatchedAttributes($this->getAttributeMatcher($result)->getMatchedAttributes(), false);
        }

        $typeModel->setChannelAttributesSets($result['variations']['set'], false);

        $channelVariations = array();
        foreach ($result['variations']['asins'] as $asin => $asinAttributes) {
            $channelVariations[$asin] = $asinAttributes['specifics'];
        }

        $typeModel->setChannelVariations($channelVariations);

        $this->getListingProduct()->save();

        try {
            $typeModel->getProcessor()->process();
        } catch (\Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }
    }

    //----------------------------------

    protected function validate()
    {
        if ($this->_step !== null && !in_array($this->_step, $this->getAllowedSteps())) {
            return false;
        }

        if ($this->getVariationManager()->isIndividualType()) {
            if ($this->getListingProduct()->getMagentoProduct()->isBundleType() ||
                $this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions() ||
                $this->getListingProduct()->getMagentoProduct()->isDownloadableTypeWithSeparatedLinks()
            ) {
                return false;
            }
        }

        return true;
    }

    protected function getConnectorParams()
    {
        $params = array(
            'step' => $this->_step,
            'query' => $this->getQueryParam(),
            'search_method' => $this->getSearchMethod(),
            'listing_product_id' => $this->getListingProduct()->getId(),
            'variation_bad_parent_modify_child_to_simple' => true
        );

        if ($this->getVariationManager()->isVariationParent()) {
            $params['variation_bad_parent_modify_child_to_simple'] = false;
        }

        if ($this->getSearchMethod() == 'byIdentifier') {
            $params['query_type'] = $this->getIdentifierType($this->getQueryParam());
        }

        return $params;
    }

    protected function getGeneralIdFromResult($result)
    {
        if ($this->getVariationManager()->isRelationParentType() || empty($result['requested_child_id'])) {
            return $result['general_id'];
        }

        return $result['requested_child_id'];
    }

    protected function canPutResultToSuggestData($result)
    {
        if (count($result) > 1) {
            return true;
        }

        $result = reset($result);

        if (!$this->getVariationManager()->isRelationParentType()) {
            // result matched if it is simple or variation with requested child
            if ($result['is_variation_product'] && empty($result['requested_child_id'])) {
                return true;
            }

            return false;
        }

        if ($result['is_variation_product'] && empty($result['bad_parent'])) {
            $attributeMatcher = $this->getAttributeMatcher($result);

            if (!$attributeMatcher->isAmountEqual() || !$attributeMatcher->isFullyMatched()) {
                return true;
            }

            return false;
        }

        return true;
    }

    protected function getQueryParam()
    {
        $validationHelper = Mage::helper('M2ePro');
        $amazonHelper = Mage::helper('M2ePro/Component_Amazon');

        switch ($this->_step) {
            case self::STEP_GENERAL_ID:

                $query = $this->getAmazonListingProduct()->getGeneralId();
                empty($query) && $query = $this->getAmazonListingProduct()->getListingSource()->getSearchGeneralId();

                if (!$amazonHelper->isASIN($query) && !$validationHelper->isISBN($query)) {
                    $query = null;
                }
                break;

            case self::STEP_WORLDWIDE_ID:

                $query = $this->getAmazonListingProduct()->getListingSource()->getSearchWorldwideId();

                if (!$validationHelper->isEAN($query) && !$validationHelper->isUPC($query)) {
                    $query = null;
                }
                break;

            default:

                $query = null;
        }

        return $query;
    }

    protected function getSearchMethod()
    {
        $searchMethods = array_combine(
            $this->getAllowedSteps(), array('byAsin', 'byIdentifier')
        );

        $searchMethod = $searchMethods[$this->_step];

        if ($searchMethod == 'byAsin' && Mage::helper('M2ePro')->isISBN($this->getQueryParam())) {
            $searchMethod = 'byIdentifier';
        }

        return $searchMethod;
    }

    /**
     * @return false|string
     */
    protected function getIdentifierType($identifier)
    {
        $validation = Mage::helper('M2ePro');

        return (Mage::helper('M2ePro/Component_Amazon')->isASIN($identifier) ? 'ASIN' :
               ($validation->isISBN($identifier)                             ? 'ISBN' :
               ($validation->isUPC($identifier)                              ? 'UPC'  :
               ($validation->isEAN($identifier)                              ? 'EAN'  : false))));
    }

    protected function getAttributeMatcher($result)
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute $attributeMatcher */
        $attributeMatcher = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Attribute');
        $attributeMatcher->setMagentoProduct($this->getListingProduct()->getMagentoProduct());
        $attributeMatcher->setDestinationAttributes(array_keys($result['variations']['set']));

        return $attributeMatcher;
    }

    //----------------------------------

    protected function setNotFoundSearchStatus()
    {
        $this->saveStatus( Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_NOT_FOUND);
    }

    /**
     * @return void
     */
    public function setIdentifierInvalidStatus()
    {
        $this->saveStatus(Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_IDENTIFIER_INVALID);
    }

    /**
     * @param int $status
     * @param array|null $searchSettingsData
     * @return void
     */
    private function saveStatus($status, array $searchSettingsData = null)
    {
        if ($searchSettingsData !== null) {
            $searchSettingsData = Mage::helper('M2ePro')->jsonEncode($searchSettingsData);
        }

        $this->getListingProduct()->setData('search_settings_status', $status);
        $this->getListingProduct()->setData('search_settings_data', $searchSettingsData);
        $this->getListingProduct()->save();
    }

    //----------------------------------
}
