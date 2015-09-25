<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Synchronization_Edit_Form_Tabs_Schedule
    extends Ess_M2ePro_Block_Adminhtml_Ebay_Template_Synchronization_Edit_Form_Data
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayTemplateSynchronizationEditFormTabsSchedule');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/template/synchronization/form/tabs/schedule.phtml');
    }

    // ####################################

    public function getFormData()
    {
        $data = parent::getFormData();

        //--
        if (!empty($data['schedule_interval_settings']) && is_string($data['schedule_interval_settings'])) {

            $scheduleIntervalSettings = json_decode($data['schedule_interval_settings'], true);
            unset($data['schedule_interval_settings']);

            if (isset($scheduleIntervalSettings['mode'])) {
                $data['schedule_interval_settings']['mode'] = $scheduleIntervalSettings['mode'];
            }

            if (isset($scheduleIntervalSettings['date_from'])) {
                $data['schedule_interval_settings']['date_from'] =
                    Mage::helper('M2ePro')->gmtDateToTimezone($scheduleIntervalSettings['date_from'],false,'Y-m-d');
            }

            if (isset($scheduleIntervalSettings['date_to'])) {
                $data['schedule_interval_settings']['date_to'] =
                    Mage::helper('M2ePro')->gmtDateToTimezone($scheduleIntervalSettings['date_to'],false,'Y-m-d');
            }
        } else {
            unset($data['schedule_interval_settings']);
        }
        //--

        //--
        if (!empty($data['schedule_week_settings']) && is_string($data['schedule_week_settings'])) {

            $scheduleWeekSettings = json_decode($data['schedule_week_settings'], true);
            unset($data['schedule_week_settings']);

            $parsedSettings = array();
            foreach ($scheduleWeekSettings as $day => $scheduleDaySettings) {

                $fromTimestamp = strtotime($scheduleDaySettings['time_from']);
                $toTimestamp   = strtotime($scheduleDaySettings['time_to']);

                $parsedSettings[$day] = array(
                    'hours_from'   => date('g', $fromTimestamp),
                    'minutes_from' => date('i', $fromTimestamp),
                    'appm_from'    => date('a', $fromTimestamp),

                    'hours_to'   => date('g', $toTimestamp),
                    'minutes_to' => date('i', $toTimestamp),
                    'appm_to'    => date('a', $toTimestamp),
                );
            }

            $data['schedule_week_settings'] = $parsedSettings;
        } else {
            unset($data['schedule_week_settings']);
        }
        //--

        return $data;
    }

    // ####################################

    public function getDefault()
    {
        $default = Mage::helper('M2ePro/View_Ebay')->isSimpleMode()
            ? Mage::getSingleton('M2ePro/Ebay_Template_Synchronization')->getScheduleDefaultSettingsSimpleMode()
            : Mage::getSingleton('M2ePro/Ebay_Template_Synchronization')->getScheduleDefaultSettingsAdvancedMode();

        $default['schedule_interval_settings'] = json_decode($default['schedule_interval_settings'], true);
        $default['schedule_week_settings'] = json_decode($default['schedule_week_settings'], true);

        return $default;
    }

    // ####################################

    public function isDayExistInWeekSettingsArray($day, $weekSettings)
    {
        $daysInSettingsArray = array_keys($weekSettings);
        return in_array(strtolower($day), $daysInSettingsArray);
    }

    // ####################################
}