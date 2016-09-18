<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Templates_Relist
    extends Ess_M2ePro_Model_Ebay_Synchronization_Templates_Abstract
{
    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/relist/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Relist';
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 20;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 35;
    }

    //########################################

    protected function performActions()
    {
        $this->immediatelyChangedProducts();
        $this->executeScheduled();
    }

    //########################################

    private function immediatelyChangedProducts()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Immediately when Product was changed');

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        foreach ($changedListingsProducts as $listingProduct) {

            $action = $this->getAction($listingProduct);

            /** @var $configurator Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator */
            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');

            $this->prepareConfigurator($listingProduct, $configurator, $action);

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct, $action, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetRelistRequirements($listingProduct)) {
                continue;
            }

            /**
             * @var $synchronizationTemplate Ess_M2ePro_Model_Ebay_Template_Synchronization
             */
            $synchronizationTemplate = $listingProduct->getChildObject()->getEbaySynchronizationTemplate();
            if ($synchronizationTemplate->isScheduleEnabled() &&
                (!$synchronizationTemplate->isScheduleIntervalNow() ||
                 !$synchronizationTemplate->isScheduleWeekNow())
            ) {
                $additionalData = $listingProduct->getAdditionalData();

                if (!isset($additionalData['add_to_schedule'])) {
                    $additionalData['add_to_schedule'] = true;
                    $listingProduct->setSettings('additional_data', $additionalData)->save();
                }

                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct, $action, $configurator
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //########################################

    private function executeScheduled()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Execute scheduled');

        /** @var Ess_M2ePro_Model_Template_Synchronization $synchTemplateCollection */
        $synchTemplateCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Template_Synchronization');

        foreach ($synchTemplateCollection as $synchTemplate) {

            /* @var $ebaySynchTemplate Ess_M2ePro_Model_Ebay_Template_Synchronization */
            $ebaySynchTemplate = $synchTemplate->getChildObject();

            if (!$ebaySynchTemplate->isScheduleEnabled()) {
                continue;
            }

            if (!$ebaySynchTemplate->isScheduleIntervalNow() ||
                !$ebaySynchTemplate->isScheduleWeekNow()) {
                continue;
            }

            $listingsProducts = array();
            $affectedListingsProducts = NULL;

            do {

                $tempListingsProducts = $this->getNextScheduledListingsProducts($synchTemplate->getId());

                if (count($tempListingsProducts) <= 0) {
                    break;
                }

                if (is_null($affectedListingsProducts)) {
                    $affectedListingsProducts = $ebaySynchTemplate->getAffectedListingsProducts(true);
                }

                if (count($affectedListingsProducts) <= 0) {
                    break;
                }

                foreach ($tempListingsProducts as $tempListingProduct) {

                    $found = false;
                    foreach ($affectedListingsProducts as $affectedListingProduct) {
                        if ((int)$tempListingProduct->getId() == $affectedListingProduct['id']) {
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        continue;
                    }

                    $listingsProducts[] = $tempListingProduct;
                }

            } while (count($listingsProducts) < 100);

            foreach ($listingsProducts as $listingProduct) {

                /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
                $listingProduct->getMagentoProduct()->enableCache();

                $action = $this->getAction($listingProduct);

                /** @var $configurator Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator */
                $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');

                $this->prepareConfigurator($listingProduct, $configurator, $action);

                $isExistInRunner = $this->getRunner()->isExistProduct(
                    $listingProduct, $action, $configurator
                );

                if ($isExistInRunner) {
                    continue;
                }

                if (!$this->getInspector()->isMeetRelistRequirements($listingProduct)) {
                    continue;
                }

                $this->getRunner()->addProduct(
                    $listingProduct, $action, $configurator
                );
            }
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function getNextScheduledListingsProducts($synchTemplateId)
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $cacheConfigGroup = '/ebay/template/synchronization/'.$synchTemplateId.'/schedule/relist/';

        $yearMonthDay = Mage::helper('M2ePro')->getCurrentGmtDate(false,'Y-m-d');
        $configData = $cacheConfig->getGroupValue($cacheConfigGroup,'last_listing_product_id');

        if (is_null($configData)) {
            $configData = array();
        } else {
            $configData = json_decode($configData,true);
        }

        $lastListingProductId = 0;
        if (isset($configData[$yearMonthDay])) {
            $lastListingProductId = (int)$configData[$yearMonthDay];
        }

        /** @var Mage_Core_Model_Mysql4_Collection_Abstract $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('main_table.id',array('gt'=>$lastListingProductId));
        $collection->addFieldToFilter('main_table.status',
                    array('neq'=>Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED));
        $collection->addFieldToFilter('main_table.status',
                    array('neq'=>Ess_M2ePro_Model_Listing_Product::STATUS_LISTED));
        $collection->addFieldToFilter('main_table.additional_data',
                    array('like'=>'%"add_to_schedule":true%'));
        $collection->getSelect()->order('main_table.id ASC');
        $collection->getSelect()->limit(100);

        $lastItem = $collection->getLastItem();
        if (!$lastItem->getId()) {
            return array();
        }

        $configData = array($yearMonthDay=>$lastItem->getId());
        $cacheConfig->setGroupValue($cacheConfigGroup,'last_listing_product_id',json_encode($configData));

        return $collection->getItems();
    }

    //########################################

    private function getAction(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if ($listingProduct->isHidden()) {
            return Ess_M2ePro_Model_Listing_Product::ACTION_REVISE;
        }

        return Ess_M2ePro_Model_Listing_Product::ACTION_RELIST;
    }

    private function prepareConfigurator(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                         Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator $configurator,
                                         $action)
    {
        if ($action != Ess_M2ePro_Model_Listing_Product::ACTION_RELIST) {
            $configurator->setParams(array('replaced_action' => Ess_M2ePro_Model_Listing_Product::ACTION_RELIST));
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        if (!$ebayListingProduct->getEbaySynchronizationTemplate()->isRelistSendData()) {
            $configurator->setPartialMode();
            $configurator->allowQty()->allowPrice()->allowVariations();
        }
    }

    //########################################
}