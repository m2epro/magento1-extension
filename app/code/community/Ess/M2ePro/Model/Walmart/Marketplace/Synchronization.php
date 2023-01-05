<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2018 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Marketplace_Synchronization
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

        $this->getProgressManager()->setPercentage(60);

        $this->processSpecifics();

        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');

        $this->getProgressManager()->setPercentage(100);

        $this->getLockItemManager()->remove();
    }

    //########################################

    protected function processDetails()
    {
        $dispatcherObj = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');
        $connectorObj  = $dispatcherObj->getVirtualConnector(
            'marketplace', 'get', 'info',
            array('include_details' => true,
                  'marketplace' => $this->_marketplace->getNativeId()),
            'info', null
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
        $tableMarketplaces = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_walmart_dictionary_marketplace');

        $connWrite->delete($tableMarketplaces, array('marketplace_id = ?' => $this->_marketplace->getId()));

        $helper = Mage::helper('M2ePro/Data');

        $data = array(
            'marketplace_id' => $this->_marketplace->getId(),
            'client_details_last_update_date' => isset($details['last_update']) ? $details['last_update'] : null,
            'server_details_last_update_date' => isset($details['last_update']) ? $details['last_update'] : null,
            'product_data'   => isset($details['product_data']) ? $helper->jsonEncode($details['product_data']) : null,
        );

        $connWrite->insert($tableMarketplaces, $data);
    }

    protected function processCategories()
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $tableCategories = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix(
            'm2epro_walmart_dictionary_category'
        );
        $connWrite->delete($tableCategories, array('marketplace_id = ?' => $this->_marketplace->getId()));

        $partNumber = 1;

        for ($i = 0; $i < 100; $i++) {
            $dispatcherObj = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');
            $connectorObj  = $dispatcherObj->getVirtualConnector(
                'marketplace', 'get', 'categories',
                array('part_number' => $partNumber,
                      'marketplace' => $this->_marketplace->getNativeId()),
                null, null
            );

            $dispatcherObj->process($connectorObj);
            $response = $connectorObj->getResponseData();

            if ($response === null || empty($response['data'])) {
                break;
            }

            $insertData = array();

            $helper = Mage::helper('M2ePro');

            for ($categoryIndex = 0; $categoryIndex < count($response['data']); $categoryIndex++) {
                $data = $response['data'][$categoryIndex];

                $isLeaf = $data['is_leaf'];
                $insertData[] = array(
                    'marketplace_id'     => $this->_marketplace->getId(),
                    'category_id'        => $data['id'],
                    'parent_category_id' => $data['parent_id'],
                    'browsenode_id'      => ($isLeaf ? $data['browsenode_id'] : null),
                    'product_data_nicks' => ($isLeaf ? $helper->jsonEncode($data['product_data_nicks']) : null),
                    'title'              => $data['title'],
                    'path'               => $data['path'],
                    'is_leaf'            => $isLeaf,
                );

                if (count($insertData) >= 100 || $categoryIndex >= (count($response['data']) - 1)) {
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

    protected function processSpecifics()
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $tableSpecifics = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix(
            'm2epro_walmart_dictionary_specific'
        );
        $connWrite->delete($tableSpecifics, array('marketplace_id = ?' => $this->_marketplace->getId()));

        $partNumber = 1;

        for ($i = 0; $i < 100; $i++) {
            $dispatcherObject = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');
            $connectorObj     = $dispatcherObject->getVirtualConnector(
                'marketplace', 'get', 'specifics',
                array('part_number' => $partNumber,
                'marketplace' => $this->_marketplace->getNativeId())
            );

            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();

            if ($response === null || empty($response['data'])) {
                break;
            }

            $insertData = array();

            $helper = Mage::helper('M2ePro/Data');

            for ($specificIndex = 0; $specificIndex < count($response['data']); $specificIndex++) {
                $data = $response['data'][$specificIndex];

                $insertData[] = array(
                    'marketplace_id'     => $this->_marketplace->getId(),
                    'specific_id'        => $data['id'],
                    'parent_specific_id' => $data['parent_id'],
                    'product_data_nick'  => $data['product_data_nick'],
                    'title'              => $data['title'],
                    'xml_tag'            => $data['xml_tag'],
                    'xpath'              => $data['xpath'],
                    'type'               => (int)$data['type'],
                    'values'             => $helper->jsonEncode($data['values']),
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

            if ($partNumber === null) {
                break;
            }
        }
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
                'nick' => Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_SYNCHRONIZATION_LOCK_ITEM_NICK
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
        $this->_synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK);
        $this->_synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_MARKETPLACES);

        return $this->_synchronizationLog;
    }

    //########################################
}
