<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_ProcessActionsResults
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'amazon/listing/product/process_actions_results';

    //####################################

    protected function performActions()
    {
        $this->completeExpiredActions();
        $this->executeCompletedRequestsPendingSingle();
    }

    //####################################

    protected function completeExpiredActions()
    {
        /** @var Ess_M2ePro_Model_Resource_Amazon_Listing_Product_Action_Processing_Collection $actionCollection */
        $actionCollection = Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Action_Processing_Collection');
        $actionCollection->addFieldToFilter('request_pending_single_id', array('notnull' => true));
        $actionCollection->getSelect()->joinLeft(
            array('rps' => Mage::getResourceModel('M2ePro/Request_Pending_Single')->getMainTable()),
            'rps.id = main_table.request_pending_single_id',
            array()
        );
        $actionCollection->addFieldToFilter('rps.id', array('null' => true));

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Processing[] $actions */
        $actions = $actionCollection->getItems();

        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
        $message->initFromPreparedData(
            'Request wait timeout exceeded.',
            Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
        );

        foreach ($actions as $action) {
            $this->completeAction($action, array('messages' => array($message->asArray())));
        }
    }

    protected function executeCompletedRequestsPendingSingle()
    {
        $requestIds = Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Action_Processing')
            ->getUniqueRequestPendingSingleIds();
        if (empty($requestIds)) {
            return;
        }

        $requestPendingSingleCollection = Mage::getResourceModel('M2ePro/Request_Pending_Single_Collection');
        $requestPendingSingleCollection->addFieldToFilter('id', array('in' => $requestIds));
        $requestPendingSingleCollection->addFieldToFilter('is_completed', 1);

        /** @var Ess_M2ePro_Model_Request_Pending_Single[] $requestPendingSingleObjects */
        $requestPendingSingleObjects = $requestPendingSingleCollection->getItems();
        if (empty($requestPendingSingleObjects)) {
            return;
        }

        foreach ($requestPendingSingleObjects as $requestId => $requestPendingSingle) {
            $actionCollection = Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Action_Processing_Collection');
            $actionCollection->setRequestPendingSingleIdFilter($requestId);
            $actionCollection->setInProgressFilter();

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Processing[] $actions */
            $actions = $actionCollection->getItems();

            $resultData     = $requestPendingSingle->getResultData();
            $resultMessages = $requestPendingSingle->getResultMessages();

            foreach ($actions as $action) {
                $listingProductId = $action->getListingProductId();

                $resultActionData = $this->getResponseData($resultData, $listingProductId);
                $resultActionData['messages'] = $this->getResponseMessages(
                    $resultData, $resultMessages, $listingProductId
                );

                $this->completeAction($action, $resultActionData, $requestPendingSingle->getData('create_date'));
            }

            $requestPendingSingle->deleteInstance();
        }
    }

    //####################################

    protected function getResponseData(array $responseData, $listingProductId)
    {
        $data = array();

        if (!empty($responseData['asins'][$listingProductId.'-id'])) {
            $data['asins'] = $responseData['asins'][$listingProductId.'-id'];
        }

        return $data;
    }

    protected function getResponseMessages(array $responseData, array $responseMessages, $relatedId)
    {
        $messages = $responseMessages;

        if (!empty($responseData['messages'][0])) {
            $messages = array_merge($messages, $responseData['messages']['0']);
        }

        if (!empty($responseData['messages']['0-id'])) {
            $messages = array_merge($messages, $responseData['messages']['0-id']);
        }

        if (!empty($responseData['messages'][$relatedId.'-id'])) {
            $messages = array_merge($messages, $responseData['messages'][$relatedId.'-id']);
        }

        return $messages;
    }

    protected function completeAction(
        Ess_M2ePro_Model_Amazon_Listing_Product_Action_Processing $action,
        array $data,
        $requestTime = null
    ) {
        try {
            $processing = $action->getProcessing();

            $processing->setSettings('result_data', $data);
            $processing->setData('is_completed', 1);

            if ($requestTime !== null) {
                $processingParams = $processing->getParams();
                $processingParams['request_time'] = $requestTime;
                $processing->setSettings('params', $processingParams);
            }

            $processing->save();
        } catch (\Exception $exception) {
            $this->processTaskException($exception);
        }

        $action->deleteInstance();
    }

    //####################################
}
