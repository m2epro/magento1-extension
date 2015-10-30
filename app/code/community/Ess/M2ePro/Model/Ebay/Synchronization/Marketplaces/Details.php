<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces_Details
    extends Ess_M2ePro_Model_Ebay_Synchronization_Marketplaces_Abstract
{
    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/details/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Details';
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
        return 25;
    }

    //########################################

    protected function performActions()
    {
        $params = $this->getParams();

        /** @var $marketplace Ess_M2ePro_Model_Marketplace **/
        $marketplace = Mage::helper('M2ePro/Component_Ebay')
                            ->getObject('Marketplace', (int)$params['marketplace_id']);

        $this->getActualOperationHistory()->addText('Starting Marketplace "'.$marketplace->getTitle().'"');

        $this->getActualOperationHistory()->addTimePoint(__METHOD__.'get'.$marketplace->getId(),
                                                         'Get Details from eBay');
        $details = $this->receiveFromEbay($marketplace);
        $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$marketplace->getId());

        $this->getActualLockItem()->setPercents($this->getPercentsStart() + $this->getPercentsInterval()/2);
        $this->getActualLockItem()->activate();

        $this->getActualOperationHistory()->addTimePoint(__METHOD__.'save'.$marketplace->getId(),'Save Details to DB');
        $this->saveDetailsToDb($marketplace,$details);
        $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'save'.$marketplace->getId());

        $this->logSuccessfulOperation($marketplace);
    }

    //########################################

    protected function receiveFromEbay(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $dispatcherObj = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector('marketplace','get','info',
                                                            array('include_details' => 1),'info',
                                                            $marketplace->getId(),NULL,NULL);

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
        $coreResourceModel = Mage::getSingleton('core/resource');

        $tableMarketplaces = $coreResourceModel->getTableName('m2epro_ebay_dictionary_marketplace');
        $tableShipping = $coreResourceModel->getTableName('m2epro_ebay_dictionary_shipping');

        // Save marketplaces
        // ---------------------------------------
        $connWrite->delete($tableMarketplaces, array('marketplace_id = ?' => $marketplace->getId()));

        $insertData = array(
            'marketplace_id'                  => $marketplace->getId(),
            'client_details_last_update_date' => isset($details['last_update']) ? $details['last_update'] : NULL,
            'server_details_last_update_date' => isset($details['last_update']) ? $details['last_update'] : NULL,
            'dispatch'                        => json_encode($details['dispatch']),
            'packages'                        => json_encode($details['packages']),
            'return_policy'                   => json_encode($details['return_policy']),
            'listing_features'                => json_encode($details['listing_features']),
            'payments'                        => json_encode($details['payments']),
            'shipping_locations'              => json_encode($details['shipping_locations']),
            'shipping_locations_exclude'      => json_encode($details['shipping_locations_exclude']),
            'tax_categories'                  => json_encode($details['tax_categories']),
            'charities'                       => json_encode($details['charities']),
        );

        if (isset($details['additional_data'])) {
            $insertData['additional_data'] = json_encode($details['additional_data']);
        }

        unset($details['categories_version']);
        $connWrite->insert($tableMarketplaces, $insertData);
        // ---------------------------------------

        // Save shipping
        // ---------------------------------------
        $connWrite->delete($tableShipping, array('marketplace_id = ?' => $marketplace->getId()));

        foreach ($details['shipping'] as $data) {
            $insertData = array(
                'marketplace_id'   => $marketplace->getId(),
                'ebay_id'          => $data['ebay_id'],
                'title'            => $data['title'],
                'category'         => json_encode($data['category']),
                'is_flat'          => $data['is_flat'],
                'is_calculated'    => $data['is_calculated'],
                'is_international' => $data['is_international'],
                'data'             => json_encode($data['data']),
            );
            $connWrite->insert($tableShipping, $insertData);
        }
        // ---------------------------------------
    }

    protected function logSuccessfulOperation(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        // M2ePro_TRANSLATIONS
        // The "Details" Action for eBay Site: "%mrk%" has been successfully completed.

        $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
            'The "Details" Action for eBay Site: "%mrk%" has been successfully completed.',
            array('mrk' => $marketplace->getTitle())
        );

        $this->getLog()->addMessage($tempString,
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
    }

    //########################################
}