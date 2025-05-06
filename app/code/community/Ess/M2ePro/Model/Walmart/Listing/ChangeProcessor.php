<?php

class Ess_M2ePro_Model_Walmart_Listing_ChangeProcessor
    extends Ess_M2ePro_Model_Amazon_Template_ChangeProcessor_Abstract
{
    const INSTRUCTION_TYPE_CONDITION_DATA_CHANGED = 'listing_condition_data_changed';
    const INSTRUCTION_INITIATOR = 'listing_change_processor';

    protected function getInstructionInitiator()
    {
        return self::INSTRUCTION_INITIATOR;
    }

    /**
     * @param Ess_M2ePro_Model_Walmart_Listing_Diff $diff
     * @param int|string $status
     *
     * @return array
     */
    protected function getInstructionsData(Ess_M2ePro_Model_ActiveRecord_Diff $diff, $status)
    {
        $data = array();

        if ($diff->isConditionDifferent()) {
            $priority = $status == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED
                ? 10
                : 5;

            $data[] = array(
                Ess_M2ePro_Model_Resource_Listing_Product_Instruction::COLUMN_PRIORITY => $priority,
                Ess_M2ePro_Model_Resource_Listing_Product_Instruction::COLUMN_TYPE
                => self::INSTRUCTION_TYPE_CONDITION_DATA_CHANGED,
            );
        }

        return $data;
    }
}
