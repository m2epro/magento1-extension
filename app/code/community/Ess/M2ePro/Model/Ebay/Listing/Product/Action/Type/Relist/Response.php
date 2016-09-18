<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Relist_Response
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
{
    //########################################

    public function processSuccess(array $response, array $responseParams = array())
    {
        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
            'ebay_item_id' => $this->createEbayItem($response['ebay_item_id'])->getId()
        );

        if ($this->getConfigurator()->isAllAllowed()) {
            $data['synch_status'] = Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_OK;
            $data['synch_reasons'] = NULL;
        }

        $data = $this->appendStatusHiddenValue($data);
        $data = $this->appendStatusChangerValue($data, $responseParams);

        $data = $this->appendOnlineBidsValue($data);
        $data = $this->appendOnlineQtyValues($data);
        $data = $this->appendOnlinePriceValues($data);
        $data = $this->appendOnlineInfoDataValues($data);

        $data = $this->appendOutOfStockValues($data);
        $data = $this->appendItemFeesValues($data, $response);
        $data = $this->appendStartDateEndDateValues($data, $response);
        $data = $this->appendGalleryImagesValues($data, $response, $responseParams);

        $data = $this->removeConditionNecessary($data);

        $data = $this->appendIsVariationMpnFilledValue($data);
        $data = $this->appendVariationsThatCanNotBeDeleted($data, $response);

        if (isset($data['additional_data'])) {
            $data['additional_data'] = json_encode($data['additional_data']);
        }

        $this->getListingProduct()->addData($data)->save();

        $this->updateVariationsValues(false);
    }

    public function processAlreadyActive(array $response, array $responseParams = array())
    {
        $responseParams['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;
        $this->processSuccess($response,$responseParams);
    }

    //########################################

    public function markAsPotentialDuplicate()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        $additionalData['last_failed_action_data'] = array(
            'native_request_data' => $this->getRequestData()->getData(),
            'previous_status' => $this->getListingProduct()->getStatus(),
            'action' => Ess_M2ePro_Model_Listing_Product::ACTION_RELIST,
            'request_time' => Mage::helper('M2ePro')->getCurrentGmtDate(),
        );

        $this->getListingProduct()->addData(array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
            'additional_data' => json_encode($additionalData),
        ))->save();

        $this->getEbayListingProduct()->updateVariationsStatus();
    }

    public function markAsNotListedItem()
    {
        $this->getListingProduct()
             ->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)
             ->save();
    }

    public function markAsNeedUpdateConditionData()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        $additionalData['is_need_relist_condition'] = true;

        $this->getListingProduct()
             ->setData('additional_data', json_encode($additionalData))
             ->save();
    }

    //########################################

    private function removeConditionNecessary($data)
    {
        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        if (isset($data['additional_data']['is_need_relist_condition'])) {
            unset($data['additional_data']['is_need_relist_condition']);
        }

        return $data;
    }

    //########################################

    public function tryToReListItemWithFullDataAction()
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator $configurator */
        $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
        $configurator->setFullMode();
        $this->getListingProduct()->setActionConfigurator($configurator);

        $dispatcher = Mage::getModel('M2ePro/Connector_Ebay_Item_Dispatcher');
        $dispatcher->process(
            Ess_M2ePro_Model_Listing_Product::ACTION_RELIST,
            array($this->getListingProduct()),
            array(
                'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_SYNCH,
            )
        );
    }

    //########################################
}