<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_OtherCategory_ChangeProcessor
    extends Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract
{
    const INSTRUCTION_INITIATOR = 'template_other_category_change_processor';

    //########################################

    protected function getInstructionInitiator()
    {
        return self::INSTRUCTION_INITIATOR;
    }

    // ---------------------------------------

    protected function getInstructionsData(Ess_M2ePro_Model_Template_Diff_Abstract $diff, $status)
    {
        /** @var Ess_M2ePro_Model_Ebay_Template_Category_Diff $diff */

        $data = array();

        if ($diff->isCategoriesDifferent()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED,
                'priority'  => $priority,
            );
        }

        return $data;
    }

    //########################################
}
