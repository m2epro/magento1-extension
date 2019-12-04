<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Info_Mysql_Integration extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelDatabaseIntegration');
        // ---------------------------------------

        $this->setTemplate('M2ePro/controlPanel/info/mysql/integration.phtml');
    }

    //########################################

    public function getInfoTables()
    {
        $tablesData = array_merge(
            $this->getGeneralTables(),
            $this->getEbayTables(),
            $this->getAmazonTables(),
            $this->getWalmartTables()
        );

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
                    '*/adminhtml_controlPanel_database/manageTable', array('table' => $tableName)
                );
            }
        }

        return $tablesInfo;
    }

    //########################################

    protected function getGeneralTables()
    {
        return array(
            'General' => array(
                'm2epro_account',
                'm2epro_listing',
                'm2epro_listing_product',
                'm2epro_listing_other'
            )
        );
    }

    protected function getAmazonTables()
    {
        return array(
            'Amazon' => array(
                'm2epro_amazon_account',
                'm2epro_amazon_item',
                'm2epro_amazon_listing',
                'm2epro_amazon_listing_product',
                'm2epro_amazon_listing_other'
            )
        );
    }

    protected function getEbayTables()
    {
        return array(
            'Ebay' => array(
                'm2epro_ebay_account',
                'm2epro_ebay_item',
                'm2epro_ebay_listing',
                'm2epro_ebay_listing_product',
                'm2epro_ebay_listing_other'
            )
        );
    }

    protected function getWalmartTables()
    {
        return array(
            'Walmart' => array(
                'm2epro_walmart_account',
                'm2epro_walmart_item',
                'm2epro_walmart_listing',
                'm2epro_walmart_listing_product',
                'm2epro_walmart_listing_other'
            )
        );
    }

    //########################################
}
