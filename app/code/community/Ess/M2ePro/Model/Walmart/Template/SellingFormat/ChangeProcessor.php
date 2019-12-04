<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_SellingFormat_ChangeProcessor
    extends Ess_M2ePro_Model_Walmart_Template_ChangeProcessor_Abstract
{
    const INSTRUCTION_INITIATOR = 'template_selling_format_change_processor';

    //########################################

    protected function getInstructionInitiator()
    {
        return self::INSTRUCTION_INITIATOR;
    }

    // ---------------------------------------

    protected function getInstructionsData(Ess_M2ePro_Model_Template_Diff_Abstract $diff, $status)
    {
        /** @var Ess_M2ePro_Model_Walmart_Template_SellingFormat_Diff $diff */

        $data = array();

        if ($diff->isQtyDifferent()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
                'priority'  => 80,
            );
        }

        if ($diff->isLagTimeDifferent()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_LAG_TIME_DATA_CHANGED,
                'priority'  => 60,
            );
        }

        if ($diff->isPriceDifferent()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 60;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_PRICE_DATA_CHANGED,
                'priority'  => $priority,
            );
        }

        if ($diff->isPromotionsDifferent()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 50;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_PROMOTIONS_DATA_CHANGED,
                'priority'  => $priority,
            );
        }

        if ($diff->isDetailsDifferent()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_DETAILS_DATA_CHANGED,
                'priority'  => $priority,
            );
        }

        return $data;
    }

    //########################################
}
