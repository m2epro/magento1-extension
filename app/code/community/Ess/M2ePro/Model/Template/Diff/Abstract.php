<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Template_Diff_Abstract
{
    /** @var array */
    protected $newSnapshot = array();

    /** @var array */
    protected $oldSnapshot = array();

    //########################################

    public function setNewSnapshot(array $snapshot)
    {
        $this->newSnapshot = $snapshot;
        return $this;
    }

    public function setOldSnapshot(array $snapshot)
    {
        $this->oldSnapshot = $snapshot;
        return $this;
    }

    //########################################

    abstract public function isDifferent();

    //########################################

    protected function isSettingsDifferent($keys, $groupKey = NULL)
    {
        $newSnapshotData = $this->newSnapshot;
        if (!is_null($groupKey) && isset($newSnapshotData[$groupKey])) {
            $newSnapshotData = $newSnapshotData[$groupKey];
        }

        $oldSnapshotData = $this->oldSnapshot;
        if (!is_null($groupKey) && isset($oldSnapshotData[$groupKey])) {
            $oldSnapshotData = $oldSnapshotData[$groupKey];
        }

        foreach ($keys as $key) {
            if (empty($newSnapshotData[$key]) && empty($oldSnapshotData[$key])) {
                continue;
            }

            if (empty($newSnapshotData[$key]) || empty($oldSnapshotData[$key])) {
                return true;
            }

            if ($newSnapshotData[$key] != $oldSnapshotData[$key]) {
                return true;
            }
        }

        return false;
    }

    //########################################
}