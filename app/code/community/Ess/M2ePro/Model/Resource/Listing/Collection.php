<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Listing_Collection
    extends Ess_M2ePro_Model_Resource_Collection_Component_Parent_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing');
    }

    public function addProductsTotalCount()
    {
        $listingProductTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_listing_product');

        $sql = <<<SQL
SELECT listing_id, COUNT(id) AS products_total_count
FROM `{$listingProductTable}`
GROUP BY listing_id
SQL;

        $this->getSelect()->joinLeft(
            new \Zend_Db_Expr('(' . $sql . ')'),
            'main_table.id=t.listing_id',
            array(
                'products_total_count' => 'products_total_count',
            )
        );

        return $this;
    }
}
