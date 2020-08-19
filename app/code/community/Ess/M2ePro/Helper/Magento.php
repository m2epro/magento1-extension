<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Magento extends Mage_Core_Helper_Abstract
{
    //########################################

    public function getName()
    {
        return 'magento';
    }

    public function getVersion($asArray = false)
    {
        $versionString = Mage::getVersion();
        return $asArray ? explode('.', $versionString) : $versionString;
    }

    //########################################

    public function getEditionName()
    {
        if ($this->isEnterpriseEdition()) {
            return 'enterprise';
        }

        if ($this->isCommunityEdition()) {
            return 'community';
        }

        return 'undefined';
    }

    // ---------------------------------------

    public function isEnterpriseEdition()
    {
        return Mage::getConfig()->getModuleConfig('Enterprise_Enterprise') &&
               Mage::getConfig()->getModuleConfig('Enterprise_AdminGws') &&
               Mage::getConfig()->getModuleConfig('Enterprise_Checkout') &&
               Mage::getConfig()->getModuleConfig('Enterprise_Customer');
    }

    public function isCommunityEdition()
    {
        return !$this->isEnterpriseEdition();
    }

    //########################################

    public function getMySqlTables()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read')->listTables();
    }

    public function getDatabaseTablesPrefix()
    {
        return (string)Mage::getConfig()->getTablePrefix();
    }

    public function getDatabaseName()
    {
        return (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname');
    }

    //########################################

    public function getModules()
    {
        return array_keys((array)Mage::getConfig()->getNode('modules')->children());
    }

    public function isTinyMceAvailable()
    {
        if ($this->isCommunityEdition()) {
            return version_compare($this->getVersion(false), '1.4.0.0', '>=');
        }

        return true;
    }

    public function getBaseCurrency()
    {
        return (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
    }

    // ---------------------------------------

    public function isSecretKeyToUrl()
    {
        return (bool)Mage::getStoreConfigFlag('admin/security/use_form_key');
    }

    public function getCurrentSecretKey()
    {
        if (!$this->isSecretKeyToUrl()) {
            return '';
        }

        return Mage::getSingleton('adminhtml/url')->getSecretKey();
    }

    //########################################

    public function isDeveloper()
    {
        return (bool)Mage::getIsDeveloperMode();
    }

    public function isCronWorking()
    {
        $minDateTime = new DateTime(Mage::helper('M2ePro')->getCurrentGmtDate(), new DateTimeZone('UTC'));
        $minDateTime->modify('-1 day');
        $minDateTime = Mage::helper('M2ePro')->getDate($minDateTime->format('U'));

        $collection = Mage::getModel('cron/schedule')->getCollection();
        $collection->addFieldToFilter('executed_at', array('gt'=>$minDateTime));

        return $collection->getSize() > 0;
    }

    public function getBaseUrl()
    {
        return str_replace('index.php/', '', Mage::getBaseUrl());
    }

    public function getLocale()
    {
        $localeComponents = explode('_', Mage::app()->getLocale()->getLocale());
        return strtolower($localeComponents[0]);
    }

    public function getTranslatedCountryName($countryId, $localeCode = 'en_US')
    {
        /** @var $locale Mage_Core_Model_Locale */
        $locale = Mage::getSingleton('core/locale');
        if ($locale->getLocaleCode() != $localeCode) {
            $locale->setLocaleCode($localeCode);
        }

        return $locale->getCountryTranslation($countryId);
    }

    public function getCountries()
    {
        $unsortedCountries = Mage::getModel('directory/country_api')->items();

        $unsortedCountriesNames = array();
        foreach ($unsortedCountries as $country) {
            $unsortedCountriesNames[] = $country['name'];
        }

        sort($unsortedCountriesNames, SORT_STRING);

        $sortedCountries = array();
        foreach ($unsortedCountriesNames as $name) {
            foreach ($unsortedCountries as $country) {
                if ($country['name'] == $name) {
                    $sortedCountries[] = $country;
                    break;
                }
            }
        }

        return $sortedCountries;
    }

    public function getRegionsByCountryCode($countryCode)
    {
        $result = array();

        try {
            $country = Mage::getModel('directory/country')->loadByCode($countryCode);
        } catch (\Exception $e) {
            Mage::helper('M2ePro/Module_Exception')->process($e);
            return $result;
        }

        if (!$country->getId()) {
            return $result;
        }

        $result = array();
        foreach ($country->getRegions() as $region) {
            $result[] = array(
                'region_id' => $region->getRegionId(),
                'code'      => $region->getCode(),
                'name'      => $region->getName()
            );
        }

        if (empty($result) && $countryCode == 'AU') {
            $result = array(
                array('region_id' => '','code' => 'NSW','name' => 'New South Wales'),
                array('region_id' => '','code' => 'QLD','name' => 'Queensland'),
                array('region_id' => '','code' => 'SA','name' => 'South Australia'),
                array('region_id' => '','code' => 'TAS','name' => 'Tasmania'),
                array('region_id' => '','code' => 'VIC','name' => 'Victoria'),
                array('region_id' => '','code' => 'WA','name' => 'Western Australia'),
            );
        } else if (empty($result) && $countryCode == 'GB') {
            $result = array(
                array('region_id' => '','code' => 'UKH','name' => 'East of England'),
                array('region_id' => '','code' => 'UKF','name' => 'East Midlands'),
                array('region_id' => '','code' => 'UKI','name' => 'London'),
                array('region_id' => '','code' => 'UKC','name' => 'North East'),
                array('region_id' => '','code' => 'UKD','name' => 'North West'),
                array('region_id' => '','code' => 'UKJ','name' => 'South East'),
                array('region_id' => '','code' => 'UKK','name' => 'South West'),
                array('region_id' => '','code' => 'UKG','name' => 'West Midlands'),
                array('region_id' => '','code' => 'UKE','name' => 'Yorkshire and the Humber'),
            );
        }

        return $result;
    }

    //########################################

    public function getAreas()
    {
        return array(
            Mage_Core_Model_App_Area::AREA_GLOBAL,
            Mage_Core_Model_App_Area::AREA_ADMIN,
            Mage_Core_Model_App_Area::AREA_FRONTEND,
            'adminhtml',
            'crontab',
        );
    }

    public function getAllEventObservers()
    {
        $eventObservers = array();
        foreach ($this->getAreas() as $area) {
            $areaNode = Mage::getConfig()->getNode($area);
            if (empty($areaNode)) {
                continue;
            }

            $areaEvents = $areaNode->events;
            if (empty($areaEvents)) {
                continue;
            }

            foreach ($areaEvents->asArray() as $eventName => $eventData) {
                foreach ($eventData['observers'] as $observerConfig) {
                    $observerName = '#class#::#method#';

                    if (!empty($observerConfig['class'])) {
                        $observerName = str_replace('#class#', $observerConfig['class'], $observerName);
                    }

                    if (!empty($observerConfig['method'])) {
                        $observerName = str_replace('#method#', $observerConfig['method'], $observerName);
                    }

                    $eventObservers[$area][$eventName][] = $observerName;
                }
            }
        }

        return $eventObservers;
    }

    //########################################

    public function getNextMagentoOrderId()
    {
        $orderEntityType = Mage::getSingleton('eav/config')->getEntityType('order');
        $defaultStoreId = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();

        if (!$orderEntityType->getIncrementModel()) {
            return false;
        }

        $entityStoreConfig = Mage::getModel('eav/entity_store')->loadByEntityStore(
            $orderEntityType->getId(), $defaultStoreId
        );

        if (!$entityStoreConfig->getId()) {
            $entityStoreConfig
                ->setEntityTypeId($orderEntityType->getId())
                ->setStoreId($defaultStoreId)
                ->setIncrementPrefix($defaultStoreId)
                ->save();
        }

        $incrementInstance = Mage::getModel($orderEntityType->getIncrementModel())
            ->setPrefix($entityStoreConfig->getIncrementPrefix())
            ->setPadLength($orderEntityType->getIncrementPadLength())
            ->setPadChar($orderEntityType->getIncrementPadChar())
            ->setLastId($entityStoreConfig->getIncrementLastId())
            ->setEntityTypeId($entityStoreConfig->getEntityTypeId())
            ->setStoreId($entityStoreConfig->getStoreId());

        return $incrementInstance->getNextId();
    }

    //########################################

    public function clearMenuCache()
    {
        Mage::app()->getCache()->clean(
            Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            array(Mage_Adminhtml_Block_Page_Menu::CACHE_TAGS)
        );
    }

    public function clearCache()
    {
        Mage::app()->getCache()->clean(Zend_Cache::CLEANING_MODE_ALL);
    }

    //########################################
}
