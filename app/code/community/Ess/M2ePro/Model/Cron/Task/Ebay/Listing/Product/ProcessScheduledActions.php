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

    //####################################

    public function isPossibleToRun()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    //####################################

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
                Mage::helper('M2ePro/Module_Exception')->process($e, false);
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

    protected function getListScheduledActionsPreparedCollection()
    {
        $priorityCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/list/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/list/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_LIST);

        return $collection;
    }

    protected function getRelistScheduledActionsPreparedCollection()
    {
        $priorityCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/relist/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/relist/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_RELIST);

        return $collection;
    }

    protected function getReviseQtyScheduledActionsPreparedCollection()
    {
        $priorityCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_qty/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_qty/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);
        $collection->addFieldToFilter('main_table.tag', array('like' => '%/qty/%'));

        return $collection;
    }

    protected function getRevisePriceScheduledActionsPreparedCollection()
    {
        $priorityCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_price/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_price/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);
        $collection->addFieldToFilter('main_table.tag', array('like' => '%/price/%'));

        return $collection;
    }

    protected function getReviseTitleScheduledActionsPreparedCollection()
    {
        $priorityCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_title/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_title/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);
        $collection->addFieldToFilter('main_table.tag', array('like' => '%/title/%'));

        return $collection;
    }

    protected function getReviseSubtitleScheduledActionsPreparedCollection()
    {
        $priorityCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_subtitle/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_subtitle/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);
        $collection->addFieldToFilter('main_table.tag', array('like' => '%/subtitle/%'));

        return $collection;
    }

    protected function getReviseDescriptionScheduledActionsPreparedCollection()
    {
        $priorityCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_description/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_description/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);
        $collection->addFieldToFilter('main_table.tag', array('like' => '%/description/%'));

        return $collection;
    }

    protected function getReviseImagesScheduledActionsPreparedCollection()
    {
        $priorityCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_images/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_images/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);
        $collection->addFieldToFilter('main_table.tag', array('like' => '%/images/%'));

        return $collection;
    }

    protected function getReviseCategoriesScheduledActionsPreparedCollection()
    {
        $priorityCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_categories/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_categories/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);
        $collection->addFieldToFilter('main_table.tag', array('like' => '%/categories/%'));

        return $collection;
    }

    protected function getRevisePaymentScheduledActionsPreparedCollection()
    {
        $priorityCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_payment/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_payment/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);
        $collection->addFieldToFilter('main_table.tag', array('like' => '%/payment/%'));

        return $collection;
    }

    protected function getReviseShippingScheduledActionsPreparedCollection()
    {
        $priorityCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_shipping/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_shipping/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);
        $collection->addFieldToFilter('main_table.tag', array('like' => '%/shipping/%'));

        return $collection;
    }

    protected function getReviseReturnScheduledActionsPreparedCollection()
    {
        $priorityCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_return/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_return/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);
        $collection->addFieldToFilter('main_table.tag', array('like' => '%/return/%'));

        return $collection;
    }

    protected function getReviseOtherScheduledActionsPreparedCollection()
    {
        $priorityCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_other/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/revise_other/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);
        $collection->addFieldToFilter('main_table.tag', array('like' => '%/other/%'));

        return $collection;
    }

    protected function getStopScheduledActionsPreparedCollection()
    {
        $priorityCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/stop/', 'priority_coefficient'
        );
        $waitIncreaseCoefficient = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/listing/product/action/stop/', 'wait_increase_coefficient'
        );

        $collection = $this->getScheduledActionsPreparedCollection(
            $priorityCoefficient, $waitIncreaseCoefficient
        );
        $collection->addFieldToFilter('main_table.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_STOP);

        return $collection;
    }

    //####################################

    /**
     * @param $priorityCoefficient
     * @param $waitIncreaseCoefficient
     * @return Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
     */
    protected function getScheduledActionsPreparedCollection($priorityCoefficient, $waitIncreaseCoefficient)
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
            array('aa' => Mage::getResourceModel('M2ePro/Ebay_Account')->getMainTable()),
            'l.account_id = aa.account_id'
        );
        $collection->getSelect()->joinLeft(
            array('pl' => Mage::getResourceModel('M2ePro/Processing_Lock')->getMainTable()),
            'pl.object_id = main_table.listing_product_id AND model_name = \'M2ePro/Listing_Product\''
        );

        $collection->addFieldToFilter('component', Ess_M2ePro_Helper_Component_Ebay::NICK);
        $collection->addFieldToFilter('pl.id', array('null' => true));

        $now = Mage::helper('M2ePro')->getCurrentGmtDate();
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns(
                array(
                'id'                 => 'main_table.id',
                'listing_product_id' => 'main_table.listing_product_id',
                'account_id'         => 'aa.account_id',
                'action_type'        => 'main_table.action_type',
                'tag'                => new Zend_Db_Expr('NULL'),
                'additional_data'    => 'main_table.additional_data',
                'coefficient'        => new Zend_Db_Expr(
                    "{$priorityCoefficient} +
                    (time_to_sec(timediff('{$now}', main_table.create_date)) / 3600) * {$waitIncreaseCoefficient}"
                ),
                'create_date'        => 'main_table.create_date',
                )
            );

        return $collection;
    }

    //####################################
}
