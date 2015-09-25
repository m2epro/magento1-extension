<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Search_Settings
{
    const STEP_GENERAL_ID = 1;
    const STEP_MAGENTO_TITLE = 2;

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
     * @return Ess_M2ePro_Model_Buy_Listing_Product
     */
    private function getBuyListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    private function getAllowedSteps()
    {
        return array(
            self::STEP_GENERAL_ID,
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
                'search_settings_status', Ess_M2ePro_Model_Buy_Listing_Product::SEARCH_SETTINGS_STATUS_NOT_FOUND
            );
            $this->getListingProduct()->setData('search_settings_data', null);

            $this->getListingProduct()->save();

            return true;
        }

        $query = $this->getQueryParam();

        if (empty($query)) {
            return $this->process();
        }

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');
        $connectorObj = $dispatcherObject->getConnector('settings', $this->getSearchMethod(), 'requester',
                                                        $this->getConnectorParams(),
                                                        $this->getListingProduct()->getAccount(),
                                                        'Ess_M2ePro_Model_Buy_Search');

        return $dispatcherObject->process($connectorObj);
    }

    // ########################################

    private function processResult()
    {
        $result = $this->stepData['result'];
        $params = $this->stepData['params'];

        if ($this->step == self::STEP_MAGENTO_TITLE) {
            $tempResult = $this->filterReceivedItemsFullTitleMatch($result);
            count($tempResult) == 1 && $result = $tempResult;
        }

        $type = 'string';
        if ($this->step != self::STEP_MAGENTO_TITLE) {
            $type = strtolower($this->getSearchType());
        }

        $searchSettingsData = array(
            'type'  => $type,
            'value' => $params['query'],
        );

        if ($this->canPutResultToSuggestData($result)) {
            $searchSettingsData['data'] = $result;

            $this->getListingProduct()->setData(
                'search_settings_status',
                Ess_M2ePro_Model_Buy_Listing_Product::SEARCH_SETTINGS_STATUS_ACTION_REQUIRED
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

        return;
    }

    // ########################################

    private function validate()
    {
        if (!is_null($this->step) && !in_array($this->step, $this->getAllowedSteps())) {
            return false;
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
        );

        if ($this->getSearchMethod() == 'byIdentifier') {
            $params['search_type'] = $this->getSearchType();
        }

        return $params;
    }

    private function getGeneralIdFromResult($result)
    {
        if (!isset($result[0]['variations'])) {
            return $result[0]['general_id'];
        }

        reset($result[0]['variations']['skus']);

        return key($result[0]['variations']['skus']);
    }

    private function canPutResultToSuggestData($result)
    {
        if (count($result) > 1) {
            return true;
        }

        if (isset($result[0]['variations']) && count($result[0]['variations']['skus']) != 1) {
            return true;
        }

        return false;
    }

    private function filterReceivedItemsFullTitleMatch($results)
    {
        $return = array();

        $magentoProductTitle = $this->getBuyListingProduct()->getActualMagentoProduct()->getName();
        $magentoProductTitle = trim(strtolower($magentoProductTitle));

        foreach ($results as $item) {
            $itemTitle = trim(strtolower($item['title']));
            if ($itemTitle == $magentoProductTitle) {
                $return[] = $item;
            }
        }

        return $return;
    }

    // ########################################

    private function getQueryParam()
    {
        switch ($this->step) {
            case self::STEP_GENERAL_ID:

                $query = $this->getBuyListingProduct()->getGeneralId();
                empty($query) && $query = $this->getBuyListingProduct()->getListingSource()->getSearchGeneralId();

                break;

            case self::STEP_MAGENTO_TITLE:

                $query = false;

                if ($this->getBuyListingProduct()->getBuyListing()->isSearchByMagentoTitleModeEnabled()) {
                    $query = $this->getBuyListingProduct()->getActualMagentoProduct()->getName();
                }

                break;

            default:

                $query = null;
        }

        return $query;
    }

    private function getSearchMethod()
    {
        if ($this->step == self::STEP_GENERAL_ID) {

            if ($this->getBuyListingProduct()->getBuyListing()->isGeneralIdSellerSkuMode()) {
                return 'bySellerSku';
            }

            if ($this->getBuyListingProduct()->getBuyListing()->isGeneralIdWorldwideMode() ||
                $this->getBuyListingProduct()->getBuyListing()->isGeneralIdGeneralIdMode()
            ) {
                return 'byIdentifier';
            }
        }

        return 'byQuery';
    }

    private function getSearchType()
    {
        /* @var $listing Ess_M2ePro_Model_Buy_Listing */
        $listing = $this->listingProduct->getListing()->getChildObject();

        $searchType = false;

        if ($listing->isGeneralIdGeneralIdMode()) {
            $searchType = Ess_M2ePro_Model_Connector_Buy_Search_ByIdentifier_ItemsRequester::SEARCH_TYPE_GENERAL_ID;
        }

        if ($listing->isGeneralIdWorldwideMode()) {
            $searchType = Ess_M2ePro_Model_Connector_Buy_Search_ByIdentifier_ItemsRequester::SEARCH_TYPE_UPC;
        }

        return $searchType;
    }

    // ########################################
}