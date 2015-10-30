<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Synchronization_Task_Defaults_StopQueue
    extends Ess_M2ePro_Model_Synchronization_Task_Defaults_Abstract
{
    private $itemsWereProcessed = false;

    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/stop_queue/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Stopping Products';
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 50;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 60;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    protected function intervalIsEnabled()
    {
        return true;
    }

    //########################################

    protected function performActions()
    {
        foreach (Mage::helper('M2ePro/Component')->getComponents() as $component) {
            $tempFlag = $this->sendComponentRequests($component);
            $tempFlag && $this->itemsWereProcessed = true;
        }
    }

    //########################################

    private function sendComponentRequests($component)
    {
        $items = Mage::getModel('M2ePro/StopQueue')->getCollection()
                    ->addFieldToFilter('is_processed',0)
                    ->addFieldToFilter('component_mode',$component)
                    ->getItems();

        $accountMarketplaceItems = array();

        foreach ($items as $item) {

            /** @var Ess_M2ePro_Model_StopQueue $item */
            $tempKey = (string)$item->getMarketplaceId().'_'.$item->getAccountHash();

            if (!isset($accountMarketplaceItems[$tempKey])) {
                $accountMarketplaceItems[$tempKey] = array();
            }

            if (count($accountMarketplaceItems[$tempKey]) >= 100) {
                continue;
            }

            $accountMarketplaceItems[$tempKey][] = $item;
        }

        foreach ($accountMarketplaceItems as $items) {

            if ($component == Ess_M2ePro_Helper_Component_Ebay::NICK) {

                $parts = array_chunk($items,10);

                foreach ($parts as $part) {
                    if (count($part) <= 0) {
                        continue;
                    }
                    $this->sendAccountMarketplaceRequests($component,$part);
                }

            } else {
                $this->sendAccountMarketplaceRequests($component,$items);
            }

            foreach ($items as $item) {
                /** @var Ess_M2ePro_Model_StopQueue $item */
                $item->setData('is_processed',1)->save();
            }
        }

        return count($accountMarketplaceItems) > 0;
    }

    private function sendAccountMarketplaceRequests($component, $accountMarketplaceItems)
    {
        try {

            $requestData = array(
                'items' => array(),
            );

            /** @var Ess_M2ePro_Model_StopQueue $tempItem */
            $tempItem = $accountMarketplaceItems[0];
            $requestData['account'] = $tempItem->getAccountHash();
            if (!is_null($tempItem->getMarketplaceId())) {
                $requestData['marketplace'] = $tempItem->getMarketplaceId();
            }

            foreach ($accountMarketplaceItems as $item) {
                /** @var Ess_M2ePro_Model_StopQueue $item */
                $tempIndex = count($requestData['items']);
                $component == Ess_M2ePro_Helper_Component_Ebay::NICK && $tempIndex+=100;
                $requestData['items'][$tempIndex] = $item->getDecodedItemData();
            }

            if ($component == Ess_M2ePro_Helper_Component_Ebay::NICK) {
                $entity = 'item';
                $type = 'update';
                $name = 'ends';
            } else {
                $entity = 'product';
                $type = 'update';
                $name = 'entities';
            }

            $dispatcher = Mage::getModel('M2ePro/Connector_'.ucwords($component).'_Dispatcher');
            $connectorObj = $dispatcher->getVirtualConnector($entity, $type, $name, $requestData);
            $dispatcher->process($connectorObj);

        } catch (Exception $exception) {}
    }

    //########################################

    protected function intervalSetLastTime($time)
    {
        if ($this->itemsWereProcessed) {
            return;
        }

        parent::intervalSetLastTime($time);
    }

    //########################################
}