<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Template_ChangeProcessorAbstract
{
    //########################################

    public function process(Ess_M2ePro_Model_ActiveRecord_Diff $diff, array $affectedListingsProductsData)
    {
        if (empty($affectedListingsProductsData)) {
            return;
        }

        if (!$diff->isDifferent()) {
            return;
        }

        $listingsProductsInstructionsData = array();

        $statusInstructionCache = array();

        foreach ($affectedListingsProductsData as $affectedListingProductData) {
            $status = $affectedListingProductData['status'];

            if (isset($statusInstructionCache[$status])) {
                $instructionsData = $statusInstructionCache[$status];
            } else {
                $instructionsData = $this->getInstructionsData($diff, $status);
            }

            foreach ($instructionsData as $instructionData) {
                $listingsProductsInstructionsData[] = array(
                    'listing_product_id' => $affectedListingProductData['id'],
                    'type'               => $instructionData['type'],
                    'initiator'          => $this->getInstructionInitiator(),
                    'priority'           => $instructionData['priority'],
                );
            }
        }

        Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add($listingsProductsInstructionsData);
    }

    //########################################

    abstract protected function getInstructionInitiator();

    // ---------------------------------------

    abstract protected function getInstructionsData(Ess_M2ePro_Model_ActiveRecord_Diff $diff, $status);

    //########################################
}
