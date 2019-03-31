<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Account_Repricing_ChangeProcessor
    extends Ess_M2ePro_Model_Amazon_Template_ChangeProcessor_Abstract
{
    const INSTRUCTION_INITIATOR = 'account_repricing_change_processor';

    const INSTRUCTION_TYPE_ACCOUNT_REPRICING_DATA_CHANGED = 'account_repricing_data_changed';

    //########################################

    protected function getInstructionInitiator()
    {
        return self::INSTRUCTION_INITIATOR;
    }

    // ---------------------------------------

    protected function getInstructionsData(Ess_M2ePro_Model_Template_Diff_Abstract $diff, $status)
    {
        /** @var Ess_M2ePro_Model_Amazon_Account_Repricing_Diff $diff */

        $data = array();

        if ($diff->isRepricingDifferent()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_ACCOUNT_REPRICING_DATA_CHANGED,
                'priority'  => $priority,
            );
        }

        return $data;
    }

    //########################################
}