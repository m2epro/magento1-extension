<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Ebay_PickupStore extends Mage_Core_Helper_Abstract
{
    //########################################

    public function isFeatureEnabled()
    {
        $sessionCache = Mage::helper('M2ePro/Data_Cache_Runtime');

        if ($sessionCache->getValue('bopis') !== null) {
            return $sessionCache->getValue('bopis');
        }

        $isEnabled = (bool)$this->getEnabledAccount();

        $sessionCache->setValue('bopis', $isEnabled);
        return $isEnabled;
    }

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getEnabledAccount()
    {
        /** @var Ess_M2ePro_Model_Account[] $accounts */
        $accounts = Mage::getModel('M2ePro/Account')->getCollection()
            ->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);

        foreach ($accounts as $account) {
            if ($account->getChildObject()->isPickupStoreEnabled()) {
                return $account;
            }
        }

        return false;
    }

    //########################################

    public function convertMarketplaceToCountry($marketplace)
    {
        $countries = Mage::helper('M2ePro/Magento')->getCountries();

        foreach ($countries as $country) {
            if (!empty($country['country_id']) &&
                $country['country_id'] == strtoupper($marketplace['origin_country'])
            ) {
                return $country;
            }
        }

        return false;
    }

    //########################################

    public function validateRequiredFields(array $data)
    {
        $requiredFields = array(
            'name', 'location_id', 'account_id', 'marketplace_id',
            'phone', 'postal_code', 'utc_offset',
            'country', 'region', 'city', 'address_1',
            'business_hours'
        );

        foreach ($requiredFields as $requiredField) {
            if (empty($data[$requiredField])) {
                return false;
            }
        }

        return true;
    }

    // ---------------------------------------

    public function prepareRequestData(array $data)
    {
        $requestData = array();
        $requestData['location_id'] = $data['location_id'];
        $requestData['location'] = $this->getLocationData($data);
        $requestData['info'] = $this->getInfoData($data);
        $requestData['working'] = $this->getWorkingHoursData($data);

        return $requestData;
    }

    protected function getLocationData(array $data)
    {
        $physical = array(
            'country' => $data['country'],
            'city' => $data['city'],
            'region' => $data['region'],
            'postal_code' => $data['postal_code'],
            'address_1' => $data['address_1']
        );

        if (!empty($data['address_second'])) {
            $physical['address_2'] = $data['address_2'];
        }

        $geoData = array(
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'utc_offset' => $data['utc_offset']
        );

        return array('physical' => $physical, 'geo_data' => $geoData);
    }

    protected function getInfoData(array $data)
    {
        $info = array();

        $info['name'] = $data['name'];
        $info['phone'] = $data['phone'];

        if (!empty($data['url'])) {
            $info['url'] = $data['url'];
        }

        return $info;
    }

    protected function getWorkingHoursData(array $data)
    {
        $weekHours = Mage::helper('M2ePro')->jsonDecode($data['business_hours']);
        $weekValues = array(
            'monday'    => 1,
            'tuesday'   => 2,
            'wednesday' => 3,
            'thursday'  => 4,
            'friday'    => 5,
            'saturday'  => 6,
            'sunday'    => 7
        );

        $parsedWeekHours = array();
        foreach ($weekHours['week_days'] as $weekDay) {
            if (!isset($weekHours['week_settings'][$weekDay])) {
                continue;
            }

            $parsedWeekHours[$weekValues[$weekDay]] = $weekHours['week_settings'][$weekDay];
        }

        $holidaysHours = Mage::helper('M2ePro')->jsonDecode($data['special_hours']);
        return array(
            'week' => $parsedWeekHours,
            'holidays' => $holidaysHours['date_settings']
        );
    }

    //########################################
}
