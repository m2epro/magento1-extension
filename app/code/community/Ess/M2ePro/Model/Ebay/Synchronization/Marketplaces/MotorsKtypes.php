<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces_MotorsKtypes
    extends Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces_Abstract
{
    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/motors_ktypes/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Parts Compatibility';
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 0;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 100;
    }

    //########################################

    protected function isPossibleToRun()
    {
        if (!parent::isPossibleToRun()) {
            return false;
        }

        $params = $this->getParams();

        return Mage::helper('M2ePro/Component_Ebay_Motors')
                    ->isMarketplaceSupportsKtype($params['marketplace_id']);
    }

    protected function performActions()
    {
        $params = $this->getParams();

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component_Ebay')
            ->getObject('Marketplace', (int)$params['marketplace_id']);

        $partNumber = 1;
        $this->deleteAllKtypes();

        for ($i = 0; $i < 100; $i++) {

            $this->getActualLockItem()->setPercents($this->getPercentsStart());

            $this->getActualOperationHistory()->addTimePoint(__METHOD__.'get'.$marketplace->getId(),
                                                             'Get kTypes from eBay');
            $response = $this->receiveFromEbay($marketplace, $partNumber);
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$marketplace->getId());

            if (empty($response)) {
                break;
            }

            $this->getActualLockItem()->setStatus(
                'Processing kTypes data ('.(int)$partNumber.'/'.(int)$response['total_parts'].')'
            );
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $this->getPercentsInterval()/2);
            $this->getActualLockItem()->activate();

            $this->getActualOperationHistory()->addTimePoint(__METHOD__.'save'.$marketplace->getId(),
                                                             'Save kTypes to DB');
            $this->saveKtypesToDb($response['data']);
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
        $connectorObj = $dispatcherObj->getVirtualConnector('marketplace','get','motorsKtypes',
                                                            array('part_number' => $partNumber),
                                                            NULL,$marketplace->getId());

        $response = $dispatcherObj->process($connectorObj);

        if (is_null($response) || empty($response['data'])) {
            $response = array();
        }

        $dataCount = isset($response['data']) ? count($response['data']) : 0;
        $this->getActualOperationHistory()->addText("Total received parts from eBay: {$dataCount}");

        return $response;
    }

    protected function deleteAllKtypes()
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMotorsKtypes = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_motor_ktype');

        $connWrite->delete($tableMotorsKtypes, '`is_custom` = 0');
    }

    protected function saveKtypesToDb(array $data)
    {
        $totalCountItems = count($data['items']);
        if ($totalCountItems <= 0) {
            return;
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMotorsKtype = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_motor_ktype');

        $iteration            = 0;
        $iterationsForOneStep = 1000;
        $percentsForOneStep   = ($this->getPercentsInterval()/2) / ($totalCountItems/$iterationsForOneStep);

        $temporaryIds   = array();
        $itemsForInsert = array();

        for ($i = 0; $i < $totalCountItems; $i++) {

            $item = $data['items'][$i];

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

            if (count($itemsForInsert) >= 100 || $i >= ($totalCountItems - 1)) {

                $connWrite->insertMultiple($tableMotorsKtype, $itemsForInsert);
                $connWrite->delete($tableMotorsKtype, array('is_custom = ?' => 1,
                                                            'ktype IN (?)'  => $temporaryIds));
                $itemsForInsert = $temporaryIds = array();
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
        // The "Parts Compatibility" Action for eBay Site: "%mrk%" has been successfully completed.

        $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
            'The "Parts Compatibility" Action for eBay Site: "%mrk%" has been successfully completed.',
            array('mrk' => $marketplace->getTitle())
        );

        $this->getLog()->addMessage($tempString,
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
    }

    //########################################
}