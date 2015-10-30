<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Templates_Relist
    extends Ess_M2ePro_Model_Amazon_Synchronization_Templates_Abstract
{
    //########################################

    protected function getNick()
    {
        return '/relist/';
    }

    protected function getTitle()
    {
        return 'Relist';
    }

    // ---------------------------------------

    protected function getPercentsStart()
    {
        return 20;
    }

    protected function getPercentsEnd()
    {
        return 35;
    }

    //########################################

    protected function performActions()
    {
        $this->immediatelyChangedProducts();
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

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            $amazonSynchronizationTemplate = $amazonListingProduct->getAmazonSynchronizationTemplate();

            /** @var $configurator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator */
            $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');

            if (!$amazonSynchronizationTemplate->isRelistSendData()) {
                $configurator->setPartialMode();
                $configurator->allowQty();
            }

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_RELIST, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetRelistRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_RELIST, $configurator
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //########################################
}