<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Ebay_Item_List_Multiple
    extends Ess_M2ePro_Model_Connector_Ebay_Item_MultipleAbstract
{
    //########################################

    public function __construct(array $params = array(), array $listingsProducts)
    {
        parent::__construct($params, $listingsProducts);

        foreach ($this->listingsProducts as $listingProduct) {
            $listingProduct->setData('synch_status', Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_OK);
            $listingProduct->setData('synch_reasons', null);

            $additionalData = $listingProduct->getAdditionalData();

            unset($additionalData['synch_template_list_rules_note']);

            if (isset($additionalData['add_to_schedule'])) {
                unset($additionalData['add_to_schedule']);
            }

            $listingProduct->setSettings('additional_data', $additionalData);

            $listingProduct->save();
        }
    }

    //########################################

    protected function getCommand()
    {
        return array('item','add','multiple');
    }

    protected function getLogsAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT;
    }

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_LIST;
    }

    //########################################

    protected function filterManualListingsProducts()
    {
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$listingProduct->isListable()) {

                $message = array(
                    // M2ePro_TRANSLATIONS
                    // Item is Listed or not available
                    parent::MESSAGE_TEXT_KEY => 'Item is Listed or not available',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->getLogger()->logListingProductMessage(
                    $listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $this->removeAndUnlockListingProduct($listingProduct);
                continue;
            }

            if ($listingProduct->isHidden()) {

                $message = array(
                    // M2ePro_TRANSLATIONS
                    // The List action cannot be executed for this Item as it has a Listed (Hidden) status. You have to stop Item manually first to run the List action for it.
                    parent::MESSAGE_TEXT_KEY => 'The List action cannot be executed for this Item as it has
                                                a Listed (Hidden) status. You have to stop Item manually first
                                                to run the List action for it.',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->getLogger()->logListingProductMessage(
                    $listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $this->removeAndUnlockListingProduct($listingProduct);
                continue;
            }

            if (!$listingProduct->getChildObject()->isSetCategoryTemplate()) {

                $message = array(
                    // M2ePro_TRANSLATIONS
                    // Categories Settings are not set
                    parent::MESSAGE_TEXT_KEY => 'Categories Settings are not set',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->getLogger()->logListingProductMessage(
                    $listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $this->removeAndUnlockListingProduct($listingProduct);
                continue;
            }

            if ($this->params['status_changer'] != Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER &&
                $theSameListingProduct = $this->getRequestObject($listingProduct)->getTheSameProductAlreadyListed()) {

                // M2ePro_TRANSLATIONS
                // There is another Item with the same eBay User ID, Product ID and Marketplace presented in "%listing_title%" (%listing_id%) Listing.
                $message = array(
                    parent::MESSAGE_TEXT_KEY => Mage::helper('M2ePro')->__(
                        'There is another Item with the same eBay User ID, '.
                        'Product ID and Marketplace presented in "%listing_title%" (%listing_id%) Listing.',
                        $theSameListingProduct->getListing()->getTitle(),
                        $theSameListingProduct->getListing()->getId()
                    ),
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->getLogger()->logListingProductMessage(
                    $listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $this->removeAndUnlockListingProduct($listingProduct);
                continue;
            }

            $failedAttributes = $this->getRequestObject($listingProduct)->getVariationAttributesWithSpacesAroundName();
            if ($failedAttributes) {
                $message = array(
                    parent::MESSAGE_TEXT_KEY => Mage::helper('M2ePro')->__(
                        'The Item cannot be updated properly on eBay because its Variational Attribute %attributes%
                        title contains a space at the start or in the end of the value which will
                        cause the further errors. Please, adjust the Attribute title to solve this issue.',
                        implode(', ', array_unique($failedAttributes))
                    ),
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->getLogger()->logListingProductMessage(
                    $listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $this->removeAndUnlockListingProduct($listingProduct);
                continue;
            }

            if ($this->getRequestObject($listingProduct)->getVariationOptionsWithSpacesAroundName()) {
                $message = array(
                    parent::MESSAGE_TEXT_KEY => Mage::helper('M2ePro')->__(
                        'The Item cannot be updated properly on eBay because its Option label(s) contain(s) a space
                        at the start or in the end of the value which will cause the further errors.
                        Please, adjust the Option label(s) to solve this issue.'
                    ),
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->getLogger()->logListingProductMessage(
                    $listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $this->removeAndUnlockListingProduct($listingProduct);
                continue;
            }
        }
    }

    protected function getRequestData()
    {
        $data = array(
            'products' => array()
        );

        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $this->getRequestObject($listingProduct)->resetVariations();

            $tempData = $this->getRequestObject($listingProduct)->getData();
            $this->logRequestMessages($listingProduct);

            $data['products'][$listingProduct->getId()] =
                $this->buildRequestDataObject($listingProduct,$tempData)->getData();
        }

        return $data;
    }

    // ---------------------------------------

    protected function prepareResponseData($response)
    {
        if ($this->resultType == parent::MESSAGE_TYPE_ERROR || !isset($response['result'])) {
            return $response;
        }

        foreach ($response['result'] as $listingProductId => $listingsProductResult) {

            if (!$this->isResultSuccess($listingsProductResult)) {
                continue;
            }

            $listingProduct = $this->getListingProduct($listingProductId);
            $messages = isset($listingsProductResult['messages']) ? $listingsProductResult['messages'] : array();

            $this->getResponseObject($listingProduct)->processSuccess($listingsProductResult, array(
                'is_images_upload_error' => $this->isImagesUploadFailed($messages)
            ));

            $message = array(
                // M2ePro_TRANSLATIONS
                // Item was successfully Listed
                parent::MESSAGE_TEXT_KEY => 'Item was successfully Listed',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
            );

            $this->getLogger()->logListingProductMessage(
                $listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );
        }

        return $response;
    }

    //########################################

    protected function isResponseValid($response)
    {
        if (parent::isResponseValid($response)) {
            return true;
        }

        $this->processAsPotentialDuplicate();
        return false;
    }

    protected function processResponseInfo($responseInfo)
    {
        try {
            parent::processResponseInfo($responseInfo);
        } catch (Exception $exception) {

            if (strpos($exception->getMessage(), 'code:34') === false ||
                $this->account->getChildObject()->isModeSandbox()) {
                throw $exception;
            }

            $this->processAsPotentialDuplicate();
        }
    }

    protected function processAsPotentialDuplicate()
    {
        foreach ($this->listingsProducts as $listingProduct) {

            $this->getResponseObject($listingProduct)->markAsPotentialDuplicate();

            $message = array(
                parent::MESSAGE_TEXT_KEY => 'An error occured while Listing the Item. '.
                    'The Item has been blocked. The next M2E Pro Synchronization will resolve the problem.',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_WARNING
            );

            $this->getLogger()->logListingProductMessage($listingProduct, $message);
        }
    }

    //########################################
}