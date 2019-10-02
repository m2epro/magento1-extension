<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_PickupStore_Edit_Tabs_BusinessHours extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayAccountPickupStoreEditTabsBusinessHours');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/account/pickupStore/tabs/businessHours.phtml');
    }

    //########################################

    public function getFormData()
    {
        $default = array(
            'business_hours' => array('week_settings' => array(), 'week_days' => array()),
            'special_hours' => array('date_settings' => array())
        );

        $formData = array();
        $model = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        if ($model !== null) {
            $formData = $model->toArray();
        }

        if (!empty($formData['business_hours'])) {
            $formData['business_hours'] = $this->prepareHoursData(
                $formData['business_hours'], 'week_settings'
            );
        }

        if (!empty($formData['special_hours'])) {
            $formData['special_hours'] = $this->prepareHoursData(
                $formData['special_hours'], 'date_settings'
            );
        }

        return array_merge($default, $formData);
    }

    //########################################

    public function isDayExistInWeekSettingsArray($day, $weekDays)
    {
        return in_array(strtolower($day), $weekDays);
    }

    protected function prepareHoursData($hoursData, $key)
    {
        $data = array();

        if (!empty($hoursData)) {
            $data = Mage::helper('M2ePro')->jsonDecode($hoursData);

            if (!isset($data[$key])) {
                return $data;
            }

            $parsedSettings = array();
            foreach ($data[$key] as $day => $daySettings) {
                $fromHours = date('G', strtotime($daySettings['open']));
                $fromMinutes = date('i', strtotime($daySettings['open']));

                $toHours = date('G', strtotime($daySettings['close']));
                $toMinutes = date('i', strtotime($daySettings['close']));

                $parsedSettings[$day] = array(
                    'from_hours'   => $fromHours == 0 ? 24 : $fromHours,
                    'from_minutes' => $fromMinutes,

                    'to_hours'   => $toHours == 0 ? 24 : $toHours,
                    'to_minutes' => $toMinutes,
                );
            }

            $data[$key] = $parsedSettings;
        }

        return $data;
    }

    //########################################
}
