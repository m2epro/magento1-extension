<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Synchronization_Diff
    extends Ess_M2ePro_Model_Template_Synchronization_DiffAbstract
{
    //########################################

    public function isReviseSettingsChanged()
    {
        return $this->isReviseQtyEnabled() ||
               $this->isReviseQtyDisabled() ||
               $this->isReviseQtySettingsChanged() ||
               $this->isRevisePriceEnabled() ||
               $this->isRevisePriceDisabled() ||
               $this->isReviseTitleEnabled() ||
               $this->isReviseTitleDisabled() ||
               $this->isReviseSubtitleEnabled() ||
               $this->isReviseSubtitleDisabled() ||
               $this->isReviseDescriptionEnabled() ||
               $this->isReviseDescriptionDisabled() ||
               $this->isReviseImagesEnabled() ||
               $this->isReviseImagesDisabled() ||
               $this->isReviseCategoriesEnabled() ||
               $this->isReviseCategoriesDisabled() ||
               $this->isRevisePartsEnabled() ||
               $this->isRevisePartsDisabled() ||
               $this->isRevisePaymentEnabled() ||
               $this->isRevisePaymentDisabled() ||
               $this->isReviseShippingEnabled() ||
               $this->isReviseShippingDisabled() ||
               $this->isReviseReturnEnabled() ||
               $this->isReviseReturnDisabled() ||
               $this->isReviseOtherEnabled() ||
               $this->isReviseOtherDisabled();
    }

    //########################################

    public function isReviseQtyEnabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return empty($oldSnapshotData['revise_update_qty']) && !empty($newSnapshotData['revise_update_qty']);
    }

    public function isReviseQtyDisabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return !empty($oldSnapshotData['revise_update_qty']) && empty($newSnapshotData['revise_update_qty']);
    }

    // ---------------------------------------

    public function isReviseQtySettingsChanged()
    {
        $keys = array(
            'revise_update_qty_max_applied_value_mode',
            'revise_update_qty_max_applied_value',
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################

    public function isRevisePriceEnabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return empty($oldSnapshotData['revise_update_price']) && !empty($newSnapshotData['revise_update_price']);
    }

    public function isRevisePriceDisabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return !empty($oldSnapshotData['revise_update_price']) && empty($newSnapshotData['revise_update_price']);
    }

    //########################################

    public function isReviseTitleEnabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return empty($oldSnapshotData['revise_update_title']) && !empty($newSnapshotData['revise_update_title']);
    }

    public function isReviseTitleDisabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return !empty($oldSnapshotData['revise_update_title']) && empty($newSnapshotData['revise_update_title']);
    }

    //########################################

    public function isReviseSubtitleEnabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return empty($oldSnapshotData['revise_update_sub_title'])
               && !empty($newSnapshotData['revise_update_sub_title']);
    }

    public function isReviseSubtitleDisabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return !empty($oldSnapshotData['revise_update_sub_title'])
               && empty($newSnapshotData['revise_update_sub_title']);
    }

    //########################################

    public function isReviseDescriptionEnabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return empty($oldSnapshotData['revise_update_description'])
               && !empty($newSnapshotData['revise_update_description']);
    }

    public function isReviseDescriptionDisabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return !empty($oldSnapshotData['revise_update_description'])
               && empty($newSnapshotData['revise_update_description']);
    }

    //########################################

    public function isReviseImagesEnabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return empty($oldSnapshotData['revise_update_images']) && !empty($newSnapshotData['revise_update_images']);
    }

    public function isReviseImagesDisabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return !empty($oldSnapshotData['revise_update_images']) && empty($newSnapshotData['revise_update_images']);
    }

    //########################################

    public function isReviseCategoriesEnabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return empty($oldSnapshotData['revise_update_categories'])
               && !empty($newSnapshotData['revise_update_categories']);
    }

    public function isReviseCategoriesDisabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return !empty($oldSnapshotData['revise_update_categories'])
            && empty($newSnapshotData['revise_update_categories']);
    }

    //########################################

    public function isRevisePartsEnabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return empty($oldSnapshotData['revise_update_parts'])
            && !empty($newSnapshotData['revise_update_parts']);
    }

    public function isRevisePartsDisabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return !empty($oldSnapshotData['revise_update_parts'])
            && empty($newSnapshotData['revise_update_parts']);
    }

    //########################################

    public function isRevisePaymentEnabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return empty($oldSnapshotData['revise_update_payment']) && !empty($newSnapshotData['revise_update_payment']);
    }

    public function isRevisePaymentDisabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return !empty($oldSnapshotData['revise_update_payment']) && empty($newSnapshotData['revise_update_payment']);
    }

    //########################################

    public function isReviseShippingEnabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return empty($oldSnapshotData['revise_update_shipping']) && !empty($newSnapshotData['revise_update_shipping']);
    }

    public function isReviseShippingDisabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return !empty($oldSnapshotData['revise_update_shipping']) && empty($newSnapshotData['revise_update_shipping']);
    }

    //########################################

    public function isReviseReturnEnabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return empty($oldSnapshotData['revise_update_return']) && !empty($newSnapshotData['revise_update_return']);
    }

    public function isReviseReturnDisabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return !empty($oldSnapshotData['revise_update_return']) && empty($newSnapshotData['revise_update_return']);
    }

    //########################################

    public function isReviseOtherEnabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return empty($oldSnapshotData['revise_update_other']) && !empty($newSnapshotData['revise_update_other']);
    }

    public function isReviseOtherDisabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return !empty($oldSnapshotData['revise_update_other']) && empty($newSnapshotData['revise_update_other']);
    }

    //########################################
}
