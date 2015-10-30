<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces_Categories
    extends Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces_Abstract
{
    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/categories/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Categories';
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 25;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 100;
    }

    //########################################

    protected function initialize()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues(
            Ess_M2ePro_Helper_Component_Ebay_Category_Ebay::CACHE_TAG
        );
    }

    protected function performActions()
    {
        $partNumber = 1;
        $params     = $this->getParams();

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component_Ebay')
                            ->getObject('Marketplace', (int)$params['marketplace_id']);

        $this->deleteAllCategories($marketplace);

        $this->getActualOperationHistory()->addText('Starting Marketplace "'.$marketplace->getTitle().'"');

        for ($i = 0; $i < 100; $i++) {
            $this->getActualLockItem()->setPercents($this->getPercentsStart());

            $this->getActualOperationHistory()->addTimePoint(__METHOD__.'get'.$marketplace->getId(),
                'Get Categories from eBay, part № ' . $partNumber);
            $response = $this->receiveFromEbay($marketplace, $partNumber);
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

    protected function receiveFromEbay(Ess_M2ePro_Model_Marketplace $marketplace, $partNumber)
    {
        $dispatcherObj = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
        $connectorObj  = $dispatcherObj->getVirtualConnector('marketplace','get','categories',
                                                            array('part_number' => $partNumber),
                                                            NULL,$marketplace->getId());

        $response = $dispatcherObj->process($connectorObj);

        if (is_null($response) || empty($response['data'])) {
            $response = array();
        }

        $dataCount = isset($response['data']) ? count($response['data']) : 0;
        $this->getActualOperationHistory()->addText("Total received Categories from eBay: {$dataCount}");

        return $response;
    }

    protected function deleteAllCategories(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');

        $connWrite->delete($tableCategories,array('marketplace_id = ?' => $marketplace->getId()));
    }

    protected function saveCategoriesToDb(Ess_M2ePro_Model_Marketplace $marketplace, array $categories)
    {
        if (count($categories) <= 0) {
            return;
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');

        $iteration            = 0;
        $iterationsForOneStep = 1000;
        $categoriesCount      = count($categories);
        $percentsForOneStep   = ($this->getPercentsInterval()/2) / ($categoriesCount/$iterationsForOneStep);
        $insertData           = array();

        for ($i = 0; $i < $categoriesCount; $i++) {

            $data = $categories[$i];

            $insertData[] = array(
                'marketplace_id'     => $marketplace->getId(),
                'category_id'        => $data['category_id'],
                'parent_category_id' => $data['parent_id'],
                'title'              => $data['title'],
                'path'               => $data['path'],
                'is_leaf'            => $data['is_leaf'],
                'features'           => ($data['is_leaf'] ? json_encode($data['features']) : NULL)
            );

            if (count($insertData) >= 100 || $i >= ($categoriesCount - 1)) {
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
        // The "Categories" Action for eBay Site: "%mrk%" has been successfully completed.

        $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
            'The "Categories" Action for eBay Site: "%mrk%" has been successfully completed.',
            array('mrk' => $marketplace->getTitle())
        );

        $this->getLog()->addMessage($tempString,
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
    }

    //########################################
}