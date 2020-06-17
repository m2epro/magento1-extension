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

    const LIST_PRIORITY           = 25;
    const RELIST_PRIORITY         = 125;
    const STOP_PRIORITY           = 1000;
    const DELETE_PRIORITY         = 1000;
    const REVISE_QTY_PRIORITY     = 500;
    const REVISE_PRICE_PRIORITY   = 250;
    const REVISE_DETAILS_PRIORITY = 50;
    const REVISE_IMAGES_PRIORITY  = 50;

    const PENDING_REQUEST_MAX_LIFE_TIME = 86400;

    const FIRST_CONNECTION_ERROR_DATE_REGISTRY_KEY = '/amazon/listing/product/action/first_connection_error/date/';

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
                $this->getScheduledActionsDataStatement($merchantId),
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

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function removeMissedProcessingActions()
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
     * @param array $feedsPacks
     * @param Zend_Db_Statement $scheduledActionsDataStatement
     * @param int $availableRequestsCount
     * @throws Ess_M2ePro_Model_Exception_Logic
     * @throws Zend_Db_Statement_Exception
     */
    protected function fillFeedsPacks(
        array &$feedsPacks,
        Zend_Db_Statement $scheduledActionsDataStatement,
        $availableRequestsCount = null
    ) {
        $canCreateNewPacks = true;

        while ($scheduledActionData = $scheduledActionsDataStatement->fetch()) {
            $feedTypes = $this->getFeedTypes($scheduledActionData['action_type'], $scheduledActionData['filtered_tag']);

            $canBeAdded = false;

            foreach ($feedTypes as $feedType) {
                if ($this->canAddToLastExistedPack($feedsPacks, $feedType, $scheduledActionData['account_id'])) {
                    $canBeAdded = true;
                    continue;
                }

                if (!$canCreateNewPacks || ($availableRequestsCount !== null && $availableRequestsCount <= 0)) {
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
    protected function prepareAccountsActions(array $feedsPacks)
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

    /**
     * @param int $accountId
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
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function initProcessingActions($actionType, array $listingsProductsData, $groupHash)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingsProductsCollection */
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
            '/amazon/listing/product/action/processing/prepare/', 'max_listings_products_count'
        );

        /** @var Ess_M2ePro_Model_Resource_Amazon_Listing_Product_Action_Processing_Collection $processingActionColl */
        $processingActionColl = Mage::getResourceModel(
            'M2ePro/Amazon_Listing_Product_Action_Processing_Collection'
        );
        $processingActionColl->addFieldToFilter('is_prepared', 0);
        $processingActionColl->getSelect()->limit($processingActionPreparationLimit);
        $processingActionColl->getSelect()->order('id ASC');

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Processing[] $processingActions */
        $processingActions = $processingActionColl->getItems();

        $listingsProductsIds = $processingActionColl->getColumnValues('listing_product_id');

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingsProductsCollection */
        $listingsProductsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingsProductsCollection->addFieldToFilter('id', array('in' => $listingsProductsIds));

        $processingIds = $processingActionColl->getColumnValues('processing_id');

        $processingCollection = Mage::getResourceModel('M2ePro/Processing_Collection');
        $processingCollection->addFieldToFilter('id', array('in' => $processingIds));

        $dispatcher = Mage::getModel('M2ePro/Amazon_Connector_Product_Dispatcher');

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
     * @param array $listingsProductsData
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function prepareScheduledActions(array $listingsProductsData)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingsProductsCollection */
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

            $types = $existedConfigurator->getAllowedDataTypes();
            if (empty($types)) {
                $scheduledActionManager->deleteAction($scheduledAction);
            } else {
                $scheduledAction->setData('tag', '/'.trim(implode('/', $tags), '/').'/');
                $scheduledActionManager->updateAction($scheduledAction);
            }
        }
    }

    /**
     * @param $actionType
     * @param Ess_M2ePro_Model_Amazon_Listing_Product_Action_Processing[] $processingActions
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function processGroupedProcessingActions(array $processingActions, $actionType)
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

            if ($exception instanceof Ess_M2ePro_Model_Exception_Connection) {
                $isRepeat = $exception->handleRepeatTimeout(self::FIRST_CONNECTION_ERROR_DATE_REGISTRY_KEY);
                if ($isRepeat) {
                    return;
                }
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
        $requestPendingSingle->setData(
            array(
            'component'       => Ess_M2ePro_Helper_Component_Amazon::NICK,
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

        Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Action_Processing')->markAsInProgress(
            $actionsIds, $requestPendingSingle
        );
    }

    //########################################

    /**
     * @param $merchantId
     * @return Zend_Db_Statement
     * @throws Ess_M2ePro_Model_Exception_Logic
     * @throws Zend_Db_Select_Exception
     */
    protected function getScheduledActionsDataStatement($merchantId)
    {
        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $connRead = $resource->getConnection('core_read');

        $unionSelect = $connRead->select()->union(
            array(
                $this->getListScheduledActionsPreparedCollection($merchantId)->getSelect(),
                $this->getRelistQtyScheduledActionsPreparedCollection($merchantId)->getSelect(),
                $this->getRelistPriceScheduledActionsPreparedCollection($merchantId)->getSelect(),
                $this->getReviseQtyScheduledActionsPreparedCollection($merchantId)->getSelect(),
                $this->getRevisePriceRegularScheduledActionsPreparedCollection($merchantId)->getSelect(),
                $this->getRevisePriceBusinessScheduledActionsPreparedCollection($merchantId)->getSelect(),
                $this->getReviseDetailsScheduledActionsPreparedCollection($merchantId)->getSelect(),
                $this->getReviseImagesScheduledActionsPreparedCollection($merchantId)->getSelect(),
                $this->getStopScheduledActionsPreparedCollection($merchantId)->getSelect(),
                $this->getDeleteScheduledActionsPreparedCollection($merchantId)->getSelect(),
            )
        );

        $unionSelect->order(array('coefficient DESC'));
        $unionSelect->order(array('create_date ASC'));

        $limit = (int)$this->getConfigValue('/amazon/listing/product/action/scheduled_data/', 'limit');
        $unionSelect->limit($limit);

        return $unionSelect->query();
    }

    // ---------------------------------------

    /**
     * @param $merchantId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getListScheduledActionsPreparedCollection($merchantId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK)
            ->getScheduledActionsPreparedCollection(
                self::LIST_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_LIST
            )
            ->joinAccountTable()
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("''"))
            ->addFieldToFilter('account.merchant_id', $merchantId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/list/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    /**
     * @param $merchantId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getRelistQtyScheduledActionsPreparedCollection($merchantId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK)
            ->getScheduledActionsPreparedCollection(
                self::RELIST_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_RELIST
            )
            ->joinAccountTable()
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("'qty'"))
            ->addTagFilter('qty', true)
            ->addFieldToFilter('account.merchant_id', $merchantId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/relist/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    /**
     * @param $merchantId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getRelistPriceScheduledActionsPreparedCollection($merchantId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK)
            ->getScheduledActionsPreparedCollection(
                self::RELIST_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_RELIST
            )
            ->joinAccountTable()
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("'price'"))
            ->addTagFilter('price', true)
            ->addFieldToFilter('account.merchant_id', $merchantId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/relist/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    /**
     * @param $merchantId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getReviseQtyScheduledActionsPreparedCollection($merchantId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_QTY_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addTagFilter('qty', true)
            ->joinAccountTable()
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("'qty'"))
            ->addFieldToFilter('account.merchant_id', $merchantId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/revise_qty/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    /**
     * @param $merchantId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getRevisePriceRegularScheduledActionsPreparedCollection($merchantId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_PRICE_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addTagFilter('price_regular', true)
            ->joinAccountTable()
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("'price_regular'"))
            ->addFieldToFilter('account.merchant_id', $merchantId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/revise_price/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    /**
     * @param $merchantId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getRevisePriceBusinessScheduledActionsPreparedCollection($merchantId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_PRICE_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addTagFilter('price_business', true)
            ->joinAccountTable()
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("'price_business'"))
            ->addFieldToFilter('account.merchant_id', $merchantId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/revise_price/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    /**
     * @param $merchantId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getReviseDetailsScheduledActionsPreparedCollection($merchantId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_DETAILS_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addTagFilter('details', true)
            ->joinAccountTable()
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("'details'"))
            ->addFieldToFilter('account.merchant_id', $merchantId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/revise_details/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    /**
     * @param $merchantId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getReviseImagesScheduledActionsPreparedCollection($merchantId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_IMAGES_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addTagFilter('images', true)
            ->joinAccountTable()
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("'images'"))
            ->addFieldToFilter('account.merchant_id', $merchantId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/revise_images/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    /**
     * @param $merchantId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getStopScheduledActionsPreparedCollection($merchantId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK)
            ->getScheduledActionsPreparedCollection(
                self::STOP_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_STOP
            )
            ->joinAccountTable()
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("''"))
            ->addFieldToFilter('account.merchant_id', $merchantId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/stop/', 'min_allowed_wait_interval'
            );
            $collection->addCreatedBeforeFilter($minAllowedWaitInterval);
        }

        return $collection;
    }

    /**
     * @param $merchantId
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getDeleteScheduledActionsPreparedCollection($merchantId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $collection->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK)
            ->getScheduledActionsPreparedCollection(
                self::DELETE_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_DELETE
            )
            ->joinAccountTable()
            ->addFilteredTagColumnToSelect(new Zend_Db_Expr("''"))
            ->addFieldToFilter('account.merchant_id', $merchantId);

        if (Mage::helper('M2ePro/Module')->isProductionEnvironment()) {
            $minAllowedWaitInterval = (int)$this->getConfigValue(
                '/amazon/listing/product/action/delete/', 'min_allowed_wait_interval'
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

    /**
     * @param $feedType
     * @return int
     */
    protected function getMaxPackSize($feedType)
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

    //########################################

    /**
     * @param Ess_M2ePro_Model_Amazon_Listing_Product_Action_Processing $action
     * @param array $data
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function completeProcessingAction(
        Ess_M2ePro_Model_Amazon_Listing_Product_Action_Processing $action,
        array $data
    ) {
        $processing = $action->getProcessing();

        $processing->setSettings('result_data', $data);
        $processing->setData('is_completed', 1);

        $processing->save();

        $action->deleteInstance();
    }

    /**
     * @param array $responseData
     * @param array $responseMessages
     * @param int $listingProductId
     * @return array
     */
    protected function getResponseMessages(array $responseData, array $responseMessages, $listingProductId)
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

    /**
     * @param $processingActionType
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getServerCommand($processingActionType)
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

    /**
     * @param $actionType
     * @param array $params
     * @return string
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function getLockIdentifier($actionType, array $params)
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
