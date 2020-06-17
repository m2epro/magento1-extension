<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Template_Synchronization_DiffAbstract extends Ess_M2ePro_Model_ActiveRecord_Diff
{
    //########################################

    public function isDifferent()
    {
        return $this->isListModeEnabled() ||
               $this->isListModeDisabled() ||
               $this->isListSettingsChanged() ||
               $this->isRelistModeEnabled() ||
               $this->isRelistModeDisabled() ||
               $this->isRelistSettingsChanged() ||
               $this->isStopModeEnabled() ||
               $this->isStopModeDisabled() ||
               $this->isStopSettingsChanged() ||
               $this->isReviseSettingsChanged();

    }

    //########################################

    public function isListModeEnabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return empty($oldSnapshotData['list_mode']) && !empty($newSnapshotData['list_mode']);
    }

    public function isListModeDisabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return !empty($oldSnapshotData['list_mode']) && empty($newSnapshotData['list_mode']);
    }

    // ---------------------------------------

    public function isListSettingsChanged()
    {
        $keys = array(
            'list_status_enabled',
            'list_is_in_stock',
            'list_qty_calculated',
            'list_qty_calculated_value',
            'list_advanced_rules_mode',
            'list_advanced_rules_filters'
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################

    public function isRelistModeEnabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return empty($oldSnapshotData['relist_mode']) && !empty($newSnapshotData['relist_mode']);
    }

    public function isRelistModeDisabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return !empty($oldSnapshotData['relist_mode']) && empty($newSnapshotData['relist_mode']);
    }

    // ---------------------------------------

    public function isRelistSettingsChanged()
    {
        $keys = array(
            'relist_filter_user_lock',
            'relist_status_enabled',
            'relist_is_in_stock',
            'relist_qty_calculated',
            'relist_qty_calculated_value',
            'relist_advanced_rules_mode',
            'relist_advanced_rules_filters'
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################

    public function isStopModeEnabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return empty($oldSnapshotData['stop_mode']) && !empty($newSnapshotData['stop_mode']);
    }

    public function isStopModeDisabled()
    {
        $newSnapshotData = $this->_newSnapshot;
        $oldSnapshotData = $this->_oldSnapshot;

        return !empty($oldSnapshotData['stop_mode']) && empty($newSnapshotData['stop_mode']);
    }

    // ---------------------------------------

    public function isStopSettingsChanged()
    {
        $keys = array(
            'stop_status_disabled',
            'stop_out_off_stock',
            'stop_qty_calculated',
            'stop_qty_calculated_value',
            'stop_advanced_rules_mode',
            'stop_advanced_rules_filters'
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################

    abstract public function isReviseSettingsChanged();

    //########################################
}
