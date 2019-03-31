<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_ChangeProcessor
    extends Ess_M2ePro_Model_Amazon_Template_ChangeProcessor_Abstract
{
    const INSTRUCTION_TYPE_CONDITION_DATA_CHANGED = 'listing_condition_data_changed';
    const INSTRUCTION_TYPE_SKU_SETTINGS_CHANGED   = 'listing_sku_settings_changed';

    const INSTRUCTION_INITIATOR = 'listing_change_processor';

    //########################################

    protected function getInstructionInitiator()
    {
        return self::INSTRUCTION_INITIATOR;
    }

    // ---------------------------------------

    protected function getInstructionsData(Ess_M2ePro_Model_Template_Diff_Abstract $diff, $status)
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Diff $diff */

        $data = array();

        if ($diff->isQtyDifferent()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 40;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
                'priority'  => $priority,
            );
        }

        if ($diff->isConditionDifferent()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
                $priority = 30;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_CONDITION_DATA_CHANGED,
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

        if ($diff->isImagesDifferent()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
                'priority'  => $priority,
            );
        }

        if ($diff->isSkuSettingsDifferent()) {
            $priority = 0;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
                $priority = 30;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_SKU_SETTINGS_CHANGED,
                'priority'  => $priority,
            );
        }

        return $data;
    }

    //########################################
}