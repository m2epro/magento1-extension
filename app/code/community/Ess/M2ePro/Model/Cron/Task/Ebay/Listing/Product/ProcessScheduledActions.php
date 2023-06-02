<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Product_ProcessScheduledActions
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'ebay/listing/product/process_scheduled_actions';

    const LIST_PRIORITY               = 25;
    const RELIST_PRIORITY             = 125;
    const STOP_PRIORITY               = 1000;
    const REVISE_QTY_PRIORITY         = 500;
    const REVISE_PRICE_PRIORITY       = 250;
    const REVISE_TITLE_PRIORITY       = 50;
    const REVISE_SUBTITLE_PRIORITY    = 50;
    const REVISE_DESCRIPTION_PRIORITY = 50;
    const REVISE_IMAGES_PRIORITY      = 50;
    const REVISE_CATEGORIES_PRIORITY  = 50;
    const REVISE_PARTS_PRIORITY       = 50;
    const REVISE_PAYMENT_PRIORITY     = 50;
    const REVISE_SHIPPING_PRIORITY    = 50;
    const REVISE_RETURN_PRIORITY      = 50;
    const REVISE_OTHER_PRIORITY       = 50;


    //####################################

    /**
     * @return bool
     */
    public function isPossibleToRun()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    //####################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     * @throws Zend_Db_Select_Exception
     */
    protected function performActions()
    {
        $limit = $this->calculateActionsCountLimit();
        if ($limit <= 0) {
            return;
        }

        $scheduledActions = $this->getScheduledActionsForProcessing($limit);
        if (empty($scheduledActions)) {
            return;
        }

        $iteration = 0;
        $percentsForOneAction = 100 / count($scheduledActions);

        foreach ($scheduledActions as $scheduledAction) {
            try {
                $listingProduct = $scheduledAction->getListingProduct();
                $additionalData = $scheduledAction->getAdditionalData();
            } catch (\Ess_M2ePro_Model_Exception_Logic $e) {
                Mage::helper('M2ePro/Module_Exception')->process($e);
                $scheduledAction->delete();

                continue;
            }

            $params = array();
            if (!empty($additionalData['params'])) {
                $params = $additionalData['params'];
            }

            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
            if (!empty($additionalData['configurator'])) {
                $configurator->setData($additionalData['configurator']);
                $configurator->setParams($params);
            }

            $listingProduct->setActionConfigurator($configurator);

            /** @var Ess_M2ePro_Model_Ebay_Connector_Item_Dispatcher $dispatcher */
            $dispatcher = Mage::getModel('M2ePro/Ebay_Connector_Item_Dispatcher');
            $dispatcher->process($scheduledAction->getActionType(), array($listingProduct), $params);

            $scheduledAction->delete();

            if ($iteration % 10 == 0) {
                Mage::dispatchEvent(
                    Ess_M2ePro_Model_Cron_Strategy_Abstract::PROGRESS_SET_DETAILS_EVENT_NAME,
                    array(
                        'progress_nick' => self::NICK,
                        'percentage'    => ceil($percentsForOneAction * $iteration),
                        'total'         => count($scheduledActions)
                    )
                );
            }

            $iteration++;
        }
    }

    //####################################

    /**
     * @return int
     */
    protected function calculateActionsCountLimit()
    {
        $maxAllowedActionsCount = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/scheduled_actions/', 'max_prepared_actions_count'
        );

        if ($maxAllowedActionsCount <= 0) {
            return 0;
        }

        $currentActionsCount = Mage::getResourceModel('M2ePro/Ebay_Listing_Product_Action_Processing_Collection')
            ->getSize();

        if ($currentActionsCount > $maxAllowedActionsCount) {
            return 0;
        }

        return $maxAllowedActionsCount - $currentActionsCount;
    }

    /**
     * @param $limit
     * @return Ess_M2ePro_Model_Listing_Product_ScheduledAction[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     * @throws Zend_Db_Select_Exception
     */
    protected function getScheduledActionsForProcessing($limit)
    {
        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $connRead = $resource->getConnection('core_read');

        $unionSelect = $connRead->select()->union(
            array(
                $this->getListScheduledActionsPreparedCollection()->getSelect(),
                $this->getRelistScheduledActionsPreparedCollection()->getSelect(),
                $this->getReviseQtyScheduledActionsPreparedCollection()->getSelect(),
                $this->getRevisePriceScheduledActionsPreparedCollection()->getSelect(),
                $this->getReviseTitleScheduledActionsPreparedCollection()->getSelect(),
                $this->getReviseSubtitleScheduledActionsPreparedCollection()->getSelect(),
                $this->getReviseDescriptionScheduledActionsPreparedCollection()->getSelect(),
                $this->getReviseImagesScheduledActionsPreparedCollection()->getSelect(),
                $this->getReviseCategoriesScheduledActionsPreparedCollection()->getSelect(),
                $this->getRevisePartsScheduledActionsPreparedCollection()->getSelect(),
                $this->getRevisePaymentScheduledActionsPreparedCollection()->getSelect(),
                $this->getReviseShippingScheduledActionsPreparedCollection()->getSelect(),
                $this->getReviseReturnScheduledActionsPreparedCollection()->getSelect(),
                $this->getReviseOtherScheduledActionsPreparedCollection()->getSelect(),
                $this->getStopScheduledActionsPreparedCollection()->getSelect(),
            )
        );

        $unionSelect->order(array('coefficient DESC'));
        $unionSelect->order(array('create_date ASC'));

        $unionSelect->distinct(true);
        $unionSelect->limit($limit);

        $scheduledActionsData = $unionSelect->query()->fetchAll();
        if (empty($scheduledActionsData)) {
            return array();
        }

        $scheduledActionsIds = array();
        foreach ($scheduledActionsData as $scheduledActionData) {
            $scheduledActionsIds[] = $scheduledActionData['id'];
        }

        $scheduledActionsCollection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $scheduledActionsCollection->addFieldToFilter('id', array_unique($scheduledActionsIds));

        return $scheduledActionsCollection->getItems();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getListScheduledActionsPreparedCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');

        return $collection->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->getScheduledActionsPreparedCollection(
                self::LIST_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_LIST
            );
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getRelistScheduledActionsPreparedCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');

        return $collection->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->getScheduledActionsPreparedCollection(
                self::RELIST_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_RELIST
            );
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getReviseQtyScheduledActionsPreparedCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');

        return $collection->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_QTY_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addTagFilter('qty');
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getRevisePriceScheduledActionsPreparedCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');

        return $collection->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_PRICE_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addTagFilter('price');
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getReviseTitleScheduledActionsPreparedCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');

        return $collection->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_TITLE_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addTagFilter('title');
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getReviseSubtitleScheduledActionsPreparedCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');

        return $collection->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_SUBTITLE_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addTagFilter('subtitle');
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getReviseDescriptionScheduledActionsPreparedCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');

        return $collection->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_DESCRIPTION_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addTagFilter('description');
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getReviseImagesScheduledActionsPreparedCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');

        return $collection->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_IMAGES_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addTagFilter('images');
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getReviseCategoriesScheduledActionsPreparedCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');

        return $collection->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_CATEGORIES_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addTagFilter('categories');
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getRevisePartsScheduledActionsPreparedCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');

        return $collection->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_PARTS_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addTagFilter('parts');
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getRevisePaymentScheduledActionsPreparedCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');

        return $collection->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_PAYMENT_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addTagFilter('payment');
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getReviseShippingScheduledActionsPreparedCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');

        return $collection->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_SHIPPING_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addTagFilter('shipping');
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getReviseReturnScheduledActionsPreparedCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');

        return $collection->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_RETURN_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addTagFilter('return');
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getReviseOtherScheduledActionsPreparedCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');

        return $collection->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->getScheduledActionsPreparedCollection(
                self::REVISE_OTHER_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_REVISE
            )
            ->addTagFilter('other');
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getStopScheduledActionsPreparedCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');

        return $collection->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->getScheduledActionsPreparedCollection(
                self::STOP_PRIORITY,
                Ess_M2ePro_Model_Listing_Product::ACTION_STOP
            );
    }

    //####################################
}
