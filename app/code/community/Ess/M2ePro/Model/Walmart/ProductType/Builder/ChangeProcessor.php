<?php

class Ess_M2ePro_Model_Walmart_ProductType_Builder_ChangeProcessor
    extends Ess_M2ePro_Model_Walmart_Template_ChangeProcessor_Abstract
{
    const INSTRUCTION_INITIATOR = 'product_type_change_processor';

    protected function getInstructionInitiator()
    {
        return self::INSTRUCTION_INITIATOR;
    }

    protected function getInstructionsData(Ess_M2ePro_Model_ActiveRecord_Diff $diff, $status)
    {
        $data[] = array(
            'type' => self::INSTRUCTION_TYPE_DETAILS_DATA_CHANGED,
            'priority' => 50,
        );

        return $data;
    }
}
