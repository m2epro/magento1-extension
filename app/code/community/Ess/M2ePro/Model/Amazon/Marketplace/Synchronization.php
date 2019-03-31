<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2018 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Marketplace_Synchronization
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

        $this->progressManager->setPercentage(60);

        $this->processSpecifics();

        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');

        $this->progressManager->setPercentage(100);
    }

    //########################################

    private function processDetails()
    {
        $dispatcherObj = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
        $connectorObj  = $dispatcherObj->getVirtualConnector('marketplace','get','info',
            array('include_details' => true,
                  'marketplace' => $this->marketplace->getNativeId()),
            'info',NULL);

        $dispatcherObj->process($connectorObj);
        $details = $connectorObj->getResponseData();

        if (is_null($details)) {
            return;
        }

        $details['details']['last_update'] = $details['last_update'];
        $details = $details['details'];

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMarketplaces = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_dictionary_marketplace');

        $connWrite->delete($tableMarketplaces,array('marketplace_id = ?' => $this->marketplace->getId()));

        $helper = Mage::helper('M2ePro/Data');

        $data = array(
            'marketplace_id' => $this->marketplace->getId(),
            'client_details_last_update_date' => isset($details['last_update']) ? $details['last_update'] : NULL,
            'server_details_last_update_date' => isset($details['last_update']) ? $details['last_update'] : NULL,
            'product_data' => isset($details['product_data']) ? $helper->jsonEncode($details['product_data']) : NULL,
        );

        $connWrite->insert($tableMarketplaces, $data);
    }

    private function processCategories()
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $tableCategories = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix(
            'm2epro_amazon_dictionary_category'
        );
        $connWrite->delete($tableCategories, array('marketplace_id = ?' => $this->marketplace->getId()));

        $tableProductData = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix(
            'm2epro_amazon_dictionary_category_product_data'
        );
        $connWrite->delete($tableProductData, array('marketplace_id = ?' => $this->marketplace->getId()));

        $partNumber = 1;

        for ($i = 0; $i < 100; $i++) {
            $dispatcherObj = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
            $connectorObj  = $dispatcherObj->getVirtualConnector('marketplace','get','categories',
                array('part_number' => $partNumber,
                      'marketplace' => $this->marketplace->getNativeId()),
                NULL,NULL);

            $dispatcherObj->process($connectorObj);
            $response = $connectorObj->getResponseData();

            if (is_null($response) || empty($response['data'])) {
                break;
            }

            $insertData = array();

            $helper = Mage::helper('M2ePro');

            for ($categoryIndex = 0; $categoryIndex < count($response['data']); $categoryIndex++) {

                $data = $response['data'][$categoryIndex];

                $isLeaf = $data['is_leaf'];
                $insertData[] = array(
                    'marketplace_id'     => $this->marketplace->getId(),
                    'category_id'        => $data['id'],
                    'parent_category_id' => $data['parent_id'],
                    'browsenode_id'      => ($isLeaf ? $data['browsenode_id'] : NULL),
                    'product_data_nicks' => ($isLeaf ? $helper->jsonEncode($data['product_data_nicks']) : NULL),
                    'title'              => $data['title'],
                    'path'               => $data['path'],
                    'keywords'           => ($isLeaf ? $helper->jsonEncode($data['keywords']) : NULL),
                    'is_leaf'            => $isLeaf,
                );

                if (count($insertData) >= 100 || $categoryIndex >= (count($response['data']) - 1)) {
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

    private function processSpecifics()
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $tableSpecifics = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix(
            'm2epro_amazon_dictionary_specific'
        );
        $connWrite->delete($tableSpecifics, array('marketplace_id = ?' => $this->marketplace->getId()));

        $partNumber = 1;

        for ($i = 0; $i < 100; $i++) {
            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
            $connectorObj     = $dispatcherObject->getVirtualConnector('marketplace', 'get', 'specifics',
                array('part_number' => $partNumber,
                      'marketplace' => $this->marketplace->getNativeId()));

            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();

            if (is_null($response) || empty($response['data'])) {
                break;
            }

            $insertData = array();

            $helper = Mage::helper('M2ePro/Data');

            for ($specificIndex = 0; $specificIndex < count($response['data']); $specificIndex++) {

                $data = $response['data'][$specificIndex];

                $insertData[] = array(
                    'marketplace_id'     => $this->marketplace->getId(),
                    'specific_id'        => $data['id'],
                    'parent_specific_id' => $data['parent_id'],
                    'product_data_nick'  => $data['product_data_nick'],
                    'title'              => $data['title'],
                    'xml_tag'            => $data['xml_tag'],
                    'xpath'              => $data['xpath'],
                    'type'               => (int)$data['type'],
                    'values'             => $helper->jsonEncode($data['values']),
                    'recommended_values' => $helper->jsonEncode($data['recommended_values']),
                    'params'             => $helper->jsonEncode($data['params']),
                    'data_definition'    => $helper->jsonEncode($data['data_definition']),
                    'min_occurs'         => (int)$data['min_occurs'],
                    'max_occurs'         => (int)$data['max_occurs']
                );

                if (count($insertData) >= 100 || $specificIndex >= (count($response['data']) - 1)) {
                    $connWrite->insertMultiple($tableSpecifics, $insertData);
                    $insertData = array();
                }
            }

            $partNumber = $response['next_part'];

            if (is_null($partNumber)) {
                break;
            }
        }
    }

    //########################################
}