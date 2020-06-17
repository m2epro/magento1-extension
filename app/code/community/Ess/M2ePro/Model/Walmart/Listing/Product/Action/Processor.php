<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processor
{
    const FEED_TYPE_UPDATE_QTY        = 'update_qty';
    const FEED_TYPE_UPDATE_LAG_TIME   = 'update_lag_time';
    const FEED_TYPE_UPDATE_PRICE      = 'update_price';
    const FEED_TYPE_UPDATE_PROMOTIONS = 'update_promotions';
    const FEED_TYPE_UPDATE_DETAILS    = 'update_details';

    const RELIST_PRIORITY            = 125;
    const STOP_PRIORITY              = 1000;
    const REVISE_QTY_PRIORITY        = 500;
    const REVISE_LAG_TIME_PRIORITY   = 500;
    const REVISE_PRICE_PRIORITY      = 250;
    const REVISE_DETAILS_PRIORITY    = 50;
    const REVISE_PROMOTIONS_PRIORITY = 50;

    const PENDING_REQUEST_MAX_LIFE_TIME = 86400;

    const FIRST_CONNECTION_ERROR_DATE_REGISTRY_KEY = '/walmart/listing/product/action/first_connection_error/date/';

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     * @throws Zend_Db_Select_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function process()
    {
        $this->removeMissedProcessingActions();

        /** @var Ess_M2ePro_Model_Resource_Account_Collection $accountCollection */
        $accountCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Account');
        /** @var Ess_M2ePro_Model_Account[] $accounts */
        $accounts = $accountCollection->getItems();

        foreach ($accounts as $account) {
            $feedsPacks               = $this->getFilledPacksByFeeds($account);
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

            $this->registerRequestsInThrottling($feedsPacks);
        }

        $this->prepareProcessingActions();

        $fullyPreparedGroupHashes = Mage::getResourceModel('M2ePro/Walmart_Listing_Product_Action_Processing')
            ->getFullyPreparedGroupHashes();

        foreach ($fullyPreparedGroupHashes as $groupHash) {
            $processingActionCollection = Mage::getResourceModel(
                'M2ePro/Walmart_Listing_Product_Action_Processing_Collection'
            );
            $processingActionCollection->addFieldToFilter('group_hash', $groupHash);
            $processingActionCollection->addFieldToFilter('is_prepared', 1);
            $processingActionCollection->addFieldToFilter(
                'type',
                array(
                    'in' => array(
                        Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processing::TYPE_UPDATE,
                    )
                )
            );
            $processingActionCollection->addFieldToFilter('request_pending_single_id', array('null' => true));

            $this->processGroupedProcessingActions($processingActionCollection->getItems());
        }
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function removeMissedProcessingActions()
    {
        $actionCollection = Mage::getResourceModel('M2ePro/Walmart_Listing_Product_Action_Processing_Collection');
        $actionCollection->getSelect()->joinLeft(
            array('p' => Mage::getResourceModel('M2ePro/Processing')->getMainTable()),
            'p.id = main_table.processing_id',
            array()
        );
        $actionCollection->addFieldToFilter('p.id', array('null' => true));

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processing[] $actions */
        $actions = $actionCollection->getItems();

        foreach ($actions as $action) {
            $action->deleteInstance();
        }
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Account $account
     * @return array|false
     * @throws Ess_M2ePro_Model_Exception_Logic
     * @throws Zend_Db_Select_Exception
     * @throws Zend_Db_Statement_Exception
     */
    protected function getFilledPacksByFeeds(Ess_M2ePro_Model_Account $account)
    {
        $availableFeeds = array(
            self::FEED_TYPE_UPDATE_DETAILS,
            self::FEED_TYPE_UPDATE_PROMOTIONS,
            self::FEED_TYPE_UPDATE_QTY,
            self::FEED_TYPE_UPDATE_LAG_TIME,
            self::FEED_TYPE_UPDATE_PRICE,
        );

        $canCreateNewPacksByFeedType = array_combine(
            $availableFeeds, array_fill(0, count($availableFeeds), true)
        );

        $feedsPacks = array_combine(
            $availableFeeds, array_fill(0, count($availableFeeds), array())
        );

        /** @var Ess_M2ePro_Model_Walmart_ThrottlingManager $throttlingManager */
        $throttlingManager = Mage::getModel('M2ePro/Walmart_ThrottlingManager');
        $scheduledActionsDataStatement = $this->getScheduledActionsDataStatement($account);

        while ($scheduledActionData = $scheduledActionsDataStatement->fetch()) {
            $feedTypes = $this->getFeedTypes($scheduledActionData['action_type'], $scheduledActionData['filtered_tag']);

            $canBeAdded = false;

            foreach ($feedTypes as $feedType) {
                if ($this->canAddToLastExistedPack($feedsPacks, $feedType, $scheduledActionData['account_id'])) {
                    $canBeAdded = true;
                    continue;
                }

                $availableRequestsCount = $throttlingManager->getAvailableRequestsCount(
                    $scheduledActionData['account_id'], $feedType
                );

                if (!$canCreateNewPacksByFeedType[$feedType] || $availableRequestsCount <= 0) {
                    $canBeAdded = false;
                    $canCreateNewPacksByFeedType[$feedType] = false;
                    break;
                }

                $canBeAdded = true;
            }

            if (!$canBeAdded) {
                continue;
            }

            foreach ($feedTypes as $feedType) {
                if ($this->canAddToLastExistedPack($feedsPacks, $feedType, $scheduledActionData['account_id'])) {
                    $this->addToLastExistedPack($feedsPacks, $feedType, $scheduledActionData);
                    continue;
                }

                if (!$canCreateNewPacksByFeedType[$feedType]) {
                    continue;
                }

                $this->addToNewPack($feedsPacks, $feedType, $scheduledActionData);
            }
        }

        return $feedsPacks;
    }

    /**
     * @param array $feedsPacks
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function prepareAccountsActions(array $feedsPacks)
    {
        $result = array();
        $accounts = array();

        foreach ($feedsPacks as $feedType => $feedPacks) {
            foreach ($feedPacks as $accountId => $accountPacks) {
                if (!isset($accounts[$accountId])) {
                    $accounts[$accountId] = Mage::helper('M2ePro/Component_Walmart')->getObject('Account', $accountId);
                }

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
                            'M2ePro/Walmart_Listing_Product_Action_Configurator'
                        );

                        $additionalData = Mage::helper('M2ePro')->jsonDecode($listingProductData['additional_data']);
                        if (!empty($additionalData['configurator'])) {
                            $listingProductConfigurator->setData($additionalData['configurator']);
                        }

                        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator $configurator */
                        if (!empty($result[$accountId][$actionType][$listingProductId]['configurator'])) {
                            $configurator = $result[$accountId][$actionType][$listingProductId]['configurator'];
                        } else {
                            $configurator = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Configurator');
                            $configurator->disableAll();
                        }

                        switch ($listingProductData['filtered_tag']) {
                            case 'qty':
                                if ($listingProductConfigurator->isQtyAllowed()) {
                                    $configurator->allowQty();
                                }
                                break;

                            case 'lag_time':
                                if ($listingProductConfigurator->isLagTimeAllowed()) {
                                    $configurator->allowLagTime();
                                }
                                break;

                            case 'price':
                                if ($listingProductConfigurator->isPriceAllowed()) {
                                    $configurator->allowPrice();
                                }
                                break;

                            case 'promotions':
                                if ($listingProductConfigurator->isPromotionsAllowed()) {
                                    $configurator->allowPromotions();
                                }
                                break;

                            case 'details':
                                if ($listingProductConfigurator->isDetailsAllowed()) {
                                    $configurator->allowDetails();
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

    /**
     * @param array $accountsActions
     * @return array
     */
    protected function prepareRequestsPacks(array $accountsActions)
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

                        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator $configurator */
                        $configurator = $listingProductData['configurator'];
                        if ($configurator->isDetailsAllowed()) {
                            $groupHashesMetadata[$accountId][$groupHash]['slow_actions_count']++;
                        }
                    }

                    $requestsPacks[$accountId][$actionType][$groupHash][$listingProductId] = $listingProductData;
                }
            }
        }

        return $requestsPacks;
    }

    /**
     * @param $accountId
     * @param array $groupHashesMetadata
     * @param array $listingProductData
     * @return int|string|null
     */
    protected function getActualGroupHash($accountId, array $groupHashesMetadata, array $listingProductData)
    {
        if (empty($groupHashesMetadata[$accountId])) {
            return Mage::helper('M2ePro')->generateUniqueHash();
        }

        end($groupHashesMetadata[$accountId]);
        $lastGroupHash = key($groupHashesMetadata[$accountId]);

        if ($listingProductData['action_type'] != Ess_M2ePro_Model_Listing_Product::ACTION_REVISE) {
            return $lastGroupHash;
        }

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator $configurator */
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
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function initProcessingActions($actionType, array $listingsProductsData, $groupHash)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingsProductsCollection */
        $listingsProductsCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $listingsProductsCollection->addFieldToFilter('id', array_keys($listingsProductsData));

        foreach ($listingsProductsData as $listingProductId => $listingProductData) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = $listingsProductsCollection->getItemById($listingProductId);

            $processingRunner = Mage::getModel('M2ePro/Walmart_Connector_Product_ProcessingRunner');
            $processingRunner->setListingProduct($listingProduct);

            $configurator = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Configurator');
            if (isset($listingProductData['configurator'])) {
                $configurator = clone $listingProductData['configurator'];
            }

            $params = array();
            if (!empty($listingProductData['additional_data'])) {
                $additionalData = Mage::helper('M2ePro')->jsonDecode($listingProductData['additional_data']);
                !empty($additionalData['params']) && $params = $additionalData['params'];
            }

            $processingRunner->setParams(
                array(
                'account_id'         => $listingProduct->getAccount()->getId(),
                'listing_product_id' => $listingProductId,
                'configurator'       => $configurator->getData(),
                'action_type'        => $actionType,
                'lock_identifier'    => $this->getLockIdentifier($actionType, $params),
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
        $processingActionColl->addFieldToFilter(
            'type',
            array(
                'in' => array(
                    Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processing::TYPE_UPDATE,
                )
            )
        );
        $processingActionColl->getSelect()->limit($processingActionPreparationLimit);
        $processingActionColl->getSelect()->order('id ASC');

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processing[] $processingActions */
        $processingActions = $processingActionColl->getItems();

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
                $processingAction->getProcessing()->deleteInstance();
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
     * @throws Ess_M2ePro_Model_Exception_Logic
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
            $listingProductData = $listingsProductsData[$scheduledAction->getListingProductId()];

            if (!$scheduledAction->isActionTypeRevise() || empty($listingProductData['configurator'])) {
                $scheduledActionManager->deleteAction($scheduledAction);
                continue;
            }

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator $newConfigurator */
            $newConfigurator = $listingProductData['configurator'];

            $existedConfigurator = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Configurator');

            $tags = array_filter(explode('/', $scheduledAction->getTag()));
            $tags = array_flip($tags);

            $additionalData = $scheduledAction->getAdditionalData();
            if (!empty($additionalData['configurator'])) {
                $existedConfigurator->setData($additionalData['configurator']);
            }

            $existedConfigurator->setModeIncluding();

            foreach ($newConfigurator->getAllowedDataTypes() as $allowedDataType) {
                switch ($allowedDataType) {
                    case 'qty':
                        $existedConfigurator->disallowQty();
                        unset($tags['qty']);
                        break;

                    case 'lag_time':
                        $existedConfigurator->disallowLagTime();
                        unset($tags['lag_time']);
                        break;

                    case 'price':
                        $existedConfigurator->disallowPrice();
                        unset($tags['price']);
                        break;

                    case 'promotions':
                        $existedConfigurator->disallowPromotions();
                        unset($tags['promotions']);
                        break;

                    case 'details':
                        $existedConfigurator->disallowDetails();
                        unset($tags['details']);
                        break;
                }
            }

            $additionalData['configurator'] = $existedConfigurator->getData();
            $scheduledAction->setSettings('additional_data', $additionalData);

            $types = $existedConfigurator->getAllowedDataTypes();
            if (empty($types)) {
                $scheduledActionManager->deleteAction($scheduledAction);
            } else {
                $tags = array_keys($tags);
                $scheduledAction->setData('tag', '/'.trim(implode('/', $tags), '/').'/');
                $scheduledActionManager->updateAction($scheduledAction);
            }
        }
    }

    /**
     * @param array $processingActions
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function processGroupedProcessingActions(array $processingActions)
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
            'product', 'update', 'entities',
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
            'expiration_date' => Mage::helper('M2ePro')->getDate(
                Mage::helper('M2ePro')->getCurrentGmtDate(true)+self::PENDING_REQUEST_MAX_LIFE_TIME
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
     * @throws Zend_Db_Select_Exception
     */
    protected function getScheduledActionsDataStatement(Ess_M2ePro_Model_Account $account)
    {
        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $connRead = $resource->getConnection('core_read');

        $unionSelect = $connRead->select()->union(
            array(
                $this->getRelistQtyScheduledActionsPreparedCollection($account->getId())->getSelect(),
                $this->getRelistPriceScheduledActionsPreparedCollection($account->getId())->getSelect(),
                $this->getReviseQtyScheduledActionsPreparedCollection($account->getId())->getSelect(),
                $this->getReviseLagTimeScheduledActionsPreparedCollection($account->getId())->getSelect(),
                $this->getRevisePriceScheduledActionsPreparedCollection($account->getId())->getSelect(),
                $this->getRevisePromotionsScheduledActionsPreparedCollection($account->getId())->getSelect(),
                $this->getReviseDetailsScheduledActionsPreparedCollection($account->getId())->getSelect(),
                $this->getStopScheduledActionsPreparedCollection($account->getId())->getSelect()
            )
        );

        $unionSelect->order(array('coefficient DESC'));
        $unionSelect->order(array('create_date ASC'));

        $limit = (int)$this->getConfigValue('/walmart/listing/product/action/scheduled_data/', 'limit');
        $unionSelect->limit($limit);

        return $unionSelect->query();
    }

    // ---------------------------------------

    /**
     * @param $accountId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getRelistQtyScheduledActionsPreparedCollection($accountId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK)
            ->getScheduledActionsPreparedCollection(
                self::RELIST_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_RELIST
            )
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("'qty'"))
            ->addTagFilter('qty', true)
            ->addFieldToFilter('l.account_id', $accountId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/walmart/listing/product/action/relist/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    /**
     * @param $accountId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getRelistPriceScheduledActionsPreparedCollection($accountId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK)
            ->getScheduledActionsPreparedCollection(
                self::RELIST_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_RELIST
            )
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("'price'"))
            ->addTagFilter('price', true)
            ->addFieldToFilter('l.account_id', $accountId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/walmart/listing/product/action/relist/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    /**
     * @param $accountId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getReviseQtyScheduledActionsPreparedCollection($accountId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_QTY_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("'qty'"))
            ->addTagFilter('qty', true)
            ->addFieldToFilter('l.account_id', $accountId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/walmart/listing/product/action/revise_qty/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    /**
     * @param $accountId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getReviseLagTimeScheduledActionsPreparedCollection($accountId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_LAG_TIME_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("'lag_time'"))
            ->addTagFilter('lag_time', true)
            ->addFieldToFilter('l.account_id', $accountId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/walmart/listing/product/action/revise_lag_time/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    /**
     * @param $accountId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getRevisePriceScheduledActionsPreparedCollection($accountId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_PRICE_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("'price'"))
            ->addTagFilter('price', true)
            ->addFieldToFilter('l.account_id', $accountId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/walmart/listing/product/action/revise_price/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    /**
     * @param $accountId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getRevisePromotionsScheduledActionsPreparedCollection($accountId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_PROMOTIONS_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("'promotions'"))
            ->addTagFilter('promotions', true)
            ->addFieldToFilter('l.account_id', $accountId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/walmart/listing/product/action/revise_promotions/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    /**
     * @param $accountId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getReviseDetailsScheduledActionsPreparedCollection($accountId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_DETAILS_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("'details'"))
            ->addTagFilter('details', true)
            ->addFieldToFilter('l.account_id', $accountId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/walmart/listing/product/action/revise_details/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    /**
     * @param $accountId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getStopScheduledActionsPreparedCollection($accountId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK)
            ->getScheduledActionsPreparedCollection(
                self::STOP_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_STOP
            )
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("''"))
            ->addFieldToFilter('l.account_id', $accountId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/walmart/listing/product/action/stop/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    //########################################

    /**
     * @param array $feedsPacks
     * @param $feedType
     * @param $accountId
     * @return bool
     */
    protected function canAddToLastExistedPack(array $feedsPacks, $feedType, $accountId)
    {
        if (empty($feedsPacks[$feedType][$accountId])) {
            return false;
        }

        $lastPackIndex = count($feedsPacks[$feedType][$accountId]) - 1;

        return count($feedsPacks[$feedType][$accountId][$lastPackIndex]) < $this->getMaxPackSize($feedType);
    }

    /**
     * @param array $feedsPacks
     * @param $feedType
     * @param $scheduledActionData
     */
    protected function addToLastExistedPack(array &$feedsPacks, $feedType, $scheduledActionData)
    {
        if (empty($feedsPacks[$feedType][$scheduledActionData['account_id']])) {
            $lastPackIndex = 0;
        } else {
            $lastPackIndex = count($feedsPacks[$feedType][$scheduledActionData['account_id']]) - 1;
        }

        $feedsPacks[$feedType][$scheduledActionData['account_id']][$lastPackIndex][] = $scheduledActionData;
    }

    // ---------------------------------------

    /**
     * @param array $feedsPacks
     * @param $feedType
     * @param $scheduledActionData
     */
    protected function addToNewPack(array &$feedsPacks, $feedType, $scheduledActionData)
    {
        if (empty($feedsPacks[$feedType][$scheduledActionData['account_id']])) {
            $newPackIndex = 0;
        } else {
            $newPackIndex = count($feedsPacks[$feedType][$scheduledActionData['account_id']]);
        }

        $feedsPacks[$feedType][$scheduledActionData['account_id']][$newPackIndex][] = $scheduledActionData;
    }

    //########################################

    /**
     * @param $actionType
     * @param $tag
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getFeedTypes($actionType, $tag = null)
    {
        if (!in_array(
            $actionType, array(
                Ess_M2ePro_Model_Listing_Product::ACTION_RELIST,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                Ess_M2ePro_Model_Listing_Product::ACTION_STOP
            )
        )) {
            throw new Ess_M2ePro_Model_Exception_Logic('Unknown action type.');
        }

        $feedTypesByTags = array(
            'qty'        => self::FEED_TYPE_UPDATE_QTY,
            'lag_time'   => self::FEED_TYPE_UPDATE_LAG_TIME,
            'price'      => self::FEED_TYPE_UPDATE_PRICE,
            'promotions' => self::FEED_TYPE_UPDATE_PROMOTIONS,
            'details'    => self::FEED_TYPE_UPDATE_DETAILS,
        );

        $feedTypes = array();
        if ($actionType == Ess_M2ePro_Model_Listing_Product::ACTION_RELIST) {
            $feedTypes[] = self::FEED_TYPE_UPDATE_QTY;

            if ($tag === 'price' || $tag === 'promotions') {
                $feedTypes[] = $feedTypesByTags[$tag];
            }

            return $feedTypes;
        }

        if ($actionType == Ess_M2ePro_Model_Listing_Product::ACTION_REVISE) {
            if ($tag !== null && isset($feedTypesByTags[$tag])) {
                $feedTypes[] = $feedTypesByTags[$tag];
            }

            return $feedTypes;
        }

        if ($actionType == Ess_M2ePro_Model_Listing_Product::ACTION_STOP) {
            $feedTypes[] = self::FEED_TYPE_UPDATE_QTY;

            return $feedTypes;
        }

        return $feedTypes;
    }

    /**
     * @param string $feedType
     * @return int
     */
    protected function getMaxPackSize($feedType)
    {
        if ($feedType == self::FEED_TYPE_UPDATE_DETAILS) {
            return 100;
        }

        return 1000;
    }

    //########################################

    /**
     * @param array $feedsPacks
     */
    protected function registerRequestsInThrottling($feedsPacks)
    {
        $throttlingManager = Mage::getModel('M2ePro/Walmart_ThrottlingManager');

        foreach ($feedsPacks as $feedType => $feedPacks) {
            foreach ($feedPacks as $accountId => $accountPacks) {
                $throttlingManager->registerRequests($accountId, $feedType, count($accountPacks));
            }
        }
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processing $action
     * @param array $data
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function completeProcessingAction(
        Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processing $action,
        array $data
    ) {
        $processing = $action->getProcessing();

        $processing->setSettings('result_data', $data);
        $processing->setData('is_completed', 1);

        $processing->save();

        $action->deleteInstance();
    }

    /**
     * @param $actionType
     * @param array $params
     * @return string
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function getLockIdentifier($actionType, array $params)
    {
        switch ($actionType) {
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
        }

        throw new Ess_M2ePro_Model_Exception('Wrong Action type');
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

    //########################################
}
