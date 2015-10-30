<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Marketplaces_Categories
    extends Ess_M2ePro_Model_Amazon_Synchronization_Marketplaces_Abstract
{
    //########################################

    protected function getNick()
    {
        return '/categories/';
    }

    protected function getTitle()
    {
        return 'Categories';
    }

    // ---------------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //########################################

    protected function performActions()
    {
        $partNumber = 1;
        $params     = $this->getParams();

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component_Amazon')
                            ->getObject('Marketplace', (int)$params['marketplace_id']);

        $this->deleteAllCategories($marketplace);
        $this->deleteAllProductDataInfo($marketplace);

        $this->getActualOperationHistory()->addText('Starting Marketplace "'.$marketplace->getTitle().'"');

        for ($i = 0; $i < 100; $i++) {
            $this->getActualLockItem()->setPercents($this->getPercentsStart());

            $this->getActualOperationHistory()->addTimePoint(__METHOD__.'get'.$marketplace->getId(),
                'Get Categories from Amazon, part № ' . $partNumber);
            $response = $this->receiveFromAmazon($marketplace, $partNumber);
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$marketplace->getId());

            if (empty($response)) {
                break;
            }

            $this->getActualLockItem()->setStatus(
                'Processing Categories data ('.(int)$partNumber.'/'.(int)$response['total_parts'].')'
            );
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $this->getPercentsInterval()/2);
            $this->getActualLockItem()->activate();

            $this->getActualOperationHistory()->addTimePoint(__METHOD__.'save'.$marketplace->getId(),
                'Save Categories to DB');
            $this->saveCategoriesToDb($marketplace, $response['data']);
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'save'.$marketplace->getId());

            $this->getActualLockItem()->setPercents($this->getPercentsEnd());
            $this->getActualLockItem()->activate();

            $partNumber = $response['next_part'];

            if (is_null($partNumber)) {
                break;
            }
        }

        $this->logSuccessfulOperation($marketplace);
    }

    //########################################

    protected function receiveFromAmazon(Ess_M2ePro_Model_Marketplace $marketplace, $partNumber)
    {
        $dispatcherObj = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
        $connectorObj  = $dispatcherObj->getVirtualConnector('marketplace','get','categories',
                                                             array('part_number' => $partNumber,
                                                                   'marketplace' => $marketplace->getNativeId()),
                                                             NULL,NULL,NULL);

        $response = $dispatcherObj->process($connectorObj);

        if (is_null($response) || empty($response['data'])) {
            $response = array();
        }

        $dataCount = isset($response['data']) ? count($response['data']) : 0;
        $this->getActualOperationHistory()->addText("Total received Categories from Amazon: {$dataCount}");

        return $response;
    }

    protected function deleteAllCategories(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_category');

        $connWrite->delete($tableCategories,array('marketplace_id = ?' => $marketplace->getId()));
    }

    protected function deleteAllProductDataInfo(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableCategories = Mage::getSingleton('core/resource')
            ->getTableName('m2epro_amazon_dictionary_category_product_data');

        $connWrite->delete($tableCategories,array('marketplace_id = ?' => $marketplace->getId()));
    }

    protected function saveCategoriesToDb(Ess_M2ePro_Model_Marketplace $marketplace, array $categories)
    {
        $totalCountCategories = count($categories);
        if ($totalCountCategories <= 0) {
            return;
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_category');

        $iteration            = 0;
        $iterationsForOneStep = 1000;
        $percentsForOneStep   = ($this->getPercentsInterval()/2) / ($totalCountCategories/$iterationsForOneStep);
        $insertData           = array();

        for ($i = 0; $i < $totalCountCategories; $i++) {

            $data = $categories[$i];

            $isLeaf = $data['is_leaf'];
            $insertData[] = array(
                'marketplace_id'     => $marketplace->getId(),
                'category_id'        => $data['id'],
                'parent_category_id' => $data['parent_id'],
                'browsenode_id'      => ($isLeaf ? $data['browsenode_id'] : NULL),
                'product_data_nicks' => ($isLeaf ? json_encode($data['product_data_nicks']) : NULL),
                'title'              => $data['title'],
                'path'               => $data['path'],
                'keywords'           => ($isLeaf ? json_encode($data['keywords']) : NULL),
                'is_leaf'            => $isLeaf,
            );

            if (count($insertData) >= 100 || $i >= ($totalCountCategories - 1)) {
                $connWrite->insertMultiple($tableCategories, $insertData);
                $insertData = array();
            }

            if (++$iteration % $iterationsForOneStep == 0) {
                $percentsShift = ($iteration/$iterationsForOneStep) * $percentsForOneStep;
                $this->getActualLockItem()->setPercents(
                    $this->getPercentsStart() + $this->getPercentsInterval()/2 + $percentsShift
                );
            }
        }
    }

    protected function logSuccessfulOperation(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        // M2ePro_TRANSLATIONS
        // The "Categories" Action for %amazon% Marketplace: "%mrk%" has been successfully completed.

        $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
            'The "Categories" Action for %amazon% Marketplace: "%mrk%" has been successfully completed.',
            array('!amazon' => Mage::helper('M2ePro/Component_Amazon')->getTitle(),
                  'mrk'     => $marketplace->getTitle())
        );

        $this->getLog()->addMessage($tempString,
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
    }

    //########################################
}