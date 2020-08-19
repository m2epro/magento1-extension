<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Server_Maintenance extends Mage_Core_Helper_Abstract
{
    protected $_dateEnabledFrom;
    protected $_dateEnabledTo;

    //########################################

    public function isScheduled()
    {
        $dateEnabledFrom = $this->getDateEnabledFrom();
        $dateEnabledTo = $this->getDateEnabledTo();

        if ($dateEnabledFrom == false || $dateEnabledTo == false) {
            return false;
        }

        $dateCurrent = new DateTime('now', new DateTimeZone('UTC'));
        if ($dateCurrent < $dateEnabledFrom && $dateCurrent < $dateEnabledTo) {
            return true;
        }

        return false;
    }

    public function isNow()
    {
        $dateEnabledFrom = $this->getDateEnabledFrom();
        $dateEnabledTo = $this->getDateEnabledTo();

        if ($dateEnabledFrom == false || $dateEnabledTo == false) {
            return false;
        }

        $dateCurrent = new DateTime('now', new DateTimeZone('UTC'));
        if ($dateCurrent > $dateEnabledFrom && $dateCurrent < $dateEnabledTo) {
            return true;
        }

        return false;
    }

    //########################################

    public function getDateEnabledFrom()
    {
        if ($this->_dateEnabledFrom === null) {
            $dateEnabledFrom = Mage::helper('M2ePro/Module')->getRegistry()->getValue(
                '/server/maintenance/schedule/date/enabled/from/'
            );
            $this->_dateEnabledFrom = $dateEnabledFrom
                ? new DateTime($dateEnabledFrom, new DateTimeZone('UTC'))
                : false;
        }

        return $this->_dateEnabledFrom;
    }

    public function setDateEnabledFrom($date)
    {
        Mage::helper('M2ePro/Module')->getRegistry()->setValue(
            '/server/maintenance/schedule/date/enabled/from/',
            $date
        );
        $this->_dateEnabledFrom = $date;

        return $this;
    }

    public function getDateEnabledTo()
    {
        if ($this->_dateEnabledTo === null) {
            $dateEnabledTo = Mage::helper('M2ePro/Module')->getRegistry()->getValue(
                '/server/maintenance/schedule/date/enabled/to/'
            );
            $this->_dateEnabledTo = $dateEnabledTo
                ? new DateTime($dateEnabledTo, new DateTimeZone('UTC'))
                : false;
        }

        return $this->_dateEnabledTo;
    }

    public function setDateEnabledTo($date)
    {
        Mage::helper('M2ePro/Module')->getRegistry()->setValue(
            '/server/maintenance/schedule/date/enabled/to/',
            $date
        );
        $this->_dateEnabledTo = $date;

        return $this;
    }

    //########################################

    public function processUnexpectedMaintenance()
    {
        if ($this->isNow()) {
            return;
        }

        $to = new \DateTime('now', new \DateTimeZone('UTC'));
        $to->modify('+ 10 minutes');
        // @codingStandardsIgnoreLine
        $to->modify('+' . mt_rand(0, 300) . ' second');

        $this->setDateEnabledFrom(Mage::helper('M2ePro')->getCurrentGmtDate());
        $this->setDateEnabledTo($to->format('Y-m-d H:i:s'));
    }

    //########################################
}
