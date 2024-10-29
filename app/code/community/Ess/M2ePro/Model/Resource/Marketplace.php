<?php

class Ess_M2ePro_Model_Resource_Marketplace
    extends Ess_M2ePro_Model_Resource_Component_Parent_Abstract
{
    const COLUMN_STATUS = 'status';
    const COLUMN_SORDER = 'sorder';

    public function _construct()
    {
        $this->_init('M2ePro/Marketplace', 'id');
    }

    /**
     * @param Ess_M2ePro_Model_Marketplace $marketplace
     */
    public function isDictionaryExist($marketplace)
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName = null;

        switch ($marketplace->getComponentMode()) {
            case Ess_M2ePro_Helper_Component_Ebay::NICK:
                $tableName = 'm2epro_ebay_dictionary_marketplace';
                break;
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $tableName = 'm2epro_amazon_dictionary_marketplace';
                break;
            case Ess_M2ePro_Helper_Component_Walmart::NICK:
                $tableName = 'm2epro_walmart_dictionary_marketplace';
                break;
            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown component_mode');
        }

        $select = $connection
            ->select()
            ->from(Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($tableName), 'id')
            ->where('marketplace_id = ?', $marketplace->getId());

        return $connection->fetchOne($select) !== false;
    }

    //########################################
}
