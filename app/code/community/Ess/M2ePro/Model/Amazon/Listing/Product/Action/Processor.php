<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Processor
{
    const FEED_TYPE_ADD            = 'list';
    const FEED_TYPE_DELETE         = 'delete';
    const FEED_TYPE_UPDATE_QTY     = 'update_qty';
    const FEED_TYPE_UPDATE_PRICE   = 'update_price';
    const FEED_TYPE_UPDATE_DETAILS = 'update_details';
    const FEED_TYPE_UPDATE_IMAGES  = 'update_images';

    const PENDING_REQUEST_MAX_LIFE_TIME = 86400;

    const CONNECTION_ERROR_REPEAT_TIMEOUT = 180;
    const FIRST_CONNECTION_ERROR_DATE_REGISTRY_KEY = '/amazon/listing/product/action/first_connection_error/date/';

    //########################################

    public function process()
    {
        $this->removeMissedProcessingActions();

        /** @var Ess_M2ePro_Model_Mysql4_Account_Collection $accountCollection */
        $accountCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');

        $merchantIds = array_unique($accountCollection->getColumnValues('merchant_id'));
        if (empty($merchantIds)) {
            return;
        }

        $throttlingManager = Mage::getSingleton('M2ePro/Amazon_ThrottlingManager');

        foreach ($merchantIds as $merchantId) {
            $availableRequestsCount = $throttlingManager->getAvailableRequestsCount(
                $merchantId, Ess_M2ePro_Model_Amazon_ThrottlingManager::REQUEST_TYPE_FEED
            );
            if ($availableRequestsCount <= 0) {
                continue;
            }

            $feedsPacks = array(
                self::FEED_TYPE_ADD            => array(),
                self::FEED_TYPE_DELETE         => array(),
                self::FEED_TYPE_UPDATE_QTY     => array(),
                self::FEED_TYPE_UPDATE_PRICE   => array(),
                self::FEED_TYPE_UPDATE_DETAILS => array(),
                self::FEED_TYPE_UPDATE_IMAGES  => array(),
            );

            $this->fillFeedsPacks(
                $feedsPacks,
                $this->getScheduledActionsDataStatement($merchantId, true),
                $availableRequestsCount
            );

            $actionsDataForProcessing = $this->prepareAccountsActions($feedsPacks);

            $requestsPacks = $this->prepareRequestsPacks($actionsDataForProcessing);

            foreach ($requestsPacks as $accountId => $accountPacks) {
                foreach ($accountPacks as $actionType => $groupPacks) {
                    foreach ($groupPacks as $groupHash => $packData) {
                        if (empty($packData)) {
                            continue;
                        }

                        $this->initProcessingActions($actionType, $packData, $groupHash);
                        $this->prepareScheduledActions($packData);
                    }
                }
            }
        }

        $this->prepareProcessingActions();

        $fullyPreparedGroupHashes = Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Action_Processing')
            ->getFullyPreparedGroupHashes();

        foreach ($fullyPreparedGroupHashes as $groupHash) {
            $processingActionCollection = Mage::getResourceModel(
                'M2ePro/Amazon_Listing_Product_Action_Processing_Collection'
            );
            $processingActionCollection->addFieldToFilter('group_hash', $groupHash);
            $processingActionCollection->addFieldToFilter('is_prepared', 1);
            $processingActionCollection->addFieldToFilter('request_pending_single_id', array('null' => true));

            $processingActionsByType = array();

            foreach ($processingActionCollection->getItems() as $processingAction) {
                if (!isset($processingActionsByType[$processingAction->getType()])) {
                    $processingActionsByType[$processingAction->getType()] = array();
                }

                $processingActionsByType[$processingAction->getType()][] = $processingAction;
            }

            foreach ($processingActionsByType as $actionType => $processingActions) {
                $this->processGroupedProcessingActions($processingActions, $actionType);
            }
        }
    }

    //########################################

    private function removeMissedProcessingActions()
    {
        $actionCollection = Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Action_Processing_Collection');
        $actionCollection->getSelect()->joinLeft(
            array('p' => Mage::getResourceModel('M2ePro/Processing')->getMainTable()),
            'p.id = main_table.processing_id',
            array()
        );
        $actionCollection->addFieldToFilter('p.id', array('null' => true));

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Processing[] $actions */
        $actions = $actionCollection->getItems();

        foreach ($actions as $action) {
            $action->deleteInstance();
        }
    }

    // ---------------------------------------

    /**
     * @param $merchantId
     * @param array $feedsPacks
     * @param Zend_Db_Statement $scheduledActionsDataStatement
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    private function fillFeedsPacks(array &$feedsPacks,
                                    Zend_Db_Statement $scheduledActionsDataStatement,
                                    $availableRequestsCount = NULL)
    {
        $canCreateNewPacks = true;

        while ($scheduledActionData = $scheduledActionsDataStatement->fetch()) {
            $feedTypes = $this->getFeedTypes($scheduledActionData['action_type'], $scheduledActionData['filtered_tag']);

            $canBeAdded = false;

            foreach ($feedTypes as $feedType) {
                if ($this->canAddToLastExistedPack($feedsPacks, $feedType, $scheduledActionData['account_id'])) {
                    $canBeAdded = true;
                    continue;
                }

                if (!$canCreateNewPacks || (!is_null($availableRequestsCount) && $availableRequestsCount <= 0)) {
                    $canBeAdded = false;
                    break;
                }

                $canBeAdded = true;
            }

            if (!$canBeAdded) {
                $canCreateNewPacks = false;
                continue;
            }

            foreach ($feedTypes as $feedType) {
                if ($this->canAddToLastExistedPack($feedsPacks, $feedType, $scheduledActionData['account_id'])) {
                    $this->addToLastExistedPack($feedsPacks, $feedType, $scheduledActionData);
                    continue;
                }

                if (!$canCreateNewPacks) {
                    continue;
                }

                $this->addToNewPack($feedsPacks, $feedType, $scheduledActionData);
                $availableRequestsCount--;
            }
        }
    }

    /**
     * @param array $feedsPacks
     * @return array
     */
    private function prepareAccountsActions(array $feedsPacks)
    {
        $result = array();

        foreach ($feedsPacks as $feedType => $feedPacks) {
            foreach ($feedPacks as $accountId => $accountPacks) {
                foreach ($accountPacks as $accountPack) {
                    foreach ($accountPack as $listingProductData) {
                        $listingProductId = $listingProductData['listing_product_id'];
                        $actionType       = $listingProductData['action_type'];

                        if (empty($result[$accountId][$actionType])) {
                            $result[$accountId][$actionType] = array();
                        }

                        if ($actionType != Ess_M2ePro_Model_Listing_Product::ACTION_REVISE &&
                            $actionType != Ess_M2ePro_Model_Listing_Product::ACTION_RELIST
                        ) {
                            $result[$accountId][$actionType][$listingProductId] = $listingProductData;
                            continue;
                        }

                        $listingProductConfigurator = Mage::getModel(
                            'M2ePro/Amazon_Listing_Product_Action_Configurator'
                        );

                        $additionalData = Mage::helper('M2ePro')->jsonDecode($listingProductData['additional_data']);
                        if (!empty($additionalData['configurator'])) {
                            $listingProductConfigurator->setData($additionalData['configurator']);
                        }

                        if ($actionType == Ess_M2ePro_Model_Listing_Product::ACTION_RELIST) {
                            if (!empty($result[$accountId][$actionType][$listingProductId]['configurator'])) {
                                continue;
                            }

                            $listingProductData['configurator'] = $listingProductConfigurator;

                            $result[$accountId][$actionType][$listingProductId] = $listingProductData;

                            continue;
                        }

                        if (!empty($result[$accountId][$actionType][$listingProductId]['configurator'])) {
                            $configurator = $result[$accountId][$actionType][$listingProductId]['configurator'];
                        } else {
                            $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');
                            $configurator->disableAll();
                        }

                        switch ($listingProductData['filtered_tag']) {
                            case 'qty':
                                if ($listingProductConfigurator->isQtyAllowed()) {
                                    $configurator->allowQty();
                                }

                                break;

                            case 'price_regular':
                            case 'price_business':
                                if ($listingProductConfigurator->isRegularPriceAllowed()) {
                                    $configurator->allowRegularPrice();
                                }

                                if ($listingProductConfigurator->isBusinessPriceAllowed()) {
                                    $configurator->allowBusinessPrice();
                                }

                                break;

                            case 'details':
                                if ($listingProductConfigurator->isDetailsAllowed()) {
                                    $configurator->allowDetails();
                                }

                                break;

                            case 'images':
                                if ($listingProductConfigurator->isImagesAllowed()) {
                                    $configurator->allowImages();
                                }

                                break;
                        }

                        $listingProductData['configurator'] = $configurator;

                        $result[$accountId][$actionType][$listingProductId] = $listingProductData;
                    }

                }
            }
        }

        return $result;
    }

    private function prepareRequestsPacks(array $accountsActions)
    {
        $groupHashesMetadata = array();
        $requestsPacks = array();

        foreach ($accountsActions as $accountId => $accountData) {
            foreach ($accountData as $actionType => $actionData) {
                foreach ($actionData as $listingProductId => $listingProductData) {
                    $groupHash = $this->getActualGroupHash($accountId, $groupHashesMetadata, $listingProductData);
                    if (!isset($groupHashesMetadata[$accountId][$groupHash])) {
                        $groupHashesMetadata[$accountId][$groupHash] = array(
                            'slow_actions_count' => 0
                        );
                    }

                    if ($listingProductData['action_type'] == Ess_M2ePro_Model_Listing_Product::ACTION_REVISE) {

                        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator $configurator */
                        $configurator = $listingProductData['configurator'];
                        if ($configurator->isDetailsAllowed()) {
                            $groupHashesMetadata[$accountId][$groupHash]['slow_actions_count']++;
                        }
                    }

                    if ($listingProductData['action_type'] == Ess_M2ePro_Model_Listing_Product::ACTION_LIST) {
                        $groupHashesMetadata[$accountId][$groupHash]['slow_actions_count']++;
                    }

                    $requestsPacks[$accountId][$actionType][$groupHash][$listingProductId] = $listingProductData;
                }
            }
        }

        return $requestsPacks;
    }

    private function getActualGroupHash($accountId, array $groupHashesMetadata, array $listingProductData)
    {
        if (empty($groupHashesMetadata[$accountId])) {
            return Mage::helper('M2ePro')->generateUniqueHash();
        }

        end($groupHashesMetadata[$accountId]);
        $lastGroupHash = key($groupHashesMetadata[$accountId]);

        if ($listingProductData['action_type'] == Ess_M2ePro_Model_Listing_Product::ACTION_LIST) {
            $metadata = $groupHashesMetadata[$accountId][$lastGroupHash];
            if ($metadata['slow_actions_count'] < $this->getMaxPackSize(self::FEED_TYPE_ADD)) {
                return $lastGroupHash;
            }

            return Mage::helper('M2ePro')->generateUniqueHash();
        }

        if ($listingProductData['action_type'] != Ess_M2ePro_Model_Listing_Product::ACTION_REVISE) {
            return $lastGroupHash;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator $configurator */
        $configurator = $listingProductData['configurator'];
        if (!$configurator->isDetailsAllowed()) {
            return $lastGroupHash;
        }

        foreach ($groupHashesMetadata[$accountId] as $groupHash => $metadata) {
            if ($metadata['slow_actions_count'] < $this->getMaxPackSize(self::FEED_TYPE_UPDATE_DETAILS)) {
                return $groupHash;
            }
        }

        return Mage::helper('M2ePro')->generateUniqueHash();
    }

    /**
     * @param $actionType
     * @param array $listingsProductsData
     * @param $groupHash
     * @return string
     */
    private function initProcessingActions($actionType, array $listingsProductsData, $groupHash)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingsProductsCollection */
        $listingsProductsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingsProductsCollection->addFieldToFilter('id', array_keys($listingsProductsData));

        foreach ($listingsProductsData as $listingProductId => $listingProductData) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = $listingsProductsCollection->getItemById($listingProductId);

            $processingRunner = Mage::getModel('M2ePro/Amazon_Connector_Product_ProcessingRunner');
            if ($actionType == Ess_M2ePro_Model_Listing_Product::ACTION_LIST) {
                $processingRunner = Mage::getModel('M2ePro/Amazon_Connector_Product_List_ProcessingRunner');
            }

            $processingRunner->setListingProduct($listingProduct);

            $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');
            if (isset($listingProductData['configurator'])) {
                $configurator = clone $listingProductData['configurator'];
            }

            $params = array();
            if (!empty($listingProductData['additional_data'])) {
                $additionalData = Mage::helper('M2ePro')->jsonDecode($listingProductData['additional_data']);
                !empty($additionalData['params']) && $params = $additionalData['params'];
            }

            $processingRunner->setParams(array(
                'account_id'         => $listingProduct->getAccount()->getId(),
                'listing_product_id' => $listingProductId,
                'configurator'       => $configurator->getData(),
                'action_type'        => $actionType,
                'lock_identifier'    => $this->getLockIdentifier($actionType, $params),
                'requester_params'   => $params,
                'group_hash'         => $groupHash,
            ));

            $processingRunner->start();
        }

        return $groupHash;
    }

    private function prepareProcessingActions()
    {
        $processingActionPreparationLimit = (int)$this->getConfigValue(
            '/amazon/listing/product/action/processing/prepare/', 'max_listings_products_count'
        );

        /** @var Ess_M2ePro_Model_Mysql4_Amazon_Listing_Product_Action_Processing_Collection $processingActionColl */
        $processingActionColl = Mage::getResourceModel(
            'M2ePro/Amazon_Listing_Product_Action_Processing_Collection'
        );
        $processingActionColl->addFieldToFilter('is_prepared', 0);
        $processingActionColl->getSelect()->limit($processingActionPreparationLimit);
        $processingActionColl->getSelect()->order('id ASC');

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Processing[] $processingActions */
        $processingActions = $processingActionColl->getItems();

        $listingsProductsIds = $processingActionColl->getColumnValues('listing_product_id');

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingsProductsCollection */
        $listingsProductsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingsProductsCollection->addFieldToFilter('id', array('in' => $listingsProductsIds));

        $processingIds = $processingActionColl->getColumnValues('processing_id');

        $processingCollection = Mage::getResourceModel('M2ePro/Processing_Collection');
        $processingCollection->addFieldToFilter('id', array('in' => $processingIds));

        $dispatcher = Mage::getModel('M2ePro/Amazon_Connector_Product_Dispatcher');

        foreach ($processingActions as $processingAction) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = $listingsProductsCollection->getItemById($processingAction->getListingProductId());

            if (is_null($listingProduct)) {
                $processingAction->getProcessing()->deleteInstance();
                $processingAction->deleteInstance();
                continue;
            }

            /** @var Ess_M2ePro_Model_Processing $processing */
            $processing = $processingCollection->getItemById($processingAction->getProcessingId());
            $processingAction->setProcessing($processing);

            $listingProduct->setProcessingAction($processingAction);

            $processingParams = $processing->getParams();

            $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');
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
     * @param $actionType
     * @param array $listingsProductsData
     */
    private function prepareScheduledActions(array $listingsProductsData)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingsProductsCollection */
        $listingsProductsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingsProductsCollection->addFieldToFilter('id', array_keys($listingsProductsData));

        $scheduledActionManager = Mage::getModel('M2ePro/Listing_Product_ScheduledAction_Manager');

        $scheduledActionsCollection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $scheduledActionsCollection->addFieldToFilter('listing_product_id', array_keys($listingsProductsData));

        /** @var Ess_M2ePro_Model_Listing_Product_ScheduledAction[] $scheduledActions */
        $scheduledActions = $scheduledActionsCollection->getItems();

        foreach ($scheduledActions as $scheduledAction) {
            $listingProductData = $listingsProductsData[$scheduledAction->getListingProductId()];

            if (!$scheduledAction->isActionTypeRevise() || empty($listingProductData['configurator'])) {
                $scheduledActionManager->deleteAction($scheduledAction);
                continue;
            }

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator $newConfigurator */
            $newConfigurator = $listingProductData['configurator'];

            $existedConfigurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');

            $tags = array_filter(explode('/', $scheduledAction->getTag()));
            $tags = array_flip($tags);

            $additionalData = $scheduledAction->getAdditionalData();
            if (!empty($additionalData['configurator'])) {
                $existedConfigurator->setData($additionalData['configurator']);
            }

            foreach ($newConfigurator->getAllowedDataTypes() as $allowedDataType) {
                switch ($allowedDataType) {
                    case 'qty':
                        $existedConfigurator->disallowQty();
                        unset($tags['qty']);
                        break;

                    case 'regular_price':
                    case 'business_price':
                        $existedConfigurator->disallowRegularPrice();
                        $existedConfigurator->disallowBusinessPrice();

                        unset($tags['price_regular']);
                        unset($tags['price_business']);

                        break;

                    case 'details':
                        $existedConfigurator->disallowDetails();
                        unset($tags['details']);
                        break;

                    case 'images':
                        $existedConfigurator->disallowImages();
                        unset($tags['images']);
                        break;
                }
            }

            $tags = array_keys($tags);

            $additionalData['configurator'] = $existedConfigurator->getData();
            $scheduledAction->setSettings('additional_data', $additionalData);

            if (count($existedConfigurator->getAllowedDataTypes()) == 0) {
                $scheduledActionManager->deleteAction($scheduledAction);
            } else {
                $scheduledAction->setData('tag', '/'.trim(implode('/', $tags), '/').'/');
                $scheduledActionManager->updateAction($scheduledAction);
            }
        }
    }

    /**
     * @param Ess_M2ePro_Model_Amazon_Order_Action_Processing[] $processingActions
     * @param $actionType
     * @param Ess_M2ePro_Model_Account $account
     * @param Ess_M2ePro_Model_Amazon_Listing_Product_Action_Processing[] $processingActions
     */
    private function processGroupedProcessingActions(array $processingActions, $actionType)
    {
        if (empty($processingActions)) {
            return;
        }

        $account = reset($processingActions)->getListingProduct()->getListing()->getAccount();

        $itemsRequestData = array();

        foreach ($processingActions as $processingAction) {
            $itemsRequestData[$processingAction->getListingProductId()] = $processingAction->getRequestData();
        }

        /** @var Ess_M2ePro_Model_Amazon_Account $amazonAccount */
        $amazonAccount = $account->getChildObject();

        $requestData = array(
            'items'   => $itemsRequestData,
            'account' => $amazonAccount->getServerHash(),
        );

        /** @var Ess_M2ePro_Model_Amazon_Connector_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');

        $command = $this->getServerCommand($actionType);

        $connector = $dispatcher->getVirtualConnector(
            $command[0], $command[1], $command[2],
            $requestData, null, null
        );

        try {
            $dispatcher->process($connector);
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $currentDate              = Mage::helper('M2ePro')->getCurrentGmtDate();
            $firstConnectionErrorDate = $this->getFirstConnectionErrorDate();

            if (empty($firstConnectionErrorDate)) {
                $this->setFirstConnectionErrorDate($currentDate);
                return;
            }

            if (strtotime($currentDate) - strtotime($firstConnectionErrorDate) < self::CONNECTION_ERROR_REPEAT_TIMEOUT){
                return;
            }

            if (!empty($firstConnectionErrorDate)) {
                $this->removeFirstConnectionErrorDate();
            }

            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromException($exception);

            foreach ($processingActions as $processingAction) {
                $this->completeProcessingAction($processingAction, array('messages' => array($message->asArray())));
            }

            return;
        }

        $responseData = $connector->getResponseData();
        $responseMessages = $connector->getResponseMessages();

        if (empty($responseData['processing_id'])) {
            foreach ($processingActions as $processingAction) {
                $messages = $this->getResponseMessages(
                    $responseData, $responseMessages, $processingAction->getListingProductId()
                );
                $this->completeProcessingAction($processingAction, array('messages' => $messages));
            }

            return;
        }

        $requestPendingSingle = Mage::getModel('M2ePro/Request_Pending_Single');
        $requestPendingSingle->setData(array(
            'component'       => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'server_hash'     => $responseData['processing_id'],
            'expiration_date' => Mage::helper('M2ePro')->getDate(
                Mage::helper('M2ePro')->getCurrentGmtDate(true)+self::PENDING_REQUEST_MAX_LIFE_TIME
            )
        ));
        $requestPendingSingle->save();

        $actionsIds = array();
        foreach ($processingActions as $processingAction) {
            $actionsIds[] = $processingAction->getId();
        }

        Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Action_Processing')->markAsInProgress(
            $actionsIds, $requestPendingSingle
        );
    }

    //########################################

    /**
     * @return Zend_Db_Statement
     */
    private function getScheduledActionsDataStatement($merchantId,
                                                      $withCreateDateFilter = false,
                                                      $excludedListingsProductsIds = array())
    {
        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $connRead = $resource->getConnection('core_read');

        $unionSelect = $connRead->select()->union(array(
            $this->getListScheduledActionsPreparedCollection(
                $merchantId, $withCreateDateFilter, $excludedListingsProductsIds
            )->getSelect(),
            $this->getRelistScheduledActionsPreparedCollection(
                $merchantId, $withCreateDateFilter, $excludedListingsProductsIds
            )->getSelect(),
            $this->getReviseQtyScheduledActionsPreparedCollection(
                $merchantId, $withCreateDateFilter, $excludedListingsProductsIds
            )->getSelect(),
            $this->getRevisePriceRegularScheduledActionsPreparedCollection(
                $merchantId, $withCreateDateFilter, $excludedListingsProductsIds
            )->getSelect(),
            $this->getRevisePriceBusinessScheduledActionsPreparedCollection(
                $merchantId, $withCreateDateFilter, $excludedListingsProductsIds
            )->getSelect(),
            $this->getReviseDetailsScheduledActionsPreparedCollection(
                $merchantId, $withCreateDateFilter, $excludedListingsProductsIds
            )->getSelect(),
            $this->getReviseImagesScheduledActionsPreparedCollection(
                $merchantId, $withCreateDateFilter, $excludedListingsProductsIds
            )->getSelect(),
            $this->getStopScheduledActionsPreparedCollection(
                $merchantId, $withCreateDateFilter, $excludedListingsProductsIds
            )->getSelect(),
            $this->getDeleteScheduledActionsPreparedCollection(
                $merchantId, $withCreateDateFilter, $excludedListingsProductsIds
            )->getSelect(),
        ));

        $unionSelect->order(array('coefficient DESC'));
        $unionSelect->order(array('create_date ASC'));

        $limit = (int)$this->getConfigValue('/amazon/listing/product/action/scheduled_data/', 'limit');
        $unionSelect->limit($limit);

        return $unionSelect->query();
    }

    // ---------------------------------------

    private function getListScheduledActionsPreparedCollection($merchantId,
                                                               $withCreateDateFilter = false,
                                                               $excludedListingsProductsIds = array())
    {
        $priorityCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/list/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/list/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_LIST);
        $collection->addFieldToFilter('aa.merchant_id', $merchantId);

        if (!empty($excludedListingsProductsIds)) {
            $collection->addFieldToFilter('listing_product_id', array('nin' => $excludedListingsProductsIds));
        }

        $collection->getSelect()->columns(array('filtered_tag' => new Zend_Db_Expr('\'\'')));

        if ($withCreateDateFilter && Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/list/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    private function getRelistScheduledActionsPreparedCollection($merchantId,
                                                                 $withCreateDateFilter = false,
                                                                 $excludedListingsProductsIds = array())
    {
        $priorityCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/relist/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/relist/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_RELIST);
        $collection->addFieldToFilter('aa.merchant_id', $merchantId);

        if (!empty($excludedListingsProductsIds)) {
            $collection->addFieldToFilter('listing_product_id', array('nin' => $excludedListingsProductsIds));
        }

        $collection->getSelect()->columns(array('filtered_tag' => new Zend_Db_Expr('\'\'')));

        if ($withCreateDateFilter && Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/relist/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    private function getReviseQtyScheduledActionsPreparedCollection($merchantId,
                                                                    $withCreateDateFilter = false,
                                                                    $excludedListingsProductsIds = array())
    {
        $priorityCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/revise_qty/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/revise_qty/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);
        $collection->getSelect()->where(
            'main_table.tag LIKE \'%/qty/%\' OR main_table.tag IS NULL OR main_table.tag = \'\''
        );
        $collection->addFieldToFilter('aa.merchant_id', $merchantId);

        if (!empty($excludedListingsProductsIds)) {
            $collection->addFieldToFilter('listing_product_id', array('nin' => $excludedListingsProductsIds));
        }

        $collection->getSelect()->columns(array('filtered_tag' => new Zend_Db_Expr('\'qty\'')));

        if ($withCreateDateFilter && Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/revise_qty/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    private function getRevisePriceRegularScheduledActionsPreparedCollection($merchantId,
                                                                             $withCreateDateFilter = false,
                                                                             $excludedListingsProductsIds = array())
    {
        $priorityCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/revise_price/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/revise_price/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);
        $collection->getSelect()->where(
            'main_table.tag LIKE \'%/price_regular/%\' OR main_table.tag IS NULL OR main_table.tag = \'\''
        );
        $collection->addFieldToFilter('aa.merchant_id', $merchantId);

        if (!empty($excludedListingsProductsIds)) {
            $collection->addFieldToFilter('listing_product_id', array('nin' => $excludedListingsProductsIds));
        }

        $collection->getSelect()->columns(array('filtered_tag' => new Zend_Db_Expr('\'price_regular\'')));

        if ($withCreateDateFilter && Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/revise_price/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    private function getRevisePriceBusinessScheduledActionsPreparedCollection($merchantId,
                                                                              $withCreateDateFilter = false,
                                                                              $excludedListingsProductsIds = array())
    {
        $priorityCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/revise_price/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/revise_price/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);
        $collection->getSelect()->where(
            'main_table.tag LIKE \'%/price_business/%\' OR main_table.tag IS NULL OR main_table.tag = \'\''
        );
        $collection->addFieldToFilter('aa.merchant_id', $merchantId);

        if (!empty($excludedListingsProductsIds)) {
            $collection->addFieldToFilter('listing_product_id', array('nin' => $excludedListingsProductsIds));
        }

        $collection->getSelect()->columns(array('filtered_tag' => new Zend_Db_Expr('\'price_business\'')));

        if ($withCreateDateFilter && Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/revise_price/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    private function getReviseDetailsScheduledActionsPreparedCollection($merchantId,
                                                                        $withCreateDateFilter = false,
                                                                        $excludedListingsProductsIds = array())
    {
        $priorityCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/revise_details/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/revise_details/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);
        $collection->getSelect()->where(
            'main_table.tag LIKE \'%/details/%\' OR main_table.tag IS NULL OR main_table.tag = \'\''
        );
        $collection->addFieldToFilter('aa.merchant_id', $merchantId);

        if (!empty($excludedListingsProductsIds)) {
            $collection->addFieldToFilter('listing_product_id', array('nin' => $excludedListingsProductsIds));
        }

        $collection->getSelect()->columns(array('filtered_tag' => new Zend_Db_Expr('\'details\'')));

        if ($withCreateDateFilter && Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/revise_details/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    private function getReviseImagesScheduledActionsPreparedCollection($merchantId,
                                                                       $withCreateDateFilter = false,
                                                                       $excludedListingsProductsIds = array())
    {
        $priorityCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/revise_images/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/revise_images/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);
        $collection->getSelect()->where(
            'main_table.tag LIKE \'%/images/%\' OR main_table.tag IS NULL OR main_table.tag = \'\''
        );
        $collection->addFieldToFilter('aa.merchant_id', $merchantId);

        if (!empty($excludedListingsProductsIds)) {
            $collection->addFieldToFilter('listing_product_id', array('nin' => $excludedListingsProductsIds));
        }

        $collection->getSelect()->columns(array('filtered_tag' => new Zend_Db_Expr('\'images\'')));

        if ($withCreateDateFilter && Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/revise_images/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    private function getStopScheduledActionsPreparedCollection($merchantId,
                                                               $withCreateDateFilter = false,
                                                               $excludedListingsProductsIds = array())
    {
        $priorityCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/stop/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/stop/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_STOP);
        $collection->addFieldToFilter('aa.merchant_id', $merchantId);

        if (!empty($excludedListingsProductsIds)) {
            $collection->addFieldToFilter('listing_product_id', array('nin' => $excludedListingsProductsIds));
        }

        $collection->getSelect()->columns(array('filtered_tag' => new Zend_Db_Expr('\'\'')));

        if ($withCreateDateFilter && Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/stop/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    private function getDeleteScheduledActionsPreparedCollection($merchantId,
                                                                 $withCreateDateFilter = false,
                                                                 $excludedListingsProductsIds = array())
    {
        $priorityCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/delete/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)$this->getConfigValue(
            '/amazon/listing/product/action/delete/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_DELETE);
        $collection->addFieldToFilter('aa.merchant_id', $merchantId);

        if (!empty($excludedListingsProductsIds)) {
            $collection->addFieldToFilter('listing_product_id', array('nin' => $excludedListingsProductsIds));
        }

        $collection->getSelect()->columns(array('filtered_tag' => new Zend_Db_Expr('\'\'')));

        if ($withCreateDateFilter && Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/delete/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    // ---------------------------------------

    /**
     * @param $priorityCoefficient
     * @param $waitIncreaseCoefficient
     * @return Ess_M2ePro_Model_Mysql4_Listing_Product_ScheduledAction_Collection
     */
    private function getScheduledActionsPreparedCollection($priorityCoefficient, $waitIncreaseCoefficient)
    {
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->getSelect()->joinLeft(
            array('lp' => Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable()),
            'main_table.listing_product_id = lp.id'
        );
        $collection->getSelect()->joinLeft(
            array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            'lp.listing_id = l.id'
        );
        $collection->getSelect()->joinLeft(
            array('aa' => Mage::getResourceModel('M2ePro/Amazon_Account')->getMainTable()),
            'l.account_id = aa.account_id'
        );
        $collection->getSelect()->joinLeft(
            array('pl' => Mage::getResourceModel('M2ePro/Processing_Lock')->getMainTable()),
            'pl.object_id = main_table.listing_product_id AND model_name = \'M2ePro/Listing_Product\''
        );

        $collection->addFieldToFilter('component', Ess_M2ePro_Helper_Component_Amazon::NICK);
        $collection->addFieldToFilter('pl.id', array('null' => true));

        $now = Mage::helper('M2ePro')->getCurrentGmtDate();
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'listing_product_id' => 'main_table.listing_product_id',
                'account_id'         => 'aa.account_id',
                'action_type'        => 'main_table.action_type',
                'tag'                => new Zend_Db_Expr('NULL'),
                'additional_data'    => 'main_table.additional_data',
                'coefficient'        => new Zend_Db_Expr(
                    "{$priorityCoefficient} +
                    (time_to_sec(timediff('{$now}', main_table.create_date)) / 3600) * {$waitIncreaseCoefficient}"
                ),
                'create_date'        => 'create_date',
            ));

        return $collection;
    }

    //########################################

    private function canAddToLastExistedPack(array $feedsPacks, $feedType, $accountId)
    {
        if (empty($feedsPacks[$feedType][$accountId])) {
            return false;
        }

        $lastPackIndex = count($feedsPacks[$feedType][$accountId]) - 1;

        return count($feedsPacks[$feedType][$accountId][$lastPackIndex]) < $this->getMaxPackSize($feedType);
    }

    private function addToLastExistedPack(array &$feedsPacks, $feedType, $scheduledActionData)
    {
        if (empty($feedsPacks[$feedType][$scheduledActionData['account_id']])) {
            $lastPackIndex = 0;
        } else {
            $lastPackIndex = count($feedsPacks[$feedType][$scheduledActionData['account_id']]) - 1;
        }

        $feedsPacks[$feedType][$scheduledActionData['account_id']][$lastPackIndex][] = $scheduledActionData;
    }

    // ---------------------------------------

    private function addToNewPack(array &$feedsPacks, $feedType, $scheduledActionData)
    {
        if (empty($feedsPacks[$feedType][$scheduledActionData['account_id']])) {
            $newPackIndex = 0;
        } else {
            $newPackIndex = count($feedsPacks[$feedType][$scheduledActionData['account_id']]);
        }

        $feedsPacks[$feedType][$scheduledActionData['account_id']][$newPackIndex][] = $scheduledActionData;
    }

    //########################################

    private function getFeedTypes($actionType, $tag = NULL)
    {
        switch ($actionType) {
            case Ess_M2ePro_Model_Listing_Product::ACTION_LIST:
                return array(self::FEED_TYPE_ADD);

            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                $feedTypes = array(
                    self::FEED_TYPE_UPDATE_QTY,
                );

                if ($tag == 'price') {
                    $feedTypes[] = self::FEED_TYPE_UPDATE_PRICE;
                }

                return $feedTypes;

            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:
                $feedTypes = array();

                if ($tag == 'qty') {
                    $feedTypes[] = self::FEED_TYPE_UPDATE_QTY;
                }
                if ($tag == 'price_regular' || $tag == 'price_business') {
                    $feedTypes[] = self::FEED_TYPE_UPDATE_PRICE;
                }
                if ($tag == 'details') {
                    $feedTypes[] = self::FEED_TYPE_UPDATE_DETAILS;
                }
                if ($tag == 'images') {
                    $feedTypes[] = self::FEED_TYPE_UPDATE_IMAGES;
                }

                return $feedTypes;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                return array(self::FEED_TYPE_UPDATE_QTY);

            case Ess_M2ePro_Model_Listing_Product::ACTION_DELETE:
                return array(self::FEED_TYPE_DELETE);

            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown action type.');
        }
    }

    private function getMaxPackSize($feedType)
    {
        $slowFeedTypes = array(
            self::FEED_TYPE_ADD,
            self::FEED_TYPE_UPDATE_DETAILS,
        );

        if (in_array($feedType, $slowFeedTypes)) {
            return 1000;
        }

        return 10000;
    }

    private function calculateRequestsCount($feedsPacks)
    {
        $requestsCount = 0;

        foreach ($feedsPacks as $feedType => $feedPacks) {
            foreach ($feedPacks as $accountId => $accountPacks) {
                $requestsCount += count($accountPacks);
            }
        }

        return $requestsCount;
    }

    //########################################

    private function getFirstConnectionErrorDate()
    {
        $registry = Mage::getModel('M2ePro/Registry');
        $registry->load(self::FIRST_CONNECTION_ERROR_DATE_REGISTRY_KEY, 'key');

        return $registry->getValue();
    }

    private function setFirstConnectionErrorDate($date)
    {
        $registry = Mage::getModel('M2ePro/Registry');
        $registry->load(self::FIRST_CONNECTION_ERROR_DATE_REGISTRY_KEY, 'key');

        $registry->setData('key', self::FIRST_CONNECTION_ERROR_DATE_REGISTRY_KEY);
        $registry->setData('value', $date);

        $registry->save();
    }

    private function removeFirstConnectionErrorDate()
    {
        $registry = Mage::getModel('M2ePro/Registry');
        $registry->load(self::FIRST_CONNECTION_ERROR_DATE_REGISTRY_KEY, 'key');

        if ($registry->getId()) {
            $registry->delete();
        }
    }

    //########################################

    private function completeProcessingAction(Ess_M2ePro_Model_Amazon_Listing_Product_Action_Processing $action,
                                              array $data)
    {
        $processing = $action->getProcessing();

        $processing->setSettings('result_data', $data);
        $processing->setData('is_completed', 1);

        $processing->save();

        $action->deleteInstance();
    }

    private function getResponseMessages(array $responseData, array $responseMessages, $listingProductId)
    {
        $messages = $responseMessages;

        if (!empty($responseData['messages'][0])) {
            $messages = array_merge($messages, $responseData['messages']['0']);
        }

        if (!empty($responseData['messages']['0-id'])) {
            $messages = array_merge($messages, $responseData['messages']['0-id']);
        }

        if (!empty($responseData['messages'][$listingProductId.'-id'])) {
            $messages = array_merge($messages, $responseData['messages'][$listingProductId.'-id']);
        }

        return $messages;
    }

    private function getServerCommand($processingActionType)
    {
        switch ($processingActionType) {
            case Ess_M2ePro_Model_Amazon_Listing_Product_Action_Processing::TYPE_ADD:
                return array('product', 'add', 'entities');

            case Ess_M2ePro_Model_Amazon_Listing_Product_Action_Processing::TYPE_UPDATE:
                return array('product', 'update', 'entities');

            case Ess_M2ePro_Model_Amazon_Listing_Product_Action_Processing::TYPE_DELETE:
                return array('product', 'delete', 'entities');

            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown action type');
        }
    }

    private function getLockIdentifier($actionType, array $params)
    {
        switch ($actionType) {
            case Ess_M2ePro_Model_Listing_Product::ACTION_LIST:
                return 'list';
            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                return 'relist';
            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:
                return 'revise';
            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                if (!empty($params['remove'])) {
                    return 'stop_and_remove';
                } else {
                    return 'stop';
                }
            case Ess_M2ePro_Model_Listing_Product::ACTION_DELETE:
                return 'delete_and_remove';
        }

        throw new Ess_M2ePro_Model_Exception('Wrong Action type');
    }

    // ---------------------------------------

    private function getConfigValue($group, $key)
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue($group, $key);
    }

    //########################################
}