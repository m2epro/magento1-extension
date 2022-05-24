<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Servicing_Task_Analytics_Registry
{
    const STORAGE_KEY = 'servicing/analytics';

    //########################################

    public function isPlannedNow()
    {
        $plannedAt  = $this->getPlannedAt();
        $startedAt  = $this->getStartedAt();
        $finishedAt = $this->getFinishedAt();

        /** @var Ess_M2ePro_Helper_Data $helper */
        $helper = Mage::helper('M2ePro');
        if (empty($plannedAt) ||
            (int)$helper->createGmtDateTime($plannedAt)->format('U') > $helper->getCurrentGmtDate(true)
        ) {
            return false;
        }

        if (!empty($startedAt) && !empty($finishedAt)) {
            return false;
        }

        return true;
    }

    public function getPlannedAt()
    {
        $regData = $this->getStoredData();
        return isset($regData['planned_at']) ? $regData['planned_at'] : null;
    }

    public function getStartedAt()
    {
        $regData = $this->getStoredData();
        return isset($regData['started_at']) ? $regData['started_at'] : null;
    }

    public function getFinishedAt()
    {
        $regData = $this->getStoredData();
        return isset($regData['finished_at']) ? $regData['finished_at'] : null;
    }

    // ---------------------------------------

    public function markPlannedAt($date)
    {
        $regData = $this->getStoredData();

        $regData['planned_at'] = $date;
        unset($regData['started_at'], $regData['finished_at'], $regData['progress']);

        $this->setStoredData($regData);
    }

    public function markStarted()
    {
        $regData = $this->getStoredData();

        $regData['started_at'] = Mage::helper('M2ePro')->getCurrentGmtDate(false, 'Y-m-d H:i:s');
        $regData['progress'] = array();

        $this->setStoredData($regData);
    }

    public function markFinished()
    {
        $regData = $this->getStoredData();
        $regData['finished_at'] = Mage::helper('M2ePro')->getCurrentGmtDate(false, 'Y-m-d H:i:s');

        $this->setStoredData($regData);
    }

    //########################################

    public function getProgressData($nick, $progressDataKey)
    {
        $regData = $this->getStoredData();
        return isset($regData['progress'][$nick][$progressDataKey]) ? $regData['progress'][$nick][$progressDataKey]
                                                                    : null;
    }

    public function setProgressData($nick, $progressDataKey, $progressDataValue)
    {
        $regData = $this->getStoredData();
        $regData['progress'][$nick][$progressDataKey] = $progressDataValue;

        $this->setStoredData($regData);
    }

    //########################################

    protected function setStoredData($data)
    {
        return Mage::helper('M2ePro/Module')->getRegistry()->setValue(self::STORAGE_KEY, $data);
    }

    protected function getStoredData()
    {
        return Mage::helper('M2ePro/Module')->getRegistry()->getValueFromJson(self::STORAGE_KEY);
    }

    //########################################
}
