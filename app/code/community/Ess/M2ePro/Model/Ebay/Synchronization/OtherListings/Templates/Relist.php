<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Relist
    extends Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Abstract
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
        return 60;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 70;
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

        $changedListingsOthers = $this->getChangedInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {

            /** @var $configurator Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator */
            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_Configurator');

            /** @var Ess_M2ePro_Model_Ebay_Listing_Other $ebayListingOther */
            $ebayListingOther = $listingOther->getChildObject();

            if (!$ebayListingOther->getSynchronizationModel()->isRelistSendData()) {
                $configurator->setPartialMode();
                $configurator->allowQty()->allowPrice();
            }

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingOther, Ess_M2ePro_Model_Listing_Product::ACTION_RELIST, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetRelistRequirements($listingOther)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingOther, Ess_M2ePro_Model_Listing_Product::ACTION_RELIST, $configurator
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //########################################
}