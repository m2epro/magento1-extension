<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_Synchronization_Diff
    extends Ess_M2ePro_Model_Template_Synchronization_Diff_Abstract
{
    //########################################

    public function isReviseSettingsChanged()
    {
        return $this->isReviseQtyEnabled() ||
               $this->isReviseQtyDisabled() ||
               $this->isReviseQtySettingsChanged() ||
               $this->isRevisePriceEnabled() ||
               $this->isRevisePriceDisabled() ||
               $this->isRevisePriceSettingsChanged() ||
               $this->isRevisePromotionsEnabled() ||
               $this->isRevisePromotionsDisabled() ||
               $this->isReviseDetailsEnabled() ||
               $this->isReviseDetailsDisabled();
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

    // ---------------------------------------

    public function isRevisePriceSettingsChanged()
    {
        $keys = array(
            'revise_update_price_max_allowed_deviation_mode',
            'revise_update_price_max_allowed_deviation',
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################

    public function isRevisePromotionsEnabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return empty($oldSnapshotData['revise_update_promotions']) &&
              !empty($newSnapshotData['revise_update_promotions']);
    }

    public function isRevisePromotionsDisabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return !empty($oldSnapshotData['revise_update_promotions']) &&
                empty($newSnapshotData['revise_update_promotions']);
    }

    //########################################

    public function isReviseDetailsEnabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return empty($oldSnapshotData['revise_update_details']) &&
               !empty($newSnapshotData['revise_update_details']);
    }

    public function isReviseDetailsDisabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return !empty($oldSnapshotData['revise_update_details']) &&
               empty($newSnapshotData['revise_update_details']);
    }

    //########################################
}
