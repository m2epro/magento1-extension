<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_StoreCategory_ChangeProcessor
    extends Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract
{
    const INSTRUCTION_INITIATOR = 'template_store_category_change_processor';

    //########################################

    protected function getInstructionInitiator()
    {
        return self::INSTRUCTION_INITIATOR;
    }

    // ---------------------------------------

    protected function getInstructionsData(Ess_M2ePro_Model_ActiveRecord_Diff $diff, $status)
    {
        /** @var Ess_M2ePro_Model_Ebay_Template_StoreCategory_Diff $diff */

        $data = array();

        if ($diff->isCategoriesDifferent()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED,
                'priority'  => $status === Ess_M2ePro_Model_Listing_Product::STATUS_LISTED ? 30 : 5,
            );
        }

        return $data;
    }

    //########################################
}
