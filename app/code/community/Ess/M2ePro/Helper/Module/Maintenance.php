<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Module_Maintenance extends Mage_Core_Helper_Abstract
{
    const MAINTENANCE_COOKIE_KEY = 'm2epro_maintenance';
    const MAINTENANCE_COOKIE_DURATION = 3600;

    // ########################################

    public function isEnabled()
    {
        return (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/debug/maintenance/', 'mode');
    }

    public function isOwner()
    {
        return (bool)Mage::app()->getCookie()->get(self::MAINTENANCE_COOKIE_KEY);
    }

    // ########################################

    public function enable()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/debug/maintenance/', 'mode', 1);

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $restoreDate = Mage::helper('M2ePro')->getDate($currentTimeStamp + self::MAINTENANCE_COOKIE_DURATION);
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/debug/maintenance/', 'restore_date', $restoreDate);

        Mage::app()->getCookie()->set(self::MAINTENANCE_COOKIE_KEY, 'true', 60*60*24);
    }

    public function disable()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/debug/maintenance/', 'mode', 0);
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/debug/maintenance/', 'restore_date', null);
        Mage::app()->getCookie()->set(self::MAINTENANCE_COOKIE_KEY, '', 0);
    }

    // ########################################

    public function isExpired()
    {
        $restoreDate = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/debug/maintenance/', 'restore_date'
        );

        if (!$restoreDate) {
            return true;
        }

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);

        if ($currentTimeStamp >= strtotime($restoreDate)) {
            return true;
        }

        return false;
    }

    public function prolongRestoreDate()
    {
        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $restoreDate = Mage::helper('M2ePro')->getDate($currentTimeStamp + self::MAINTENANCE_COOKIE_DURATION);
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/debug/maintenance/', 'restore_date', $restoreDate);
    }

    // ########################################
}