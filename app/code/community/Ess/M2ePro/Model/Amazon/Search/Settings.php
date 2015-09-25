<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_Settings
{
    const STEP_GENERAL_ID    = 1;
    const STEP_WORLDWIDE_ID  = 2;
    const STEP_MAGENTO_TITLE = 3;

    // ########################################

    private $step = null;

    private $stepData = array();

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    private $listingProduct = null;

    // ########################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->listingProduct = $listingProduct;
        return $this;
    }

    public function setStep($step)
    {
        $this->step = $step;
        return $this;
    }

    public function setNextStep()
    {
        $nextStep = (int)$this->step + 1;

        if (!in_array($nextStep, $this->getAllowedSteps())) {
            return false;
        }

        $this->step = $nextStep;
        return true;
    }

    public function resetStep()
    {
        $this->step = null;
        return $this;
    }

    public function setStepData(array $result)
    {
        $this->stepData = $result;
        return $this;
    }

    // ########################################

    private function getListingProduct()
    {
        return $this->listingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product
     */
    private function getAmazonListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    private function getVariationManager()
    {
        return $this->getAmazonListingProduct()->getVariationManager();
    }

    private function getAllowedSteps()
    {
        return array(
            self::STEP_GENERAL_ID,
            self::STEP_WORLDWIDE_ID,
            self::STEP_MAGENTO_TITLE
        );
    }

    // ########################################

    public function process()
    {
        if (!$this->validate()) {
            return false;
        }

        if (!empty($this->stepData['result'])) {
            $this->processResult();
            return true;
        }

        $this->stepData = array();

        if (!$this->setNextStep()) {
            $this->getListingProduct()->setData(
                'search_settings_status', Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_NOT_FOUND
            );
            $this->getListingProduct()->setData('search_settings_data', null);

            $this->getListingProduct()->save();

            return true;
        }

        $query = $this->getQueryParam();

        if (empty($query)) {
            return $this->process();
        }

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
        $connectorObj = $dispatcherObject->getConnector('settings', $this->getSearchMethod(), 'requester',
                                                        $this->getConnectorParams(),
                                                        $this->getListingProduct()->getAccount(),
                                                        'Ess_M2ePro_Model_Amazon_Search');

        return $dispatcherObject->process($connectorObj);
    }

    // ########################################

    private function processResult()
    {
        $result = $this->stepData['result'];
        $params = $this->stepData['params'];

        $params['search_method'] == 'byAsin' && $result = array($result);

        if ($this->step == self::STEP_MAGENTO_TITLE) {
            $tempResult = $this->filterReceivedItemsFullTitleMatch($result);
            count($tempResult) == 1 && $result = $tempResult;
        }

        $type = 'string';
        if ($this->step != self::STEP_MAGENTO_TITLE) {
            $type = $this->getIdentifierType($params['query']);
        }

        $searchSettingsData = array(
            'type'  => $type,
            'value' => $params['query'],
        );

        if ($this->canPutResultToSuggestData($result)) {
            $searchSettingsData['data'] = $result;

            $this->getListingProduct()->setData(
                'search_settings_status',
                Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_ACTION_REQUIRED
            );
            $this->getListingProduct()->setSettings('search_settings_data', $searchSettingsData);

            $this->getListingProduct()->save();

            return;
        }

        $result = reset($result);

        $generalId = $this->getGeneralIdFromResult($result);

        $generalIdSearchInfo = array(
            'is_set_automatic' => true,
            'type'  => $searchSettingsData['type'],
            'value' => $searchSettingsData['value'],
        );

        $dataForUpdate = array(
            'general_id' => $generalId,
            'general_id_search_info' => json_encode($generalIdSearchInfo),
            'is_isbn_general_id' => Mage::helper('M2ePro')->isISBN($generalId),
            'search_settings_status' => null,
            'search_settings_data'   => null,
        );

        $this->getListingProduct()->addData($dataForUpdate)->save();

        if ($this->getVariationManager()->isRelationParentType()) {
            $this->processParentResult($result);
        }

        return;
    }

    private function processParentResult(array $result)
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $typeModel */
        $typeModel = $this->getVariationManager()->getTypeModel();

        $attributeMatcher = $this->getAttributeMatcher($result);
        if ($attributeMatcher->isAmountEqual() && $attributeMatcher->isFullyMatched()) {
            $typeModel->setMatchedAttributes($this->getAttributeMatcher($result)->getMatchedAttributes(), false);
        }

        $typeModel->setChannelAttributesSets($result['variations']['set'], false);

        $channelVariations = array();
        foreach($result['variations']['asins'] as $asin => $asinAttributes) {
            $channelVariations[$asin] = $asinAttributes['specifics'];
        }
        $typeModel->setChannelVariations($channelVariations);

        $this->getListingProduct()->save();

        $typeModel->getProcessor()->process();
    }

    // ########################################

    private function validate()
    {
        if (!is_null($this->step) && !in_array($this->step, $this->getAllowedSteps())) {
            return false;
        }

        if ($this->getVariationManager()->isIndividualType()) {
            if ($this->getListingProduct()->getMagentoProduct()->isBundleType() ||
                $this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()
            ) {
                return false;
            }
        }

        return true;
    }

    private function getConnectorParams()
    {
        $params = array(
            'step' => $this->step,
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

    private function getGeneralIdFromResult($result)
    {
        if ($this->getVariationManager()->isRelationParentType() || empty($result['requested_child_id'])) {
            return $result['general_id'];
        }

        return $result['requested_child_id'];
    }

    private function canPutResultToSuggestData($result)
    {
        if (count($result) > 1) {
            return true;
        }

        $result = reset($result);

        if (!$this->getVariationManager()->isRelationParentType()) {
            //result matched if it is simple or variation with requested child
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

    private function getQueryParam()
    {
        $validationHelper = Mage::helper('M2ePro');
        $amazonHelper = Mage::helper('M2ePro/Component_Amazon');

        switch ($this->step) {
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

            case self::STEP_MAGENTO_TITLE:

                $query = null;

                if ($this->getAmazonListingProduct()->getAmazonListing()->isSearchByMagentoTitleModeEnabled()) {
                    $query = $this->getAmazonListingProduct()->getActualMagentoProduct()->getName();
                }

                break;

            default:

                $query = null;
        }

        return $query;
    }

    private function getSearchMethod()
    {
        $searchMethods = array_combine(
            $this->getAllowedSteps(), array('byAsin', 'byIdentifier', 'byQuery')
        );

        $searchMethod = $searchMethods[$this->step];

        if ($searchMethod == 'byAsin' && Mage::helper('M2ePro')->isISBN($this->getQueryParam())) {
            $searchMethod = 'byIdentifier';
        }

        return $searchMethod;
    }

    private function getIdentifierType($identifier)
    {
        $validation = Mage::helper('M2ePro');

        return (Mage::helper('M2ePro/Component_Amazon')->isASIN($identifier) ? 'ASIN' :
               ($validation->isISBN($identifier)                             ? 'ISBN' :
               ($validation->isUPC($identifier)                              ? 'UPC'  :
               ($validation->isEAN($identifier)                              ? 'EAN'  : false))));
    }

    private function filterReceivedItemsFullTitleMatch($results)
    {
        $return = array();

        $magentoProductTitle = $this->getAmazonListingProduct()->getActualMagentoProduct()->getName();
        $magentoProductTitle = trim(strtolower($magentoProductTitle));

        foreach ($results as $item) {
            $itemTitle = trim(strtolower($item['title']));
            if ($itemTitle == $magentoProductTitle) {
                $return[] = $item;
            }
        }

        return $return;
    }

    private function getAttributeMatcher($result)
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute $attributeMatcher */
        $attributeMatcher = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Attribute');
        $attributeMatcher->setMagentoProduct($this->getListingProduct()->getMagentoProduct());
        $attributeMatcher->setDestinationAttributes(array_keys($result['variations']['set']));

        return $attributeMatcher;
    }

    // ########################################
}