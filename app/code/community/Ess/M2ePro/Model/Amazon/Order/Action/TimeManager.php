<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Order_Action_TimeManager
{
    const INTERVAL_ACTION_UPDATE = 3600;
    const INTERVAL_ACTION_CANCEL = 18000;
    const INTERVAL_ACTION_REFUND = 18000;

    public function isTimeToProcessUpdate($merchantId)
    {
        $lastProcessDate = Mage::helper('M2ePro/Module')->getRegistry()->getValue($this->getUpdateKey($merchantId));
        if (empty($lastProcessDate)) {
            return true;
        }

        return $this->isTime($lastProcessDate, self::INTERVAL_ACTION_UPDATE);
    }

    public function isTimeToProcessCancel($merchantId)
    {
        $lastProcessDate = Mage::helper('M2ePro/Module')->getRegistry()->getValue($this->getCancelKey($merchantId));
        if (empty($lastProcessDate)) {
            return true;
        }

        return $this->isTime($lastProcessDate, self::INTERVAL_ACTION_CANCEL);
    }

    public function isTimeToProcessRefund($merchantId)
    {
        $lastProcessDate = Mage::helper('M2ePro/Module')->getRegistry()->getValue($this->getRefundKey($merchantId));
        if (empty($lastProcessDate)) {
            return true;
        }

        return $this->isTime($lastProcessDate, self::INTERVAL_ACTION_REFUND);
    }

    public function setLastUpdate($merchantId, \DateTime $date)
    {
        Mage::helper('M2ePro/Module')->getRegistry()->setValue(
            $this->getUpdateKey($merchantId), $date->format('Y-m-d H:i:s')
        );
    }

    public function setLastCancel($merchantId, \DateTime $date)
    {
        Mage::helper('M2ePro/Module')->getRegistry()->setValue(
            $this->getCancelKey($merchantId), $date->format('Y-m-d H:i:s')
        );
    }

    public function setLastRefund($merchantId, \DateTime $date)
    {
        Mage::helper('M2ePro/Module')->getRegistry()->setValue(
            $this->getRefundKey($merchantId), $date->format('Y-m-d H:i:s')
        );
    }

    // ----------------------------------------

    private function isTime($lastRun, $interval)
    {
        $lastProcessDate = Mage::helper('M2ePro')->createGmtDateTime($lastRun);

        $currentDate = Mage::helper('M2ePro')->createCurrentGmtDateTime();

        return $lastProcessDate->format('U') < ($currentDate->format('U') - $interval);
    }

    private function getUpdateKey($merchantId)
    {
        return "/amazon/orders/update/{$merchantId}/process_date/";
    }

    private function getCancelKey($merchantId)
    {
        return "/amazon/orders/cancel/{$merchantId}/process_date/";
    }

    private function getRefundKey($merchantId)
    {
        return "/amazon/orders/refund/{$merchantId}/process_date/";
    }
}
