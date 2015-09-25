<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Info_Mysql_Integration extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentDatabaseIntegration');
        //------------------------------

        $this->setTemplate('M2ePro/development/info/mysql/integration.phtml');
    }

    // ########################################

    public function getInfoTables()
    {
        $tablesData = array_merge($this->getGeneralTables(),
                                  $this->getEbayTables(),
                                  $this->getAmazonTables());

        $helper = Mage::helper('M2ePro/Module_Database_Structure');

        $tablesInfo = array();
        foreach ($tablesData as $category => $tables) {
            foreach ($tables as $tableName) {

                $tablesInfo[$category][$tableName] = array(
                    'count' => 0, 'url'   => '#'
                );

                if (!$helper->isTableReady($tableName)) {
                    continue;
                }

                $tablesInfo[$category][$tableName]['count'] = $helper->getCountOfRecords($tableName);
                $tablesInfo[$category][$tableName]['url'] = $this->getUrl(
                    '*/adminhtml_development_database/manageTable', array('table' => $tableName)
                );
            }
        }

        return $tablesInfo;
    }

    // ########################################

    private function getGeneralTables()
    {
        return array(
            'General' => array(
                'm2epro_listing',
                'm2epro_listing_product',
            )
        );
    }

    private function getAmazonTables()
    {
        return array(
            'Amazon' => array(
                'm2epro_amazon_account',
                'm2epro_amazon_item',
                'm2epro_amazon_listing',
                'm2epro_amazon_listing_product',
            )
        );
    }

    private function getEbayTables()
    {
        return array(
            'Ebay' => array(
                'm2epro_ebay_account',
                'm2epro_ebay_item',
                'm2epro_ebay_listing',
                'm2epro_ebay_listing_product',
            )
        );
    }

    // ########################################
}