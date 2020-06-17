<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ActiveRecord_Diff
{
    /** @var array */
    protected $_newSnapshot = array();

    /** @var array */
    protected $_oldSnapshot = array();

    //########################################

    public function setNewSnapshot(array $snapshot)
    {
        $this->_newSnapshot = $snapshot;
        return $this;
    }

    public function getNewSnapShot()
    {
        return $this->_newSnapshot;
    }

    public function setOldSnapshot(array $snapshot)
    {
        $this->_oldSnapshot = $snapshot;
        return $this;
    }

    public function getOldSnapShot()
    {
        return $this->_oldSnapshot;
    }

    //########################################

    public function isDifferent()
    {
        return $this->_newSnapshot !== $this->_oldSnapshot;
    }

    //########################################

    protected function isSettingsDifferent($keys, $groupKey = NULL)
    {
        $newSnapshotData = $this->_newSnapshot;
        if (null !== $groupKey && isset($newSnapshotData[$groupKey])) {
            $newSnapshotData = $newSnapshotData[$groupKey];
        }

        $oldSnapshotData = $this->_oldSnapshot;
        if (null !== $groupKey && isset($oldSnapshotData[$groupKey])) {
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
