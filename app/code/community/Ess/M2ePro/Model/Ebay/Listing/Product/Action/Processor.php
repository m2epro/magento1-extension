<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processor
{
    const ACTION_MAX_LIFE_TIME = 86400;

    const MAX_PARALLEL_EXECUTION_PACK_SIZE = 10;

    const ONE_SERVER_CALL_INCREASE_TIME = 1;
    const MAX_TOTAL_EXECUTION_TIME      = 180;

    const CONNECTION_ERROR_REPEAT_TIMEOUT = 180;
    const FIRST_CONNECTION_ERROR_DATE_REGISTRY_KEY = '/ebay/listing/product/action/first_connection_error/date/';

    //####################################

    public function process()
    {
        $this->removeMissedProcessingActions();
        $this->completeExpiredActions();

        $actions = $this->getActionsForExecute();
        if (empty($actions)) {
            return;
        }

        $serialExecutionTime = $this->calculateSerialExecutionTime($actions);

        if ($serialExecutionTime <= self::MAX_TOTAL_EXECUTION_TIME) {
            $this->executeSerial($actions);
        } else {
            $this->executeParallel($actions);
        }
    }

    //####################################

    protected function removeMissedProcessingActions()
    {
        $actionCollection = Mage::getResourceModel('M2ePro/Ebay_Listing_Product_Action_Processing_Collection');
        $actionCollection->getSelect()->joinLeft(
            array('p' => Mage::getResourceModel('M2ePro/Processing')->getMainTable()),
            'p.id = main_table.processing_id',
            array()
        );
        $actionCollection->addFieldToFilter('p.id', array('null' => true));

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing[] $actions */
        $actions = $actionCollection->getItems();

        foreach ($actions as $action) {
            try {
                $action->deleteInstance();
            } catch (\Exception $exception) {
                Mage::helper('M2ePro/Module_Exception')->process($exception);
            }
        }
    }

    protected function completeExpiredActions()
    {
        $minimumAllowedDate = new DateTime('now', new DateTimeZone('UTC'));
        $minimumAllowedDate->modify('- '.self::ACTION_MAX_LIFE_TIME.' seconds');

        $actionCollection = Mage::getResourceModel('M2ePro/Ebay_Listing_Product_Action_Processing_Collection');
        $actionCollection->addFieldToFilter('create_date', array('lt' => $minimumAllowedDate->format('Y-m-d H:i:s')));

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing[] $actions */
        $actions = $actionCollection->getItems();
        if (empty($actions)) {
            return;
        }

        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
        $message->initFromPreparedData(
            'Request wait timeout exceeded.',
            Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
        );
        $message = $message->asArray();

        foreach ($actions as $action) {
            try {
                $this->completeAction($action, array(), array($message));
            } catch (\Exception $exception) {
                Mage::helper('M2ePro/Module_Exception')->process($exception);
                $action->deleteInstance();
            }
        }
    }

    //####################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing[]
     */
    protected function getActionsForExecute()
    {
        /** @var Ess_M2ePro_Model_Resource_Ebay_Listing_Product_Action_Processing_Collection $actionCollection */
        $actionCollection = Mage::getResourceModel('M2ePro/Ebay_Listing_Product_Action_Processing_Collection');
        $limit = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/scheduled_actions/', 'max_prepared_actions_count'
        );
        $actionCollection->getSelect()->order('id ASC')->limit($limit);

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $statement = $connRead->query($actionCollection->getSelect());

        $actions = array();

        while (($actionData = $statement->fetch()) !== false) {
            $action = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Processing');
            $action->setData($actionData);

            if ($this->isActionCanBeAdded($action, $actions)) {
                $actions[] = $action;
            }

            if ($this->isActionsSetFull($actions)) {
                break;
            }
        }

        return $actions;
    }

    //-----------------------------------------

    /**
     * @param Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing[] $actions
     */
    protected function executeSerial(array $actions)
    {
        /** @var Ess_M2ePro_Model_Ebay_Connector_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $percentsForOneAction = 100 / count($actions);

        foreach ($actions as $iteration => $action) {
            $command = $this->getCommand($action);

            /** @var Ess_M2ePro_Model_Connector_Command_RealTime_Virtual $connector */
            $connector = $dispatcher->getVirtualConnector(
                $command[0], $command[1], $command[2],
                $action->getRequestData(), null,
                $action->getListingProduct()->getMarketplace()->getId(),
                $action->getListingProduct()->getAccount()->getId(),
                $action->getRequestTimeOut()
            );

            try {
                $dispatcher->process($connector);
            } catch (Exception $exception) {
                Mage::helper('M2ePro/Module_Exception')->process($exception);

                $currentDate              = Mage::helper('M2ePro')->getCurrentGmtDate();
                $firstConnectionErrorDate = $this->getFirstConnectionErrorDate();

                if (empty($firstConnectionErrorDate)) {
                    $this->setFirstConnectionErrorDate($currentDate);
                    continue;
                }

                if (strtotime($currentDate) - strtotime($firstConnectionErrorDate)
                        < self::CONNECTION_ERROR_REPEAT_TIMEOUT) {
                    return;
                }

                if (!empty($firstConnectionErrorDate)) {
                    $this->removeFirstConnectionErrorDate();
                }

                $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
                $message->initFromException($exception);

                $this->completeAction($action, array(), array($message->asArray()));

                continue;
            }

            $this->completeAction(
                $action,
                $connector->getResponseData(), $connector->getResponseMessages(),
                $connector->getRequestTime()
            );

            if ($iteration % 10 == 0) {
                Mage::dispatchEvent(
                    Ess_M2ePro_Model_Cron_Strategy_Abstract::PROGRESS_SET_DETAILS_EVENT_NAME,
                    array(
                        'progress_nick' => Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Product_ProcessActions::NICK,
                        'percentage'    => ceil($percentsForOneAction * $iteration),
                        'total'         => count($actions)
                    )
                );
            }
        }
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing[] $actions
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function executeParallel(array $actions)
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processor_Connector_Multiple_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Processor_Connector_Multiple_Dispatcher');

        $groups = $this->groupForParallelExecution($actions, true);

        $processedActions = 0;
        $percentsForOneAction = 100 / count($actions);

        foreach ($groups as $actionsPacks) {
            foreach ($actionsPacks as $actionsPack) {
                /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing[] $actionsPack */

                $connectors = array();

                foreach ($actionsPack as $action) {
                    try {
                        $command = $this->getCommand($action);

                        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
                        $ebayListingProduct = $action->getListingProduct()->getChildObject();

                        $connectors[$action->getId()] = $dispatcher->getCustomVirtualConnector(
                            'Ebay_Listing_Product_Action_Processor_Connector_Multiple_Command_VirtualWithoutCall',
                            $command[0], $command[1], $command[2],
                            $action->getRequestData(), null,
                            $ebayListingProduct->getListing()->getMarketplaceId(),
                            $ebayListingProduct->getListing()->getAccountId(),
                            $action->getRequestTimeOut()
                        );

                        $processedActions++;
                    } catch (\Exception $exception) {
                        Mage::helper('M2ePro/Module_Exception')->process($exception);
                        $action->deleteInstance();
                    }
                }

                if (empty($connectors)) {
                    continue;
                }

                $dispatcher->processMultiple($connectors, true);

                if ($processedActions % 10 == 0) {
                    Mage::dispatchEvent(
                        Ess_M2ePro_Model_Cron_Strategy_Abstract::PROGRESS_SET_DETAILS_EVENT_NAME,
                        array(
                            'progress_nick' => Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Product_ProcessActions::NICK,
                            'percentage'    => ceil($percentsForOneAction * $processedActions),
                            'total'         => count($actions)
                        )
                    );
                }

                $systemErrorsMessages = array();
                $isServerInMaintenanceMode = null;

                foreach ($connectors as $actionId => $connector) {
                    foreach ($actionsPack as $action) {
                        if ($action->getId() != $actionId) {
                            continue;
                        }

                        $response = $connector->getResponse();

                        if ($response->getMessages()->hasSystemErrorEntity()) {
                            $systemErrorsMessages[] = $response->getMessages()->getCombinedSystemErrorsString();

                            if ($isServerInMaintenanceMode === null && $response->isServerInMaintenanceMode()) {
                                $isServerInMaintenanceMode = true;
                            }

                            continue;
                        }

                        $this->completeAction(
                            $action,
                            $connector->getResponseData(), $connector->getResponseMessages(),
                            $connector->getRequestTime()
                        );

                        break;
                    }
                }

                if (!empty($systemErrorsMessages)) {
                    throw new Ess_M2ePro_Model_Exception(
                        Mage::helper('M2ePro')->__(
                            "Internal Server Error(s) [%error_message%]",
                            $this->getCombinedErrorMessage($systemErrorsMessages)
                        ), array(), 0, !$isServerInMaintenanceMode
                    );
                }
            }
        }
    }

    //-----------------------------------------

    protected function getCombinedErrorMessage(array $systemErrorsMessages)
    {
        $combinedErrorMessages = array();
        foreach ($systemErrorsMessages as $systemErrorMessage) {
            $key = sha1($systemErrorMessage);

            if (isset($combinedErrorMessages[$key])) {
                $combinedErrorMessages[$key]["count"] += 1;
                continue;
            }

            $combinedErrorMessages[$key] = array(
                "message" => $systemErrorMessage,
                "count" => 1
            );
        }

        $message = "";
        foreach ($combinedErrorMessages as $combinedErrorMessage) {
            $message .= sprintf(
                "%s (%s)<br>",
                $combinedErrorMessage["message"],
                $combinedErrorMessage["count"]
            );
        }

        return $message;
    }

    //####################################

    /**
     * @param Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing $action
     * @param Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing[] $actions
     * @return bool
     */
    protected function isActionCanBeAdded(
        Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing $action,
        array $actions
    ) {
        if ($this->calculateParallelExecutionTime($actions) < self::MAX_TOTAL_EXECUTION_TIME) {
            return true;
        }

        $groupedActions = $this->groupForParallelExecution($actions, false);
        $commandRequestTime = $this->getCommandRequestTime($this->getCommand($action));

        if (empty($groupedActions[$commandRequestTime])) {
            return false;
        }

        foreach ($groupedActions[$commandRequestTime] as $actionsGroup) {
            if (count($actionsGroup) < self::MAX_PARALLEL_EXECUTION_PACK_SIZE) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing[] $actions
     * @return bool
     */
    protected function isActionsSetFull(array $actions)
    {
        if ($this->calculateParallelExecutionTime($actions) < self::MAX_TOTAL_EXECUTION_TIME) {
            return false;
        }

        foreach ($this->groupForParallelExecution($actions, false) as $actionsGroups) {
            foreach ($actionsGroups as $actionsGroup) {
                if (count($actionsGroup) < self::MAX_PARALLEL_EXECUTION_PACK_SIZE) {
                    return false;
                }
            }
        }

        return true;
    }

    //-----------------------------------------

    /**
     * @param Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing[] $actions
     * @return int
     */
    protected function calculateSerialExecutionTime(array $actions)
    {
        $totalTime = 0;

        foreach ($actions as $action) {
            $commandRequestTime = $this->getCommandRequestTime($this->getCommand($action));
            $totalTime += $commandRequestTime + self::ONE_SERVER_CALL_INCREASE_TIME;
        }

        return $totalTime;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing[] $actions
     * @return int
     */
    protected function calculateParallelExecutionTime(array $actions)
    {
        $totalTime = 0;

        foreach ($this->groupForParallelExecution($actions, false) as $commandRequestTime => $actionsPacks) {
            $actionsPacksCount = count($actionsPacks);
            $totalTime += $actionsPacksCount * ($commandRequestTime + self::ONE_SERVER_CALL_INCREASE_TIME);
        }

        return $totalTime;
    }

    //-----------------------------------------

    /**
     * @param Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing[] $actions
     * @param bool $needDistribute
     * @return array
     */
    protected function groupForParallelExecution(array $actions, $needDistribute = false)
    {
        $groupedByTimeActions = array();

        foreach ($actions as $action) {
            $commandRequestTime = $this->getCommandRequestTime($this->getCommand($action));
            $groupedByTimeActions[$commandRequestTime][] = $action;
        }

        $resultGroupedActions = array();

        $totalSerialExecutionTime = $this->calculateSerialExecutionTime($actions);

        foreach ($groupedByTimeActions as $commandRequestTime => $groupActions) {
            $packSize = self::MAX_PARALLEL_EXECUTION_PACK_SIZE;

            if ($needDistribute) {
                $groupSerialExecutionTime  = $this->calculateSerialExecutionTime($groupActions);
                $groupAllowedExecutionTime = (int)(
                    self::MAX_TOTAL_EXECUTION_TIME * $groupSerialExecutionTime / $totalSerialExecutionTime
                );
                if ($groupAllowedExecutionTime < $commandRequestTime) {
                    $groupAllowedExecutionTime = $commandRequestTime;
                }

                $packsCount = ceil(
                    $groupAllowedExecutionTime / ($commandRequestTime + self::ONE_SERVER_CALL_INCREASE_TIME)
                );
                $packSize   = ceil(count($groupActions) / $packsCount);
            }

            $resultGroupedActions[$commandRequestTime] = array_chunk($groupActions, $packSize);
        }

        return $resultGroupedActions;
    }

    //####################################

    protected function getFirstConnectionErrorDate()
    {
        $registry = Mage::getModel('M2ePro/Registry');
        $registry->load(self::FIRST_CONNECTION_ERROR_DATE_REGISTRY_KEY, 'key');

        return $registry->getValue();
    }

    protected function setFirstConnectionErrorDate($date)
    {
        $registry = Mage::getModel('M2ePro/Registry');
        $registry->load(self::FIRST_CONNECTION_ERROR_DATE_REGISTRY_KEY, 'key');

        $registry->setData('key', self::FIRST_CONNECTION_ERROR_DATE_REGISTRY_KEY);
        $registry->setData('value', $date);

        $registry->save();
    }

    protected function removeFirstConnectionErrorDate()
    {
        $registry = Mage::getModel('M2ePro/Registry');
        $registry->load(self::FIRST_CONNECTION_ERROR_DATE_REGISTRY_KEY, 'key');

        if ($registry->getId()) {
            $registry->delete();
        }
    }

    //####################################

    protected function getCommand(Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing $action)
    {
        switch ($action->getType()) {
            case Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing::TYPE_LIST:
                return array('item', 'add', 'single');

            case Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing::TYPE_RELIST:
                return array('item', 'update', 'relist');

            case Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing::TYPE_REVISE:
                return array('item', 'update', 'revise');

            case Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing::TYPE_STOP:
                return array('item', 'update', 'end');

            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown action type.');
        }
    }

    protected function getCommandRequestTime($command)
    {
        switch ($command) {
            case array('item', 'add', 'single'):
            case array('item', 'update', 'relist'):
                return 3;

            case array('item', 'update', 'revise'):
                return 4;

            case array('item', 'update', 'end'):
                return 1;

            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown command.');
        }
    }

    //-----------------------------------------

    protected function completeAction(
        Ess_M2ePro_Model_Ebay_Listing_Product_Action_Processing $action,
        array $data,
        array $messages,
        $requestTime = null
    ) {
        $processing = $action->getProcessing();

        $processing->setSettings('result_data', $data);
        $processing->setSettings('result_messages', $messages);
        $processing->setData('is_completed', 1);

        if ($requestTime !== null) {
            $processingParams = $processing->getParams();
            $processingParams['request_time'] = $requestTime;
            $processing->setSettings('params', $processingParams);
        }

        $processing->save();

        $action->deleteInstance();
    }

    //####################################
}