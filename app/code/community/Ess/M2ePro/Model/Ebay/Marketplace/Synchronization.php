<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2018 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Marketplace_Synchronization
{
    /** @var Ess_M2ePro_Model_Marketplace */
    private $marketplace = NULL;

    /** @var Ess_M2ePro_Model_Lock_Item_Progress */
    private $progressManager = NULL;

    //########################################

    public function setMarketplace(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $this->marketplace = $marketplace;
        return $this;
    }

    public function setProgressManager(Ess_M2ePro_Model_Lock_Item_Progress $progressManager)
    {
        $this->progressManager = $progressManager;
        return $this;
    }

    //########################################

    public function process()
    {
        $this->progressManager->setPercentage(0);

        $this->processDetails();

        $this->progressManager->setPercentage(10);

        $this->processCategories();

        $this->progressManager->setPercentage(30);

        if ($this->getEbayMarketplace()->isEpidEnabled()) {
            $this->processEpids();
        }

        $this->progressManager->setPercentage(70);

        if ($this->getEbayMarketplace()->isKtypeEnabled()) {
            $this->processKtypes();
        }

        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');

        $this->progressManager->setPercentage(100);
    }

    //########################################

    private function processDetails()
    {
        $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector('marketplace','get','info',
            array('include_details' => 1),'info',
            $this->marketplace->getId(),NULL);

        $dispatcherObj->process($connectorObj);
        $details = $connectorObj->getResponseData();

        if (is_null($details)) {
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
        $connWrite->delete($tableMarketplaces, array('marketplace_id = ?' => $this->marketplace->getId()));

        $helper = Mage::helper('M2ePro/Data');

        $insertData = array(
            'marketplace_id'                  => $this->marketplace->getId(),
            'client_details_last_update_date' => isset($details['last_update']) ? $details['last_update'] : NULL,
            'server_details_last_update_date' => isset($details['last_update']) ? $details['last_update'] : NULL,
            'dispatch'                        => $helper->jsonEncode($details['dispatch']),
            'packages'                        => $helper->jsonEncode($details['packages']),
            'return_policy'                   => $helper->jsonEncode($details['return_policy']),
            'listing_features'                => $helper->jsonEncode($details['listing_features']),
            'payments'                        => $helper->jsonEncode($details['payments']),
            'shipping_locations'              => $helper->jsonEncode($details['shipping_locations']),
            'shipping_locations_exclude'      => $helper->jsonEncode($details['shipping_locations_exclude']),
            'tax_categories'                  => $helper->jsonEncode($details['tax_categories']),
            'charities'                       => $helper->jsonEncode($details['charities']),
        );

        if (isset($details['additional_data'])) {
            $insertData['additional_data'] = $helper->jsonEncode($details['additional_data']);
        }

        unset($details['categories_version']);
        $connWrite->insert($tableMarketplaces, $insertData);
        // ---------------------------------------

        // Save shipping
        // ---------------------------------------
        $connWrite->delete($tableShipping, array('marketplace_id = ?' => $this->marketplace->getId()));

        foreach ($details['shipping'] as $data) {
            $insertData = array(
                'marketplace_id'   => $this->marketplace->getId(),
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

    private function processCategories()
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $tableCategories = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix(
            'm2epro_ebay_dictionary_category'
        );

        $connWrite->delete($tableCategories,array('marketplace_id = ?' => $this->marketplace->getId()));

        $partNumber = 1;

        for ($i = 0; $i < 100; $i++) {
            $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
            $connectorObj  = $dispatcherObj->getVirtualConnector('marketplace','get','categories',
                array('part_number' => $partNumber),
                NULL,$this->marketplace->getId());

            $dispatcherObj->process($connectorObj);
            $response = $connectorObj->getResponseData();

            if (is_null($response) || empty($response['data'])) {
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
                    'marketplace_id'     => $this->marketplace->getId(),
                    'category_id'        => $data['category_id'],
                    'parent_category_id' => $data['parent_id'],
                    'title'              => $data['title'],
                    'path'               => $data['path'],
                    'is_leaf'            => $data['is_leaf'],
                    'features'           => ($data['is_leaf'] ? $helper->jsonEncode($data['features']) : NULL)
                );

                if (count($insertData) >= 100 || $categoryIndex >= ($categoriesCount - 1)) {
                    $connWrite->insertMultiple($tableCategories, $insertData);
                    $insertData = array();
                }
            }

            $partNumber = $response['next_part'];

            if (is_null($partNumber)) {
                break;
            }
        }
    }

    private function processEpids()
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMotorsEpids = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix(
            'm2epro_ebay_dictionary_motor_epid'
        );

        $helper = Mage::helper('M2ePro/Component_Ebay_Motors');
        $scope = $helper->getEpidsScopeByType($helper->getEpidsTypeByMarketplace(
            $this->marketplace->getId()
        ));

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
            $connectorObj = $dispatcherObj->getVirtualConnector('marketplace','get','motorsEpids',
                array(
                    'marketplace' => $this->marketplace->getNativeId(),
                    'part_number' => $partNumber
                ));

            $dispatcherObj->process($connectorObj);
            $response = $connectorObj->getResponseData();

            if (is_null($response) || empty($response['data'])) {
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
            $scope = $helper->getEpidsScopeByType($helper->getEpidsTypeByMarketplace(
                $this->marketplace->getId()
            ));

            for ($epidIndex = 0; $epidIndex < $totalCountItems; $epidIndex++) {

                $item = $response['data']['items'][$epidIndex];
                $temporaryIds[] = $item['ePID'];

                $itemsForInsert[] = array(
                    'epid'         => $item['ePID'],
                    'product_type' => (int)$item['product_type'],
                    'make'         => $item['Make'],
                    'model'        => $item['Model'],
                    'year'         => $item['Year'],
                    'trim'         => (isset($item['Trim']) ? $item['Trim'] : NULL),
                    'engine'       => (isset($item['Engine']) ? $item['Engine'] : NULL),
                    'submodel'     => (isset($item['Submodel']) ? $item['Submodel'] : NULL),
                    'scope'        => $scope
                );

                if (count($itemsForInsert) >= 100 || $epidIndex >= ($totalCountItems - 1)) {

                    $connWrite->insertMultiple($tableMotorsEpids, $itemsForInsert);
                    $connWrite->delete($tableMotorsEpids, array('is_custom = ?' => 1,
                                                                'epid IN (?)'   => $temporaryIds));
                    $itemsForInsert = $temporaryIds = array();
                }
            }

            $partNumber = $response['next_part'];

            if (is_null($partNumber)) {
                break;
            }
        }
    }

    private function processKtypes()
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMotorsKtypes = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_dictionary_motor_ktype');

        $connWrite->delete($tableMotorsKtypes, '`is_custom` = 0');

        $partNumber = 1;

        for ($i = 0; $i < 100; $i++) {
            $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
            $connectorObj = $dispatcherObj->getVirtualConnector('marketplace','get','motorsKtypes',
                array('part_number' => $partNumber),
                NULL,$this->marketplace->getId());

            $dispatcherObj->process($connectorObj);
            $response = $connectorObj->getResponseData();

            if (is_null($response) || empty($response['data'])) {
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
                    $connWrite->delete($tableMotorsKtype, array('is_custom = ?' => 1,
                                                                'ktype IN (?)'  => $temporaryIds));
                    $itemsForInsert = $temporaryIds = array();
                }
            }

            $partNumber = $response['next_part'];

            if (is_null($partNumber)) {
                break;
            }
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Marketplace
     */
    private function getEbayMarketplace()
    {
        return $this->marketplace->getChildObject();
    }

    //########################################
}