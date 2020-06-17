<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Template_Synchronization_ChangeProcessor
    extends Ess_M2ePro_Model_Template_Synchronization_ChangeProcessorAbstract
{
    const INSTRUCTION_TYPE_REVISE_QTY_ENABLED            = 'template_synchronization_revise_qty_enabled';
    const INSTRUCTION_TYPE_REVISE_QTY_DISABLED           = 'template_synchronization_revise_qty_disabled';
    const INSTRUCTION_TYPE_REVISE_QTY_SETTINGS_CHANGED   = 'template_synchronization_revise_qty_settings_changed';

    const INSTRUCTION_TYPE_REVISE_PRICE_ENABLED          = 'template_synchronization_revise_price_enabled';
    const INSTRUCTION_TYPE_REVISE_PRICE_DISABLED         = 'template_synchronization_revise_price_disabled';

    const INSTRUCTION_TYPE_REVISE_DETAILS_ENABLED          = 'template_synchronization_revise_details_enabled';
    const INSTRUCTION_TYPE_REVISE_DETAILS_DISABLED         = 'template_synchronization_revise_details_disabled';

    const INSTRUCTION_TYPE_REVISE_IMAGES_ENABLED          = 'template_synchronization_revise_images_enabled';
    const INSTRUCTION_TYPE_REVISE_IMAGES_DISABLED         = 'template_synchronization_revise_images_disabled';

    //########################################

    protected function getInstructionsData(Ess_M2ePro_Model_ActiveRecord_Diff $diff, $status)
    {
        /** @var Ess_M2ePro_Model_Amazon_Template_Synchronization_Diff $diff */

        $data = parent::getInstructionsData($diff, $status);

        if ($diff->isReviseQtyEnabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_QTY_ENABLED,
                'priority'  => $status === Ess_M2ePro_Model_Listing_Product::STATUS_LISTED ? 80 : 5,
            );
        } elseif ($diff->isReviseQtyDisabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_QTY_DISABLED,
                'priority'  => 5,
            );
        } elseif ($diff->isReviseQtySettingsChanged()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_QTY_SETTINGS_CHANGED,
                'priority'  => $status === Ess_M2ePro_Model_Listing_Product::STATUS_LISTED ? 80 : 5,
            );
        }

        //----------------------------------------

        if ($diff->isRevisePriceEnabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_PRICE_ENABLED,
                'priority'  => $status === Ess_M2ePro_Model_Listing_Product::STATUS_LISTED ? 80 : 5,
            );
        } elseif ($diff->isRevisePriceDisabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_PRICE_DISABLED,
                'priority'  => 5,
            );
        }

        //----------------------------------------

        if ($diff->isReviseDetailsEnabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_DETAILS_ENABLED,
                'priority'  => $status === Ess_M2ePro_Model_Listing_Product::STATUS_LISTED ? 80 : 5,
            );
        } elseif ($diff->isReviseDetailsDisabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_DETAILS_DISABLED,
                'priority'  => 5,
            );
        }

        //----------------------------------------

        if ($diff->isReviseImagesEnabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_IMAGES_ENABLED,
                'priority'  => $status === Ess_M2ePro_Model_Listing_Product::STATUS_LISTED ? 80 : 5,
            );
        } elseif ($diff->isReviseImagesDisabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_IMAGES_DISABLED,
                'priority'  => 5,
            );
        }

        return $data;
    }

    //########################################
}
