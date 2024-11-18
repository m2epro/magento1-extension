<?php


class Ess_M2ePro_Model_Amazon_Template_ProductType_ChangeProcessor
{
    const INSTRUCTION_INITIATOR = 'template_product_type_change_processor';
    const INSTRUCTION_TYPE_DETAILS_DATA_CHANGED = 'template_details_data_changed';

    /**
     * @return string
     */
    protected function getInstructionInitiator()
    {
        return self::INSTRUCTION_INITIATOR;
    }

    /**
     * @param Ess_M2ePro_Model_Amazon_Template_ProductType_Diff $diff
     * @param $status
     *
     * @return array
     */
    protected function getInstructionsData(Ess_M2ePro_Model_ActiveRecord_Diff $diff, $status)
    {
        $data[] = array(
            'type'     => self::INSTRUCTION_TYPE_DETAILS_DATA_CHANGED,
            'priority' => 50,
        );

        return $data;
    }

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
                    'type' => $instructionData['type'],
                    'initiator' => $this->getInstructionInitiator(),
                    'priority' => $instructionData['priority'],
                );
            }
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Instruction $listingProductInstructionResource */
        $listingProductInstructionResource = Mage::getResourceModel(
            'M2ePro/Listing_Product_Instruction'
        );

        $listingProductInstructionResource->add($listingsProductsInstructionsData);
    }
}