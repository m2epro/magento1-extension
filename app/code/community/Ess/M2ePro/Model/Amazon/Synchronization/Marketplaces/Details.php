<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Marketplaces_Details
    extends Ess_M2ePro_Model_Amazon_Synchronization_Marketplaces_Abstract
{
    //########################################

    protected function getNick()
    {
        return '/details/';
    }

    protected function getTitle()
    {
        return 'Details';
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
        $params = $this->getParams();

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component_Amazon')
                            ->getObject('Marketplace', (int)$params['marketplace_id']);

        $this->getActualOperationHistory()->addText('Starting Marketplace "'.$marketplace->getTitle().'"');

        $this->getActualLockItem()->setPercents($this->getPercentsStart());

        $this->getActualOperationHistory()->addTimePoint(__METHOD__.'get'.$marketplace->getId(),
                                                         'Get details from Amazon');
        $details = $this->receiveFromAmazon($marketplace);
        $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$marketplace->getId());

        $this->getActualLockItem()->setPercents($this->getPercentsStart() + $this->getPercentsInterval()/2);
        $this->getActualLockItem()->activate();

        $this->getActualOperationHistory()->addTimePoint(__METHOD__.'save'.$marketplace->getId(),'Save details to DB');
        $this->saveDetailsToDb($marketplace,$details);
        $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'save'.$marketplace->getId());

        $this->getActualLockItem()->setPercents($this->getPercentsEnd());
        $this->getActualLockItem()->activate();

        $this->logSuccessfulOperation($marketplace);
    }

    //########################################

    protected function receiveFromAmazon(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $dispatcherObj = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
        $connectorObj  = $dispatcherObj->getVirtualConnector('marketplace','get','info',
                                                             array('include_details' => true,
                                                                   'marketplace' => $marketplace->getNativeId()),
                                                             'info',NULL,NULL);

        $details = $dispatcherObj->process($connectorObj);

        if (is_null($details)) {
            return array();
        }

        $details['details']['last_update'] = $details['last_update'];
        return $details['details'];
    }

    protected function saveDetailsToDb(Ess_M2ePro_Model_Marketplace $marketplace, array $details)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableMarketplaces = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_marketplace');
        $tableShippingOverride = Mage::getSingleton('core/resource')
            ->getTableName('m2epro_amazon_dictionary_shipping_override');

        $connWrite->delete($tableMarketplaces,array('marketplace_id = ?' => $marketplace->getId()));

        $data = array(
            'marketplace_id' => $marketplace->getId(),
            'client_details_last_update_date' => isset($details['last_update']) ? $details['last_update'] : NULL,
            'server_details_last_update_date' => isset($details['last_update']) ? $details['last_update'] : NULL,
            'product_data'   => isset($details['product_data']) ? json_encode($details['product_data']) : NULL,
        );

        $connWrite->insert($tableMarketplaces, $data);

        Mage::helper('M2ePro/Component_Amazon_Vocabulary')->setServerData($details['vocabulary']);

        $connWrite->delete($tableShippingOverride, array('marketplace_id = ?' => $marketplace->getId()));

        foreach ($details['shipping_overrides'] as $data) {
            $insertData = array(
                'marketplace_id'   => $marketplace->getId(),
                'location'         => $data['location'],
                'service'          => $data['service'],
                'option'           => $data['option']
            );
            $connWrite->insert($tableShippingOverride, $insertData);
        }
    }

    protected function logSuccessfulOperation(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        // M2ePro_TRANSLATIONS
        // The "Details" Action for %amazon% Marketplace: "%mrk%" has been successfully completed.

        $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
            'The "Details" Action for %amazon% Marketplace: "%mrk%" has been successfully completed.',
            array('!amazon' => Mage::helper('M2ePro/Component_Amazon')->getTitle(),
                  'mrk'     => $marketplace->getTitle())
        );

        $this->getLog()->addMessage($tempString,
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
    }

    //########################################
}