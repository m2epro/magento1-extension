<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Templates_List
    extends Ess_M2ePro_Model_Amazon_Synchronization_Templates_Abstract
{
    //########################################

    protected function getNick()
    {
        return '/list/';
    }

    protected function getTitle()
    {
        return 'List';
    }

    // ---------------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 5;
    }

    //########################################

    protected function performActions()
    {
        $this->immediatelyChangedProducts();
        $this->immediatelyNotCheckedProducts();
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

            /** @var $configurator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator */
            $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_LIST, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetListRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_LIST, $configurator
            );

            $this->setListAttemptData($listingProduct);
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function immediatelyNotCheckedProducts()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Immediately when Product was not checked');

        /** @var $collection Ess_M2ePro_Model_Mysql4_Listing_Product_Collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);
        $collection->addFieldToFilter('tried_to_list',0);

        $collection->getSelect()->limit(100);

        $listingsProducts = $collection->getItems();

        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $listingProduct->getMagentoProduct()->enableCache();
            $listingProduct->setData('tried_to_list',1)->save();

            /** @var $configurator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator */
            $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_LIST, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetListRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_LIST, $configurator
            );

            $this->setListAttemptData($listingProduct);
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //########################################

    private function setListAttemptData(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $additionalData = $listingProduct->getAdditionalData();
        $additionalData['last_list_attempt_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
        $listingProduct->setSettings('additional_data', $additionalData);

        $listingProduct->save();
    }

    //########################################
}