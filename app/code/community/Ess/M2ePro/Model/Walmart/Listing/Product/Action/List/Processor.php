<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processor as ActionProcessor;
use Ess_M2ePro_Model_Walmart_Listing_Product_Action_ProcessingList as ProcessingList;
use Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processing as ActionProcessing;
use Ess_M2ePro_Model_Connector_Connection_Response_Message as ResponseMessage;
use Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Action_ProcessingList_Collection as ProcessingListCollection;

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_List_Processor
{
    const LIST_PRIORITY = 25;

    const PENDING_REQUEST_MAX_LIFE_TIME = 86400;

    const FIRST_CONNECTION_ERROR_DATE_REGISTRY_KEY = '/walmart/listing/product/action/first_connection_error/date/';

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     * @throws Zend_Db_Statement_Exception
     */
    public function process()
    {
        $this->executeReadyForList();
        $this->executeCheckListResults();

        $this->executeReadyForRelist();
        $this->executeCheckRelistResults();
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     * @throws Zend_Db_Statement_Exception
     */
    protected function executeReadyForList()
    {
        $throttlingManager = Mage::getModel('M2ePro/Walmart_ThrottlingManager');

        /** @var Ess_M2ePro_Model_Resource_Account_Collection $accountCollection */
        $accountCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Account');
        foreach ($accountCollection->getItems() as $account) {
            /** @var Ess_M2ePro_Model_Account $account */

            $availableRequestsCount = $throttlingManager->getAvailableRequestsCount(
                $account->getId(), ActionProcessor::FEED_TYPE_UPDATE_DETAILS
            );

            if ($availableRequestsCount <= 0) {
                continue;
            }

            $feedsPacks = array();

            $this->fillFeedsPacks(
                $feedsPacks,
                $this->getScheduledActionsDataStatement($account)
            );

            $actionsDataForProcessing = $this->processExistedSkus($feedsPacks);

            foreach ($actionsDataForProcessing as $accountId => $accountPacks) {
                foreach ($accountPacks as $listingsProductsData) {
                    if (empty($listingsProductsData)) {
                        continue;
                    }

                    $this->initProcessingActions($listingsProductsData);
                    $this->prepareScheduledActions($listingsProductsData);
                }
            }

            foreach ($feedsPacks as $accountId => $accountPacks) {
                $throttlingManager->registerRequests(
                    $accountId, ActionProcessor::FEED_TYPE_UPDATE_DETAILS, count($accountPacks)
                );
            }
        }

        $this->prepareProcessingActions();

        $fullyPreparedGroupHashes = Mage::getResourceModel('M2ePro/Walmart_Listing_Product_Action_Processing')
            ->getFullyPreparedGroupHashes();

        foreach ($fullyPreparedGroupHashes as $groupHash) {

            /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Action_Processing_Collection $actionCollection */
            $actionCollection = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Processing')
                ->getCollection()
                ->addFieldToFilter('group_hash', $groupHash)
                ->addFieldToFilter('is_prepared', 1)
                ->addFieldToFilter('type', ActionProcessing::TYPE_ADD)
                ->addFieldToFilter('request_pending_single_id', array('null' => true));

            /** @var ActionProcessing[] $processingActions */
            $processingActions = $actionCollection->getItems();
            if (empty($processingActions)) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
            $listingProductCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
            $listingProductCollection->addFieldToFilter(
                'id', $actionCollection->getColumnValues('listing_product_id')
            );

            $createProcessingActions = array();
            $updateProcessingActions = array();

            foreach ($processingActions as $processingAction) {
                /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
                $listingProduct = $listingProductCollection->getItemById($processingAction->getListingProductId());
                /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
                $walmartListingProduct = $listingProduct->getChildObject();

                if ($walmartListingProduct->getSku()) {
                    $updateProcessingActions[$processingAction->getId()] = $processingAction;
                } else {
                    $createProcessingActions[$processingAction->getId()] = $processingAction;
                }
            }

            if (!empty($createProcessingActions)) {
                $this->processGroupedProcessingActions(
                    $createProcessingActions, array('product', 'add', 'entities')
                );
            }

            if (!empty($updateProcessingActions)) {
                $this->processGroupedProcessingActions(
                    $updateProcessingActions, array('product', 'update', 'entities')
                );
            }
        }
    }

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function executeCheckListResults()
    {
        $requestIds = Mage::getResourceModel('M2ePro/Walmart_Listing_Product_Action_Processing')
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

            /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Action_Processing_Collection $actionCollection */
            $actionCollection = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Processing')->getCollection();
            $actionCollection
                ->setInProgressFilter()
                ->addFieldToFilter('type', ActionProcessing::TYPE_ADD)
                ->getSelect()->distinct()->joinInner(
                    array('apl' => Mage::getModel('M2ePro/Walmart_Listing_Product_Action_ProcessingList')
                                            ->getResource()->getMainTable()),
                    'apl.listing_product_id = main_table.listing_product_id',
                    array()
                );

            $actionCollection
                ->setRequestPendingSingleIdFilter($requestId)
                ->addFieldToFilter('apl.stage', ProcessingList::STAGE_LIST_DETAILS);

            /** @var ActionProcessing[] $processingActions */
            $processingActions = $actionCollection->getItems();
            if (empty($processingActions)) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
            $listingProductCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
            $listingProductCollection->addFieldToFilter(
                'id', $actionCollection->getColumnValues('listing_product_id')
            );

            $resultMessages = $requestPendingSingle->getResultMessages();
            $resultData     = $requestPendingSingle->getResultData();

            foreach ($processingActions as $processingAction) {
                /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
                $listingProduct = $listingProductCollection->getItemById($processingAction->getListingProductId());

                $resultActionData = array();

                //worker may return different data structure
                if (isset($resultData[$processingAction->getListingProductId().'-id'])) {
                    $resultActionData = $resultData[$processingAction->getListingProductId().'-id'];
                } elseif (isset($resultData['data'][$processingAction->getListingProductId().'-id'])) {
                    $resultActionData = $resultData['data'][$processingAction->getListingProductId().'-id'];
                }

                if (empty($resultActionData['errors'])) {
                    $resultActionData['errors'] = array();
                }

                if (!empty($resultMessages)) {
                    $resultActionData['errors'] = array_merge($resultActionData['errors'], $resultMessages);
                }

                if (empty($resultActionData['errors']) && empty($resultActionData['wpid'])) {
                    $message = Mage::helper('M2ePro')->__(
                        'The Item was not listed due to the unexpected error on Walmart side.
                        Please try to list this Item later.'
                    );

                    $resultActionData['errors'][] = array(
                        ResponseMessage::TYPE_KEY   => Ess_M2ePro_Model_Response_Message::TYPE_ERROR,
                        ResponseMessage::TEXT_KEY   => $message,
                        ResponseMessage::SENDER_KEY => ResponseMessage::SENDER_COMPONENT,
                        ResponseMessage::CODE_KEY   => '',
                    );
                }

                $processing = $processingAction->getProcessing();
                $processing->setSettings('result_data', $resultActionData);
                $processing->save();

                /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_List_ProcessingRunner $processingRunner */
                $processingRunner = Mage::getModel($processing->getModel());
                $processingRunner->setProcessingObject($processing);
                $processingRunner->setListingProduct($listingProduct);

                if (!$processingRunner->processAddResult()) {
                    $processingRunner->complete();
                    $processingAction->deleteInstance();
                    continue;
                }

                /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Action_ProcessingList $listResource */
                $listResource = Mage::getResourceModel('M2ePro/Walmart_Listing_Product_Action_ProcessingList');
                $listResource->markAsRelistInventoryReady(array($processingAction->getListingProductId()));
            }

            $requestPendingSingle->deleteInstance();
        }
    }

    // ---------------------------------------

    /**
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function executeReadyForRelist()
    {
        /** @var Ess_M2ePro_Model_Walmart_ThrottlingManager $throttlingManager */
        $throttlingManager = Mage::getModel('M2ePro/Walmart_ThrottlingManager');

        /** @var Ess_M2ePro_Model_Resource_Account_Collection $accountCollection */
        $accountCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Account');
        foreach ($accountCollection->getItems() as $account) {
            /** @var Ess_M2ePro_Model_Account $account */

            $qtyRequestCount = $throttlingManager->getAvailableRequestsCount(
                $account->getId(), ActionProcessor::FEED_TYPE_UPDATE_QTY
            );
            $lagTimeRequestCount = $throttlingManager->getAvailableRequestsCount(
                $account->getId(), ActionProcessor::FEED_TYPE_UPDATE_LAG_TIME
            );

            if ($qtyRequestCount <= 0 || $lagTimeRequestCount <= 0) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Action_Processing_Collection $actionCollection */
            $actionCollection = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Processing')->getCollection();
            $actionCollection
                ->setInProgressFilter()
                ->addFieldToFilter('type', ActionProcessing::TYPE_ADD)
                ->getSelect()->joinInner(
                    array('apl' => Mage::getModel('M2ePro/Walmart_Listing_Product_Action_ProcessingList')
                                        ->getResource()->getMainTable()),
                    'apl.listing_product_id = main_table.listing_product_id',
                    array('processing_list_id' => 'id')
                );

            $actionCollection
                ->addFieldToFilter('apl.stage', ProcessingList::STAGE_RELIST_INVENTORY_READY)
                ->addFieldToFilter('apl.account_id', $account->getId())
                ->getSelect()->limit($this->getMaxPackSize());

            /** @var ActionProcessing[] $processingActions */
            $processingActions = $actionCollection->getItems();
            if (empty($processingActions)) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
            $listingProductCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
            $listingProductCollection->addFieldToFilter(
                'id', $actionCollection->getColumnValues('listing_product_id')
            );

            /** @var ProcessingListCollection $listCollection */
            $listCollection = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_ProcessingList')->getCollection();
            $listCollection->addFieldToFilter(
                'id', $actionCollection->getColumnValues('processing_list_id')
            );

            $itemsRequestData = array();
            foreach ($processingActions as $processingAction) {

                /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
                $listingProduct = $listingProductCollection->getItemById($processingAction->getListingProductId());
                $processingAction->setListingProduct($listingProduct);

                /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_ProcessingList $processingList */
                $processingList = $listCollection->getItemById($processingAction->getData('processing_list_id'));

                /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator $configurator */
                $configurator = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Configurator');
                $configurator->disableAll();
                $configurator->allowQty()
                             ->allowLagTime();

                $listingProduct->setActionConfigurator($configurator);
                $listingProduct->setProcessingAction($processingAction);

                $params = $processingAction->getProcessing()->getParams();
                /** @var Ess_M2ePro_Model_Walmart_Connector_Product_List_UpdateInventory_Requester $connector */
                $connector = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher')->getCustomConnector(
                    'Walmart_Connector_Product_List_UpdateInventory_Requester', $params['requester_params']
                );
                $connector->setListingProduct($listingProduct);

                $requestData = $connector->getRequestData();
                $itemsRequestData[$listingProduct->getId()] = $requestData;

                $processingList->addData(
                    array(
                        'relist_request_data'      => Mage::helper('M2ePro')->jsonEncode($requestData),
                        'relist_configurator_data' => Mage::helper('M2ePro')->jsonEncode($configurator->getData())
                    )
                );
                $processingList->save();
            }

            $throttlingManager->registerRequests($account->getId(), ActionProcessor::FEED_TYPE_UPDATE_QTY, 1);
            $throttlingManager->registerRequests($account->getId(), ActionProcessor::FEED_TYPE_UPDATE_LAG_TIME, 1);

            /** @var Ess_M2ePro_Model_Walmart_Connector_Dispatcher $dispatcher */
            $dispatcher = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');
            $connector = $dispatcher->getVirtualConnector(
                'product', 'update', 'entities',
                array('items' => $itemsRequestData), null, $account
            );

            try {
                $dispatcher->process($connector);
            } catch (Exception $exception) {
                Mage::helper('M2ePro/Module_Exception')->process($exception);

                $this->failedOnRelistAttemptCallback($actionCollection->getColumnValues('id'));
                continue;
            }

            $responseData = $connector->getResponseData();
            if (empty($responseData['processing_id'])) {
                $this->failedOnRelistAttemptCallback($actionCollection->getColumnValues('id'));
                continue;
            }

            $requestPendingSingle = Mage::getModel('M2ePro/Request_Pending_Single');
            $requestPendingSingle->setData(
                array(
                    'component'       => Ess_M2ePro_Helper_Component_Walmart::NICK,
                    'server_hash'     => $responseData['processing_id'],
                    'expiration_date' => gmdate(
                        'Y-m-d H:i:s',
                        Mage::helper('M2ePro')->getCurrentGmtDate(true)
                            + self::PENDING_REQUEST_MAX_LIFE_TIME
                    )
                )
            );
            $requestPendingSingle->save();

            /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Action_ProcessingList $processingListResource */
            $processingListResource = Mage::getResourceModel('M2ePro/Walmart_Listing_Product_Action_ProcessingList');
            $processingListResource->markAsRelistInventoryWaitingResult(
                $actionCollection->getColumnValues('listing_product_id'), $requestPendingSingle->getId()
            );
        }
    }

    /**
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function executeCheckRelistResults()
    {
        /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Action_ProcessingList $processingListResource */
        $processingListResource = Mage::getResourceModel('M2ePro/Walmart_Listing_Product_Action_ProcessingList');
        $requestIds = $processingListResource->getUniqueRelistRequestPendingSingleIds();

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

            /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Action_Processing_Collection $actionCollection */
            $actionCollection = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Processing')->getCollection();
            $actionCollection
                ->setInProgressFilter()
                ->addFieldToFilter('type', ActionProcessing::TYPE_ADD)
                ->getSelect()->joinInner(
                    array('apl' => Mage::getModel('M2ePro/Walmart_Listing_Product_Action_ProcessingList')
                        ->getResource()->getMainTable()),
                    'apl.listing_product_id = main_table.listing_product_id',
                    array('processing_list_id' => 'id')
                );

            $actionCollection
                ->addFieldToFilter('apl.stage', ProcessingList::STAGE_RELIST_INVENTORY_WAITING_RESULT)
                ->addFieldToFilter('apl.relist_request_pending_single_id', $requestPendingSingle->getId());

            /** @var ActionProcessing[] $processingActions */
            $processingActions = $actionCollection->getItems();
            if (empty($processingActions)) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
            $listingProductCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
            $listingProductCollection->addFieldToFilter(
                'id', $actionCollection->getColumnValues('listing_product_id')
            );

            /** @var ProcessingListCollection $listCollection */
            $listCollection = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_ProcessingList')->getCollection();
            $listCollection->addFieldToFilter(
                'id', $actionCollection->getColumnValues('processing_list_id')
            );

            $resultMessages = $requestPendingSingle->getResultMessages();
            $resultData     = $requestPendingSingle->getResultData();

            foreach ($processingActions as $processingAction) {
                /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
                $listingProduct = $listingProductCollection->getItemById($processingAction->getListingProductId());
                $processingAction->setListingProduct($listingProduct);

                /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_ProcessingList $processingList */
                $processingList = $listCollection->getItemById($processingAction->getData('processing_list_id'));

                $resultActionData = array();

                //worker may return different data structure
                if (isset($resultData[$processingAction->getListingProductId().'-id'])) {
                    $resultActionData = $resultData[$processingAction->getListingProductId().'-id'];
                } elseif (isset($resultData['data'][$processingAction->getListingProductId().'-id'])) {
                    $resultActionData = $resultData['data'][$processingAction->getListingProductId().'-id'];
                }

                if (empty($resultActionData['errors'])) {
                    $resultActionData['errors'] = array();
                }

                if (!empty($resultMessages)) {
                    $resultActionData['errors'] = array_merge($resultActionData['errors'], $resultMessages);
                }

                /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_List_ProcessingRunner $processingRunner */
                $processingRunner = Mage::getModel($processingAction->getProcessing()->getModel());
                $processingRunner->setProcessingObject($processingAction->getProcessing());
                $processingRunner->setListingProduct($listingProduct);

                if (!$processingRunner->processRelistResult($processingList, $resultActionData)) {
                    $this->completeListProcessingActionFail($processingAction);
                } else {
                    $this->completeListProcessingActionSuccess($processingAction);
                }
            }

            $requestPendingSingle->deleteInstance();
        }
    }

    //########################################

    /**
     * @param $processingActionsIds
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function failedOnRelistAttemptCallback($processingActionsIds)
    {
        /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Action_Processing_Collection $actionCollection */
        $actionCollection = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Processing')->getCollection();
        $actionCollection->addFieldToFilter('id', array('in' => $processingActionsIds));

        foreach ($actionCollection->getItems() as $processingAction) {
            /** @var ActionProcessing $processingAction */
            $this->completeListProcessingActionFail($processingAction);
        }
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processing $processingAction
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function completeListProcessingActionSuccess(ActionProcessing $processingAction)
    {
        $processing       = $processingAction->getProcessing();
        $listingProduct   = $processingAction->getListingProduct();
        $processingParams = $processing->getParams();

        $linking = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Type_List_Linking');
        $linking->setListingProduct($listingProduct);
        $linking->setSku($processingParams['request_data']['sku']);
        $linking->createWalmartItem();

        $logger = $this->createLogger(
            $processingParams['responser_params']['params']['status_changer'],
            $processingParams['responser_params']['logs_action_id']
        );

        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
        $message->initFromPreparedData(
            $this->getSuccessMessage($listingProduct),
            Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_SUCCESS
        );

        $logger->logListingProductMessage($listingProduct, $message);

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();
        if ($walmartListingProduct->getVariationManager()->isRelationChildType()) {
            $parentListingProduct = $walmartListingProduct->getVariationManager()
                ->getTypeModel()
                ->getParentListingProduct();

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartParentListingProduct */
            $walmartParentListingProduct = $parentListingProduct->getChildObject();
            $walmartParentListingProduct->getVariationManager()->getTypeModel()->getProcessor()->process();
        }

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_List_ProcessingRunner $processingRunner */
        $processingRunner = Mage::getModel($processing->getModel());
        $processingRunner->setProcessingObject($processing);
        $processingRunner->setListingProduct($listingProduct);

        $processingRunner->complete();
        $processingAction->deleteInstance();
    }

    /**
     * @param Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processing $processingAction
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function completeListProcessingActionFail(ActionProcessing $processingAction)
    {
        $processing       = $processingAction->getProcessing();
        $listingProduct   = $processingAction->getListingProduct();
        $processingParams = $processing->getParams();

        $listingProduct->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE);
        $listingProduct->save();

        $logger = $this->createLogger(
            $processingParams['responser_params']['params']['status_changer'],
            $processingParams['responser_params']['logs_action_id']
        );

        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
        $message->initFromPreparedData(
            'The Item was listed. However, some product data, i.e. product quantity, cannot yet
            be submitted. It is caused by the technical limitations imposed by Walmart when adding a new offer
            on their website. M2E Pro will try to submit this product data later.',
            Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
        );

        $logger->logListingProductMessage($listingProduct, $message);

        $this->completeListProcessingActionSuccess($processingAction);
    }

    //########################################

    protected function getSuccessMessage(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();
        
        if ($walmartListingProduct->getVariationManager()->isRelationParentType()) {
            return 'Parent Product was Listed';
        }

        $currency = Mage::app()->getLocale()->currency(
            $listingProduct->getMarketplace()->getChildObject()->getCurrency()
        );

        return sprintf(
            'Product was Listed with QTY %d, Price %s',
            $walmartListingProduct->getOnlineQty(),
            $currency->toCurrency($walmartListingProduct->getOnlinePrice())
        );
    }

    //########################################

    /**
     * @param array $feedsPacks
     * @param Zend_Db_Statement $scheduledActionsDataStatement
     * @throws Zend_Db_Statement_Exception
     */
    protected function fillFeedsPacks(
        array &$feedsPacks,
        Zend_Db_Statement $scheduledActionsDataStatement
    ) {
        $throttlingManager = Mage::getModel('M2ePro/Walmart_ThrottlingManager');

        $canCreateNewPacks = true;

        while ($scheduledActionData = $scheduledActionsDataStatement->fetch()) {
            $availableRequestsCount = $throttlingManager->getAvailableRequestsCount(
                $scheduledActionData['account_id'], ActionProcessor::FEED_TYPE_UPDATE_DETAILS
            );

            if ($availableRequestsCount <= 0) {
                continue;
            }

            if ($this->canAddToLastExistedPack($feedsPacks, $scheduledActionData['account_id'])) {
                $this->addToLastExistedPack($feedsPacks, $scheduledActionData);
                continue;
            }

            if (!$canCreateNewPacks) {
                continue;
            }

            $this->addToNewPack($feedsPacks, $scheduledActionData);
        }
    }

    protected function processExistedSkus(array $accountsActions)
    {
        $removedListingsProductsIds = array();

        foreach ($accountsActions as $accountId => &$accountPacks) {
            foreach ($accountPacks as &$accountData) {
                /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
                $listingProductCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
                $listingProductCollection->addFieldToFilter('id', array_keys($accountData));

                $listingsProductsSkus = array();

                foreach ($accountData as $listingProductId => $listingProductData) {
                    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
                    $listingProduct = $listingProductCollection->getItemById($listingProductId);
                    /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
                    $walmartListingProduct = $listingProduct->getChildObject();

                    if ($walmartListingProduct->getSku()) {
                        $listingsProductsSkus[$listingProductId] = $walmartListingProduct->getSku();
                        continue;
                    }

                    $skuResolver = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Type_List_SkuResolver');
                    $skuResolver->setListingProduct($listingProduct);
                    $skuResolver->setSkusInCurrentRequest($listingsProductsSkus);

                    $sku = $skuResolver->resolve();

                    $messages = $skuResolver->getMessages();
                    if (!empty($messages)) {
                        $additionalData = Mage::helper('M2ePro')->jsonDecode($listingProductData['additional_data']);
                        $logger = $this->createLogger($additionalData['params']['status_changer']);

                        foreach ($skuResolver->getMessages() as $message) {
                            $logger->logListingProductMessage($listingProduct, $message);
                        }
                    }

                    if ($sku === null) {
                        unset($accountData[$listingProductId]);
                        $removedListingsProductsIds[] = $listingProductId;

                        continue;
                    }

                    $listingsProductsSkus[$listingProductId] = $sku;
                }

                $productsData = $this->receiveProductsData($accountId, $listingsProductsSkus);

                foreach ($accountData as $listingProductId => &$listingProductData) {
                    $sku = $listingsProductsSkus[$listingProductId];

                    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
                    $listingProduct = $listingProductCollection->getItemById($listingProductId);
                    /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
                    $walmartListingProduct = $listingProduct->getChildObject();

                    if (isset($productsData[$sku])) {
                        $productData = $productsData[$sku];

                        $linking = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Type_List_Linking');
                        $linking->setListingProduct($listingProduct);
                        $linking->setSku($sku);
                        $linking->setProductIdentifiers(
                            array(
                            'wpid'    => $productData['wpid'],
                            'item_id' => $productData['item_id'],
                            'gtin'    => $productData['gtin'],
                            'upc'     => isset($productData['upc']) ? $productData['upc'] : null,
                            'ean'     => isset($productData['ean']) ? $productData['ean'] : null,
                            'isbn'    => isset($productData['isbn']) ? $productData['isbn'] : null,
                            )
                        );

                        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');

                        if ($linking->link()) {
                            $message->initFromPreparedData(
                                sprintf('Product has been found by SKU %s in your Inventory and linked.', $sku),
                                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_SUCCESS
                            );
                        } else {
                            $message->initFromPreparedData(
                                sprintf('Unexpected error during process of linking by SKU %s. The required SKU
                                         has been found but the data is not sent back. Please try again.', $sku),
                                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
                            );
                        }

                        $additionalData = Mage::helper('M2ePro')->jsonDecode($listingProductData['additional_data']);
                        $logger = $this->createLogger($additionalData['params']['status_changer']);
                        $logger->logListingProductMessage($listingProduct, $message);

                        $removedListingsProductsIds[] = $listingProductId;
                        unset($accountData[$listingProductId]);
                    } else if ($walmartListingProduct->getSku()) {
                        $listingProduct->addData(
                            array(
                                'sku'     => null,
                                'wpid'    => null,
                                'item_id' => null,
                                'gtin'    => null,
                                'upc'     => null,
                                'ean'     => null,
                                'isbn'    => null,
                            )
                        );
                        $listingProduct->save();
                    }

                    $listingProductData['sku'] = $sku;
                }
                unset($listingProductData);
            }
        }

        if (!empty($removedListingsProductsIds)) {
            $scheduledActionManager = Mage::getModel('M2ePro/Listing_Product_ScheduledAction_Manager');

            $scheduledActionsCollection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
            $scheduledActionsCollection->addFieldToFilter('listing_product_id', $removedListingsProductsIds);

            /** @var Ess_M2ePro_Model_Listing_Product_ScheduledAction[] $scheduledActions */
            $scheduledActions = $scheduledActionsCollection->getItems();

            foreach ($scheduledActions as $scheduledAction) {
                $scheduledActionManager->deleteAction($scheduledAction);
            }
        }

        return $accountsActions;
    }

    /**
     * @param array $listingsProductsData
     * @return string
     */
    protected function initProcessingActions(array $listingsProductsData)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingsProductsCollection */
        $listingsProductsCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $listingsProductsCollection->addFieldToFilter('id', array_keys($listingsProductsData));

        $groupHash = Mage::helper('M2ePro')->generateUniqueHash();

        foreach ($listingsProductsData as $listingProductId => $listingProductData) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = $listingsProductsCollection->getItemById($listingProductId);

            $processingRunner = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_List_ProcessingRunner');
            $processingRunner->setListingProduct($listingProduct);

            $configurator = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Configurator');
            if (isset($listingProductData['configurator'])) {
                $configurator = clone $listingProductData['configurator'];
            }

            $params = array(
                'sku' => $listingProductData['sku'],
            );
            if (!empty($listingProductData['additional_data'])) {
                $additionalData = Mage::helper('M2ePro')->jsonDecode($listingProductData['additional_data']);
                !empty($additionalData['params']) && $params = array_merge($params, $additionalData['params']);
            }

            $processingRunner->setParams(
                array(
                'listing_product_id' => $listingProductId,
                'account_id'         => $listingProduct->getAccount()->getId(),
                'configurator'       => $configurator->getData(),
                'action_type'        => Ess_M2ePro_Model_Listing_Product::ACTION_LIST,
                'lock_identifier'    => 'list',
                'requester_params'   => $params,
                'group_hash'         => $groupHash,
                )
            );

            $processingRunner->start();
        }

        return $groupHash;
    }

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function prepareProcessingActions()
    {
        $processingActionPreparationLimit = (int)$this->getConfigValue(
            '/walmart/listing/product/action/processing/prepare/', 'max_listings_products_count'
        );

        /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Action_Processing_Collection $processingActionColl */
        $processingActionColl = Mage::getResourceModel(
            'M2ePro/Walmart_Listing_Product_Action_Processing_Collection'
        );
        $processingActionColl->addFieldToFilter('is_prepared', 0);
        $processingActionColl->addFieldToFilter('type', ActionProcessing::TYPE_ADD);
        $processingActionColl->getSelect()->limit($processingActionPreparationLimit);
        $processingActionColl->getSelect()->order('id ASC');

        /** @var ActionProcessing[] $processingActions */
        $processingActions   = $processingActionColl->getItems();
        $listingsProductsIds = $processingActionColl->getColumnValues('listing_product_id');

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingsProductsCollection */
        $listingsProductsCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $listingsProductsCollection->addFieldToFilter('id', array('in' => $listingsProductsIds));

        $processingIds = $processingActionColl->getColumnValues('processing_id');

        $processingCollection = Mage::getResourceModel('M2ePro/Processing_Collection');
        $processingCollection->addFieldToFilter('id', array('in' => $processingIds));

        $dispatcher = Mage::getModel('M2ePro/Walmart_Connector_Product_Dispatcher');

        foreach ($processingActions as $processingAction) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = $listingsProductsCollection->getItemById($processingAction->getListingProductId());

            if ($listingProduct === null) {
                $processingAction->deleteInstance();
                continue;
            }

            /** @var Ess_M2ePro_Model_Processing $processing */
            $processing = $processingCollection->getItemById($processingAction->getProcessingId());
            $processingAction->setProcessing($processing);

            $listingProduct->setProcessingAction($processingAction);

            $processingParams = $processing->getParams();

            $configurator = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Configurator');
            $configurator->setData($processingParams['configurator']);

            $listingProduct->setActionConfigurator($configurator);

            $params = array();
            if (isset($processingParams['requester_params'])) {
                $params = $processingParams['requester_params'];
            }

            $dispatcher->process($processingParams['action_type'], $listingProduct, $params);
        }
    }

    /**
     * @param array $listingsProductsData
     */
    protected function prepareScheduledActions(array $listingsProductsData)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingsProductsCollection */
        $listingsProductsCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $listingsProductsCollection->addFieldToFilter('id', array_keys($listingsProductsData));

        $scheduledActionManager = Mage::getModel('M2ePro/Listing_Product_ScheduledAction_Manager');

        $scheduledActionsCollection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $scheduledActionsCollection->addFieldToFilter('listing_product_id', array_keys($listingsProductsData));

        /** @var Ess_M2ePro_Model_Listing_Product_ScheduledAction[] $scheduledActions */
        $scheduledActions = $scheduledActionsCollection->getItems();

        foreach ($scheduledActions as $scheduledAction) {
            $scheduledActionManager->deleteAction($scheduledAction);
        }
    }

    /**
     * @param array $processingActions
     * @param array $serverCommand
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function processGroupedProcessingActions(array $processingActions, array $serverCommand)
    {
        if (empty($processingActions)) {
            return;
        }

        $account = reset($processingActions)->getListingProduct()->getListing()->getAccount();

        $itemsRequestData = array();

        foreach ($processingActions as $processingAction) {
            $itemsRequestData[$processingAction->getListingProductId()] = $processingAction->getRequestData();
        }

        /** @var Ess_M2ePro_Model_Walmart_Account $walmartAccount */
        $walmartAccount = $account->getChildObject();

        $requestData = array(
            'items'   => $itemsRequestData,
            'account' => $walmartAccount->getServerHash(),
        );

        /** @var Ess_M2ePro_Model_Walmart_Connector_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');

        $connector = $dispatcher->getVirtualConnector(
            $serverCommand[0], $serverCommand[1], $serverCommand[2],
            $requestData, null, null
        );

        try {
            $dispatcher->process($connector);
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            if ($exception instanceof Ess_M2ePro_Model_Exception_Connection) {
                $isRepeat = $exception->handleRepeatTimeout(self::FIRST_CONNECTION_ERROR_DATE_REGISTRY_KEY);
                if ($isRepeat) {
                    return;
                }
            }

            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromException($exception);

            foreach ($processingActions as $processingAction) {
                $this->completeProcessingAction($processingAction, array('errors' => array($message->asArray())));
            }

            return;
        }

        $responseData = $connector->getResponseData();
        $responseMessages = $connector->getResponseMessages();

        if (empty($responseData['processing_id'])) {
            foreach ($processingActions as $processingAction) {
                $messages = $responseMessages;

                if (!empty($responseData['data'][$processingAction->getListingProductId().'-id']['errors'])) {
                    $messages = array_merge(
                        $messages, $responseData['data'][$processingAction->getListingProductId().'-id']['errors']
                    );
                }

                $this->completeProcessingAction($processingAction, array('errors' => $messages));
            }

            return;
        }

        $requestPendingSingle = Mage::getModel('M2ePro/Request_Pending_Single');
        $requestPendingSingle->setData(
            array(
                'component'       => Ess_M2ePro_Helper_Component_Walmart::NICK,
                'server_hash'     => $responseData['processing_id'],
                'expiration_date' => gmdate(
                    'Y-m-d H:i:s',
                    Mage::helper('M2ePro')->getCurrentGmtDate(true)
                        + self::PENDING_REQUEST_MAX_LIFE_TIME
                )
            )
        );
        $requestPendingSingle->save();

        $actionsIds = array();
        foreach ($processingActions as $processingAction) {
            $actionsIds[] = $processingAction->getId();
        }

        Mage::getResourceModel('M2ePro/Walmart_Listing_Product_Action_Processing')->markAsInProgress(
            $actionsIds, $requestPendingSingle
        );
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Account $account
     * @return Zend_Db_Statement
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getScheduledActionsDataStatement(Ess_M2ePro_Model_Account $account)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK)
            ->getScheduledActionsPreparedCollection(
                self::LIST_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_LIST
            )
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("''"))
            ->addFieldToFilter('l.account_id', $account->getId());

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/walmart/listing/product/action/list/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        $select = $collection->getSelect();

        $select->order(array('coefficient DESC'));
        $select->order(array('create_date ASC'));

        $limit = (int)$this->getConfigValue('/walmart/listing/product/action/scheduled_data/', 'limit');
        $select->limit($limit);

        return $select->query();
    }

    //########################################

    /**
     * @param array $feedsPacks
     * @param int $accountId
     * @return bool
     */
    protected function canAddToLastExistedPack(array $feedsPacks, $accountId)
    {
        if (empty($feedsPacks[$accountId])) {
            return false;
        }

        $lastPackIndex = count($feedsPacks[$accountId]) - 1;
        $maxPackSize = $this->getMaxPackSize(ActionProcessor::FEED_TYPE_UPDATE_DETAILS);

        return count($feedsPacks[$accountId][$lastPackIndex]) < $maxPackSize;
    }

    /**
     * @param array $feedsPacks
     * @param array $scheduled
     */
    protected function addToLastExistedPack(array &$feedsPacks, $scheduled)
    {
        if (empty($feedsPacks[$scheduled['account_id']])) {
            $lastPackIndex = 0;
        } else {
            $lastPackIndex = count($feedsPacks[$scheduled['account_id']]) - 1;
        }

        $feedsPacks[$scheduled['account_id']][$lastPackIndex][$scheduled['listing_product_id']] = $scheduled;
    }

    // ---------------------------------------

    /**
     * @param array $feedsPacks
     * @param array $scheduled
     */
    protected function addToNewPack(array &$feedsPacks, $scheduled)
    {
        if (empty($feedsPacks[$scheduled['account_id']])) {
            $newPackIndex = 0;
        } else {
            $newPackIndex = count($feedsPacks[$scheduled['account_id']]);
        }

        $feedsPacks[$scheduled['account_id']][$newPackIndex][$scheduled['listing_product_id']] = $scheduled;
    }

    //########################################

    /**
     * @param string $feedType
     * @return int
     */
    protected function getMaxPackSize($feedType = null)
    {
        if ($feedType == ActionProcessor::FEED_TYPE_UPDATE_DETAILS) {
            return 100;
        }

        return 1000;
    }

    //########################################

    /**
     * @param int $accountId
     * @param array $skus
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function receiveProductsData($accountId, array $skus)
    {
        if (empty($skus)) {
            return array();
        }

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Walmart')->getCachedObject('Account', $accountId);

        /** @var Ess_M2ePro_Model_Walmart_Account $walmartAccount */
        $walmartAccount = $account->getChildObject();

        $onlyItems = array();
        foreach ($skus as $sku) {
            $onlyItems[] = array(
                'type'  => 'sku',
                'value' => $sku,
            );
        }

        $requestData = array(
            'account'    => $walmartAccount->getServerHash(),
            'return_now' => true,
            'only_items' => $onlyItems,
        );

        /** @var Ess_M2ePro_Model_Walmart_Connector_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');

        $connector = $dispatcher->getVirtualConnector(
            'inventory', 'get', 'items',
            $requestData, null, null
        );

        try {
            $connector->process();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            return array();
        }

        $responseData = $connector->getResponseData();

        if (empty($responseData['data'])) {
            return array();
        }

        $productsData = array();

        foreach ($responseData['data'] as $productData) {
            $productsData[$productData['sku']] = $productData;
        }

        return $productsData;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processing $action
     * @param array $data
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function completeProcessingAction(ActionProcessing $action, array $data)
    {
        $processing = $action->getProcessing();

        $processing->setSettings('result_data', $data);
        $processing->setData('is_completed', 1);

        $processing->save();

        $action->deleteInstance();
    }

    // ---------------------------------------

    /**
     * @param $group
     * @param $key
     * @return mixed
     */
    protected function getConfigValue($group, $key)
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue($group, $key);
    }

    /**
     * @param $statusChanger
     * @param $logsActionId
     * @return Ess_M2ePro_Model_Walmart_Listing_Product_Action_Logger
     */
    protected function createLogger($statusChanger, $logsActionId = null)
    {
        $logger = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Logger');

        if ($logsActionId === null) {
            $logsActionId = Mage::getResourceModel('M2ePro/Listing_Log')->getNextActionId();
        }

        $logger->setActionId($logsActionId);
        $logger->setAction(Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT);

        switch ($statusChanger) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN:
                $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;
                break;
            case Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER:
                $initiator = Ess_M2ePro_Helper_Data::INITIATOR_USER;
                break;
            default:
                $initiator = Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION;
                break;
        }

        $logger->setInitiator($initiator);

        return $logger;
    }

    //########################################
}
