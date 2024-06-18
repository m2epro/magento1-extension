<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2018 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Marketplace_Synchronization
{
    const LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME = 1800; // 30 min

    /** @var Ess_M2ePro_Model_Marketplace */
    protected $_marketplace = null;

    /** @var Ess_M2ePro_Model_Lock_Item_Manager */
    protected $_lockItemManager = null;

    /** @var Ess_M2ePro_Model_Lock_Item_Progress */
    protected $_progressManager = null;

    /** @var Ess_M2ePro_Model_Synchronization_Log  */
    protected $_synchronizationLog = null;

    //########################################

    public function setMarketplace(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $this->_marketplace = $marketplace;
        return $this;
    }

    //########################################

    public function isLocked()
    {
        if (!$this->getLockItemManager()->isExist()) {
            return false;
        }

        if ($this->getLockItemManager()->isInactiveMoreThanSeconds(self::LOCK_ITEM_MAX_ALLOWED_INACTIVE_TIME)) {
            $this->getLockItemManager()->remove();
            return false;
        }

        return true;
    }

    //########################################

    public function process()
    {
        $this->getLockItemManager()->create();

        $this->getProgressManager()->setPercentage(0);

        $this->processDetails();

        $this->getProgressManager()->setPercentage(10);

        $this->processCategories();

        $this->getProgressManager()->setPercentage(30);

        if ($this->getEbayMarketplace()->isEpidEnabled()) {
            $this->processEpids();
        }

        $this->getProgressManager()->setPercentage(70);

        if ($this->getEbayMarketplace()->isKtypeEnabled()) {
            $this->processKtypes();
        }

        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');

        $this->getProgressManager()->setPercentage(100);

        $this->getLockItemManager()->remove();
    }

    //########################################

    protected function processDetails()
    {
        $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector(
            'marketplace', 'get', 'info',
            array('include_details' => 1), 'info',
            $this->_marketplace->getId(), null
        );

        $dispatcherObj->process($connectorObj);
        $details = $connectorObj->getResponseData();

        if ($details === null) {
            return;
        }

        $details['details']['last_update'] = $details['last_update'];
        $details = $details['details'];

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $dbHelper = Mage::helper('M2ePro/Module_Database_Structure');

        $tableMarketplaces = $dbHelper->getTableNameWithPrefix('m2epro_ebay_dictionary_marketplace');
        $tableShipping = $dbHelper->getTableNameWithPrefix('m2epro_ebay_dictionary_shipping');

        // Save marketplaces
        // ---------------------------------------
        $connWrite->delete($tableMarketplaces, array('marketplace_id = ?' => $this->_marketplace->getId()));

        $helper = Mage::helper('M2ePro/Data');

        $insertData = array(
            'marketplace_id'                  => $this->_marketplace->getId(),
            'client_details_last_update_date' => isset($details['last_update']) ? $details['last_update'] : null,
            'server_details_last_update_date' => isset($details['last_update']) ? $details['last_update'] : null,
            'dispatch'                        => $helper->jsonEncode($details['dispatch']),
            'packages'                        => $helper->jsonEncode($details['packages']),
            'return_policy'                   => $helper->jsonEncode($details['return_policy']),
            'listing_features'                => $helper->jsonEncode($details['listing_features']),
            'payments'                        => $helper->jsonEncode($details['payments']),
            'shipping_locations'              => $helper->jsonEncode($details['shipping_locations']),
            'shipping_locations_exclude'      => $helper->jsonEncode($details['shipping_locations_exclude']),
            'tax_categories'                  => $helper->jsonEncode($details['tax_categories']),
        );

        if (isset($details['additional_data'])) {
            $insertData['additional_data'] = $helper->jsonEncode($details['additional_data']);
        }

        unset($details['categories_version']);
        $connWrite->insert($tableMarketplaces, $insertData);
        // ---------------------------------------

        // Save shipping
        // ---------------------------------------
        $connWrite->delete($tableShipping, array('marketplace_id = ?' => $this->_marketplace->getId()));

        foreach ($details['shipping'] as $data) {
            $insertData = array(
                'marketplace_id'   => $this->_marketplace->getId(),
                'ebay_id'          => $data['ebay_id'],
                'title'            => $data['title'],
                'category'         => $helper->jsonEncode($data['category']),
                'is_flat'          => $data['is_flat'],
                'is_calculated'    => $data['is_calculated'],
                'is_international' => $data['is_international'],
                'data'             => $helper->jsonEncode($data['data']),
            );
            $connWrite->insert($tableShipping, $insertData);
        }

        // ---------------------------------------
    }

    protected function processCategories()
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $tableCategories = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix(
            'm2epro_ebay_dictionary_category'
        );

        $connWrite->delete($tableCategories, array('marketplace_id = ?' => $this->_marketplace->getId()));
        Mage::helper('M2ePro/Component_Ebay_Category')->removeEbayRecent();

        $partNumber = 1;

        for ($i = 0; $i < 100; $i++) {
            $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
            $connectorObj  = $dispatcherObj->getVirtualConnector(
                'marketplace', 'get', 'categories',
                array('part_number' => $partNumber),
                null, $this->_marketplace->getId()
            );

            $dispatcherObj->process($connectorObj);
            $response = $connectorObj->getResponseData();

            if ($response === null || empty($response['data'])) {
                break;
            }

            /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
            $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
            $tableCategories = Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_ebay_dictionary_category');

            $categoriesCount = count($response['data']);
            $insertData      = array();

            $helper = Mage::helper('M2ePro/Data');

            for ($categoryIndex = 0; $categoryIndex < $categoriesCount; $categoryIndex++) {
                $data = $response['data'][$categoryIndex];

                $insertData[] = array(
                    'marketplace_id'     => $this->_marketplace->getId(),
                    'category_id'        => $data['category_id'],
                    'parent_category_id' => $data['parent_id'],
                    'title'              => $data['title'],
                    'path'               => $data['path'],
                    'is_leaf'            => $data['is_leaf'],
                    'features'           => ($data['is_leaf'] ? $helper->jsonEncode($data['features']) : null)
                );

                if (count($insertData) >= 100 || $categoryIndex >= ($categoriesCount - 1)) {
                    $connWrite->insertMultiple($tableCategories, $insertData);
                    $insertData = array();
                }
            }

            $partNumber = $response['next_part'];

            if ($partNumber === null) {
                break;
            }
        }
    }

    protected function processEpids()
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMotorsEpids = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix(
            'm2epro_ebay_dictionary_motor_epid'
        );

        $helper = Mage::helper('M2ePro/Component_Ebay_Motors');
        $scope = $helper->getEpidsScopeByType(
            $helper->getEpidsTypeByMarketplace(
                $this->_marketplace->getId()
            )
        );

        $connWrite->delete(
            $tableMotorsEpids,
            array(
                'is_custom = ?' => 0,
                'scope = ?'     => $scope
            )
        );

        $partNumber = 1;

        for ($i = 0; $i < 100; $i++) {
            $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
            $connectorObj = $dispatcherObj->getVirtualConnector(
                'marketplace', 'get', 'motorsEpids',
                array(
                    'marketplace' => $this->_marketplace->getNativeId(),
                    'part_number' => $partNumber
                )
            );

            $dispatcherObj->process($connectorObj);
            $response = $connectorObj->getResponseData();

            if ($response === null || empty($response['data'])) {
                break;
            }

            $totalCountItems = count($response['data']['items']);
            if ($totalCountItems <= 0) {
                return;
            }

            /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
            $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
            $tableMotorsEpids = Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_ebay_dictionary_motor_epid');

            $temporaryIds   = array();
            $itemsForInsert = array();

            $helper = Mage::helper('M2ePro/Component_Ebay_Motors');
            $scope = $helper->getEpidsScopeByType(
                $helper->getEpidsTypeByMarketplace(
                    $this->_marketplace->getId()
                )
            );

            for ($epidIndex = 0; $epidIndex < $totalCountItems; $epidIndex++) {
                $item = $response['data']['items'][$epidIndex];
                $temporaryIds[] = $item['ePID'];

                $itemsForInsert[] = array(
                    'epid'         => $item['ePID'],
                    'product_type' => (int)$item['product_type'],
                    'make'         => $item['Make'],
                    'model'        => $item['Model'],
                    'year'         => $item['Year'],
                    'trim'         => (isset($item['Trim']) ? $item['Trim'] : null),
                    'engine'       => (isset($item['Engine']) ? $item['Engine'] : null),
                    'submodel'     => (isset($item['Submodel']) ? $item['Submodel'] : null),
                    'street_name'  => (isset($item['StreetName']) ? $item['StreetName'] : null),
                    'scope'        => $scope
                );

                if (count($itemsForInsert) >= 100 || $epidIndex >= ($totalCountItems - 1)) {
                    $connWrite->insertMultiple($tableMotorsEpids, $itemsForInsert);
                    $connWrite->delete(
                        $tableMotorsEpids, array('is_custom = ?' => 1,
                        'epid IN (?)'   => $temporaryIds)
                    );
                    $itemsForInsert = $temporaryIds = array();
                }
            }

            $partNumber = $response['next_part'];

            if ($partNumber === null) {
                break;
            }
        }
    }

    protected function processKtypes()
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMotorsKtypes = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_dictionary_motor_ktype');

        $connWrite->delete($tableMotorsKtypes, '`is_custom` = 0');

        $partNumber = 1;

        for ($i = 0; $i < 100; $i++) {
            $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
            $connectorObj = $dispatcherObj->getVirtualConnector(
                'marketplace', 'get', 'motorsKtypes',
                array('part_number' => $partNumber),
                null, $this->_marketplace->getId()
            );

            $dispatcherObj->process($connectorObj);
            $response = $connectorObj->getResponseData();

            if ($response === null || empty($response['data'])) {
                break;
            }

            $totalCountItems = count($response['data']['items']);
            if ($totalCountItems <= 0) {
                return;
            }

            /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
            $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
            $tableMotorsKtype = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix(
                'm2epro_ebay_dictionary_motor_ktype'
            );

            $temporaryIds   = array();
            $itemsForInsert = array();

            for ($ktypeIndex = 0; $ktypeIndex < $totalCountItems; $ktypeIndex++) {
                $item = $response['data']['items'][$ktypeIndex];

                $temporaryIds[] = (int)$item['ktype'];
                $itemsForInsert[] = array(
                    'ktype'          => (int)$item['ktype'],
                    'make'           => $item['make'],
                    'model'          => $item['model'],
                    'variant'        => $item['variant'],
                    'body_style'     => $item['body_style'],
                    'type'           => $item['type'],
                    'from_year'      => (int)$item['from_year'],
                    'to_year'        => (int)$item['to_year'],
                    'engine'         => $item['engine'],
                );

                if (count($itemsForInsert) >= 100 || $ktypeIndex >= ($totalCountItems - 1)) {
                    $connWrite->insertMultiple($tableMotorsKtype, $itemsForInsert);
                    $connWrite->delete(
                        $tableMotorsKtype, array('is_custom = ?' => 1,
                        'ktype IN (?)'  => $temporaryIds)
                    );
                    $itemsForInsert = $temporaryIds = array();
                }
            }

            $partNumber = $response['next_part'];

            if ($partNumber === null) {
                break;
            }
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Marketplace
     */
    protected function getEbayMarketplace()
    {
        return $this->_marketplace->getChildObject();
    }

    //########################################

    public function getLockItemManager()
    {
        if ($this->_lockItemManager !== null) {
            return $this->_lockItemManager;
        }

        return $this->_lockItemManager = Mage::getModel(
            'M2ePro/Lock_Item_Manager',
            array(
                'nick' => Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_SYNCHRONIZATION_LOCK_ITEM_NICK
            )
        );
    }

    public function getProgressManager()
    {
        if ($this->_progressManager !== null) {
            return $this->_progressManager;
        }

        return $this->_progressManager = Mage::getModel(
            'M2ePro/Lock_Item_Progress', array(
                'lock_item_manager' => $this->getLockItemManager(),
                'progress_nick'     => $this->_marketplace->getTitle() . ' Marketplace',
            )
        );
    }

    public function getLog()
    {
        if ($this->_synchronizationLog !== null) {
            return $this->_synchronizationLog;
        }

        $this->_synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->_synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
        $this->_synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_MARKETPLACES);

        return $this->_synchronizationLog;
    }

    //########################################
}
