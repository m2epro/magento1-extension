<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Magento extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function getName()
    {
        return 'magento';
    }

    public function getVersion($asArray = false)
    {
        $versionString = Mage::getVersion();
        return $asArray ? explode('.',$versionString) : $versionString;
    }

    public function getRevision()
    {
        return 'undefined';
    }

    // ########################################

    public function getEditionName()
    {
        if ($this->isProfessionalEdition()) {
            return 'professional';
        }
        if ($this->isEnterpriseEdition()) {
            return 'enterprise';
        }
        if ($this->isCommunityEdition()) {
            return 'community';
        }

        if ($this->isGoUsEdition()) {
            return 'magento go US';
        }
        if ($this->isGoUkEdition()) {
            return 'magento go UK';
        }
        if ($this->isGoAuEdition()) {
            return 'magento go AU';
        }

        if ($this->isGoEdition()) {
            return 'magento go';
        }

        return 'undefined';
    }

    //----------------------------------------

    public function isGoEdition()
    {
        return class_exists('Saas_Db',false);
    }

    public function isProfessionalEdition()
    {
        return Mage::getConfig()->getModuleConfig('Enterprise_Enterprise') &&
               !Mage::getConfig()->getModuleConfig('Enterprise_AdminGws') &&
               !Mage::getConfig()->getModuleConfig('Enterprise_Checkout') &&
               !Mage::getConfig()->getModuleConfig('Enterprise_Customer');
    }

    public function isEnterpriseEdition()
    {
        return Mage::getConfig()->getModuleConfig('Enterprise_Enterprise') &&
               Mage::getConfig()->getModuleConfig('Enterprise_AdminGws') &&
               Mage::getConfig()->getModuleConfig('Enterprise_Checkout') &&
               Mage::getConfig()->getModuleConfig('Enterprise_Customer');
    }

    public function isCommunityEdition()
    {
        return !$this->isGoEdition() &&
               !$this->isProfessionalEdition() &&
               !$this->isEnterpriseEdition();
    }

    //----------------------------------------

    public function isGoUsEdition()
    {
        if (!$this->isGoEdition()) {
            return false;
        }

        $region = Mage::getConfig()->getOptions()->getTenantRegion();
        return strtolower($region) == 'en_us';
    }

    public function isGoUkEdition()
    {
        if (!$this->isGoEdition()) {
            return false;
        }

        $region = Mage::getConfig()->getOptions()->getTenantRegion();
        return strtolower($region) == 'en_gb';
    }

    public function isGoAuEdition()
    {
        if (!$this->isGoEdition()) {
            return false;
        }

        $region = Mage::getConfig()->getOptions()->getTenantRegion();
        return strtolower($region) == 'en_au';
    }

    //----------------------------------------

    public function isGoCustomEdition()
    {
        if (!$this->isGoEdition()) {
            return false;
        }

        return $this->isGoUsEdition() ||
               $this->isGoUkEdition() ||
               $this->isGoAuEdition();
    }

    // ########################################

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

    // ########################################

    public function getModules()
    {
        return array_keys((array)Mage::getConfig()->getNode('modules')->children());
    }

    public function getConflictedModules()
    {
        $modules = Mage::getConfig()->getNode('modules')->asArray();

        $conflictedModules = array(
            '/TBT_Enhancedgrid/i' => '',
            '/warp/i' => '',
            '/Auctionmaid_/i' => '',

            '/Exactor_Tax/i' => '',
            '/Exactory_Core/i' => '',
            '/Exactor_ExactorSettings/i' => '',
            '/Exactor_Sales/i' => '',
            '/Aoe_AsyncCache/i' => '',
            '/Idev_OneStepCheckout/i' => '',

            '/Mercent_Sales/i' => '',
            '/Webtex_Fba/i' => 'Breaks creation Amazon Fba orders.',

            '/MW_FreeGift/i' => 'last item in combined amazon orders has zero price
                                 (observing event sales_quote_product_add_after)',

            '/Unirgy_Dropship/i' => 'Rewrites stock item and in some cases return
                                     always in stock for all products',

            '/Aitoc_Aitquantitymanager/i' => 'Stock management conflicts.',

            '/Eternalsoft_Ajaxcart/i' => 'Broke some ajax responses.',
            '/Amasty_Shiprestriction/i' => '"Please specify a shipping method" error for some orders.',
            '/RicoNeitzel_PaymentFilter/i' => '"The requested payment method is not available" error',
            '/Mxperts_NoRegion/i' => 'Error about empty billing address information',
            '/MageWorx_DeliveryZone/i' => 'Shipping price is 0 in magento order',

            '/Netzarbeiter_Cache/i' => 'Adding product step by circle.',

            '/Netzarbeiter_LoginCatalog/i' => 'Cron problem. [Model_Observer->_redirectToLoginPage()]',
            '/Elsner_Loginonly/i'          => 'Cron problem. [Model_Observer->_redirectToLoginPage()]'
        );

        $result = array();
        foreach($conflictedModules as $expression=>$description) {

            foreach ($modules as $module => $data) {
                if (preg_match($expression, $module)) {
                    $result[$module] = array_merge($data, array('description'=>$description));
                }
            }
        }

        return $result;
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

    //----------------------------------------

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

    // ########################################

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
        $collection->addFieldToFilter('executed_at',array('gt'=>$minDateTime));

        return $collection->getSize() > 0;
    }

    public function getBaseUrl()
    {
        return str_replace('index.php/','',Mage::getBaseUrl());
    }

    public function getLocale()
    {
        $localeComponents = explode('_' , Mage::app()->getLocale()->getLocale());
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
        foreach($unsortedCountries as $country) {
            $unsortedCountriesNames[] = $country['name'];
        }

        sort($unsortedCountriesNames, SORT_STRING);

        $sortedCountries = array();
        foreach($unsortedCountriesNames as $name) {
            foreach($unsortedCountries as $country) {
                if ($country['name'] == $name) {
                    $sortedCountries[] = $country;
                    break;
                }
            }
        }

        return $sortedCountries;
    }

    public function addGlobalNotification($title,
                                          $description,
                                          $type = Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL,
                                          $url = NULL)
    {
        $dataForAdd = array(
            'title' => $title,
            'description' => $description,
            'url' => !is_null($url) ? $url : 'http://m2epro.com/?'.sha1($title),
            'severity' => $type,
            'date_added' => now()
        );

        Mage::getModel('adminnotification/inbox')->parse(array($dataForAdd));
    }

    // ########################################

    public function getRewrites($entity = 'models')
    {
        $config = Mage::getConfig()->getNode('global/' . $entity)->children();
        $rewrites = array();

        foreach ($config as $node) {
            foreach ($node->rewrite as $rewriteNode) {
                foreach ($rewriteNode->children() as $rewrite) {
                    if (!$node->class) {
                        continue;
                    }

                    $classNameParts = explode('_', $rewrite->getName());
                    foreach ($classNameParts as &$part) {
                        $part = strtolower($part);
                        $part{0} = strtoupper($part{0});
                    }

                    $classNameParts = array_merge(array($node->class), $classNameParts);

                    $rewrites[] = array(
                        'from' => implode('_', $classNameParts),
                        'to'   => (string)$rewrite
                    );
                }
            }
        }

        return $rewrites;
    }

    //-----------------------------------------

    public function getLocalPoolOverwrites()
    {
        $paths = array(
            Mage::getBaseDir().'/app/code/local/Mage',
            Mage::getBaseDir().'/app/code/local/Zend',
            Mage::getBaseDir().'/app/code/local/Ess',
            Mage::getBaseDir().'/app/code/local/Varien',
        );

        $overwrites = array();
        foreach ($paths as $path) {

            if (!is_dir($path)) {
                continue;
            }

            $directoryIterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
            $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

            /** @var SplFileInfo $splFileObj */
            foreach ($iterator as $splFileObj) {
                $splFileObj->isFile() && $overwrites[] = $splFileObj->getRealPath();
            }
        }

        $result = array();
        foreach ($overwrites as $item) {
            $this->isOriginalFileExists($item) && $result[] = str_replace(Mage::getBaseDir().DS, '', $item);
        }

        return $result;
    }

    private function isOriginalFileExists($overwritedFilename)
    {
        $unixFormattedPath = str_replace('\\', '/', $overwritedFilename);

        $isOriginalCoreFileExist      = is_file(str_replace('/local/', '/core/', $unixFormattedPath));
        $isOriginalCommunityFileExist = is_file(str_replace('/local/', '/community/', $unixFormattedPath));
        $isOriginalLibFileExist       = is_file(str_replace('app/code/local/', 'lib/', $unixFormattedPath));

        return $isOriginalCoreFileExist || $isOriginalCommunityFileExist || $isOriginalLibFileExist;
    }

    // ########################################

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

    // ########################################

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

    public function isMagentoOrderIdUsed($orderId)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select    = $connRead->select();

        $table = Mage::getModel('sales/order')->getResource()->getMainTable();

        $select->from($table, 'entity_id')->where('increment_id = :increment_id');

        $result = $connRead->fetchOne($select, array(':increment_id' => $orderId));
        if ($result > 0) {
            return true;
        }

        return false;
    }

    // ########################################

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

    // ########################################
}