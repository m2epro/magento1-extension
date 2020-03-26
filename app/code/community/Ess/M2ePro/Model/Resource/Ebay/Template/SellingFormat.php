<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Ebay_Template_SellingFormat
    extends Ess_M2ePro_Model_Resource_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Template_SellingFormat', 'template_selling_format_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################

    public function getCharityDictionary()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictMarketplace = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_dictionary_marketplace');

        $dbSelect = $connRead->select()
            ->from($tableDictMarketplace, array('marketplace_id', 'charities'));

        $data = $connRead->fetchAssoc($dbSelect);

        foreach ($data as $key => $item) {
            $data[$key]['charities'] = Mage::helper('M2ePro')->jsonDecode($item['charities']);
        }

        return $data;
    }

    //########################################
}
