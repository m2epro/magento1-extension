<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Stop
    extends Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Abstract
{
    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/stop/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Stop';
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 70;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 75;
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

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingOther, Ess_M2ePro_Model_Listing_Product::ACTION_STOP, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetStopRequirements($listingOther)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingOther, Ess_M2ePro_Model_Listing_Product::ACTION_STOP, $configurator
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //########################################
}