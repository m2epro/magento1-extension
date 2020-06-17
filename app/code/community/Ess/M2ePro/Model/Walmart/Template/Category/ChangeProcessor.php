<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_Category_ChangeProcessor
    extends Ess_M2ePro_Model_Walmart_Template_ChangeProcessor_Abstract
{
    const INSTRUCTION_INITIATOR = 'template_category_change_processor';

    //########################################

    protected function getInstructionInitiator()
    {
        return self::INSTRUCTION_INITIATOR;
    }

    // ---------------------------------------

    protected function getInstructionsData(Ess_M2ePro_Model_ActiveRecord_Diff $diff, $status)
    {
        /** @var Ess_M2ePro_Model_Walmart_Template_Category_Diff $diff */

        $data = array();

        if ($diff->isDetailsDifferent()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_DETAILS_DATA_CHANGED,
                'priority'  => 5,
            );
        }

        return $data;
    }

    //########################################
}
