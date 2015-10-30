<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Search_Settings
{
    const STEP_GENERAL_ID = 1;
    const STEP_MAGENTO_TITLE = 2;

    //########################################

    private $step = null;

    private $stepData = array();

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    private $listingProduct = null;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return $this
     */
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

    /**
     * @param array $result
     * @return $this
     */
    public function setStepData(array $result)
    {
        $this->stepData = $result;
        return $this;
    }

    //########################################

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

    //########################################

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
            $this->setNotFoundSearchStatus();
            return true;
        }

        $query = $this->getQueryParam();

        if (empty($query)) {
            return $this->process();
        }

        $connectorParams = array(
            'step' => $this->step,
            'query' => $this->getQueryParam(),
            'search_method' => 'byQuery',
            'listing_product_id' => $this->getListingProduct()->getId(),
        );

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');
        $connectorObj = $dispatcherObject->getConnector('settings', 'byQuery', 'requester',
                                                        $connectorParams,
                                                        $this->getListingProduct()->getAccount(),
                                                        'Ess_M2ePro_Model_Buy_Search');

        return $dispatcherObject->process($connectorObj);
    }

    //########################################

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
            $type = Mage::helper('M2ePro/Component_Buy')->isGeneralId($params['query']) ? 'sku' : false;
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

        if ($this->step == self::STEP_MAGENTO_TITLE && $result['title'] !== $params['query']) {
            $this->setNotFoundSearchStatus();
            return;
        }

        $generalId = $this->getGeneralIdFromResult($result);

        if ($this->step == self::STEP_GENERAL_ID && $generalId !== $params['query']) {
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
            'general_id_search_info' => json_encode($generalIdSearchInfo),
            'search_settings_status' => null,
            'search_settings_data'   => null,
        );

        $this->getListingProduct()->addData($dataForUpdate)->save();

        return;
    }

    //########################################

    private function validate()
    {
        if (!is_null($this->step) && !in_array($this->step, $this->getAllowedSteps())) {
            return false;
        }

        return true;
    }

    private function getGeneralIdFromResult($result)
    {
        if (!isset($result['variations'])) {
            return $result['general_id'];
        }

        reset($result['variations']['skus']);

        return key($result['variations']['skus']);
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

    //########################################

    private function getQueryParam()
    {
        $query = NULL;

        switch ($this->step) {
            case self::STEP_GENERAL_ID:

                $generalIdMode = $this->getBuyListingProduct()->getGeneralId();

                if (empty($generalIdMode)) {
                    $generalIdValue = $this->getBuyListingProduct()->getListingSource()->getSearchGeneralId();

                    Mage::helper('M2ePro/Component_Buy')
                        ->isGeneralId($generalIdValue) && $query = $generalIdValue;
                }

                break;

            case self::STEP_MAGENTO_TITLE:

                if ($this->getBuyListingProduct()->getBuyListing()->isSearchByMagentoTitleModeEnabled()) {
                    $query = $this->getBuyListingProduct()->getActualMagentoProduct()->getName();
                }

                break;
        }

        return $query;
    }

    //########################################

    private function setNotFoundSearchStatus()
    {
        $this->getListingProduct()->setData(
            'search_settings_status', Ess_M2ePro_Model_Buy_Listing_Product::SEARCH_SETTINGS_STATUS_NOT_FOUND
        );
        $this->getListingProduct()->setData('search_settings_data', null);

        $this->getListingProduct()->save();
    }

    //########################################
}