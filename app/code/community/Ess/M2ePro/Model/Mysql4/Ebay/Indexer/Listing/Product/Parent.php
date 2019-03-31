<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Indexer_Listing_Product_Parent extends Ess_M2ePro_Model_Mysql4_Component_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Indexer_Listing_Product_Parent', 'listing_product_id');
    }

    //########################################

    public function getTrackedFields()
    {
        return array(
            'online_current_price',
        );
    }

    //########################################

    public function clear($listingId = null)
    {
        $conditions = array();
        $listingId && $conditions['listing_id = ?'] = (int)$listingId;

        $this->_getWriteAdapter()->delete($this->getMainTable(), $conditions);
    }

    public function build(Ess_M2ePro_Model_Listing $listing)
    {
        if (!$listing->isComponentModeEbay()) {
            throw new Ess_M2ePro_Model_Exception_Logic("Wrong component provided [{$listing->getComponentMode()}]");
        }

        $select = $this->getBuildIndexSelect($listing);

        $createDate = new DateTime('now', new DateTimeZone('UTC'));
        $createDate = $createDate->format('Y-m-d H:i:s');

        $select->columns(array(
            new Zend_Db_Expr($this->_getWriteAdapter()->quote($listing->getId())),
            new Zend_Db_Expr($this->_getWriteAdapter()->quote($createDate))
        ));

        $query = $this->_getWriteAdapter()->insertFromSelect(
            $select,
            $this->getMainTable(),
            array(
                'listing_product_id',
                'min_price',
                'max_price',
                'listing_id',
                'create_date'
            ),
            Varien_Db_Adapter_Pdo_Mysql::INSERT_IGNORE
        );
        $this->_getWriteAdapter()->query($query);
    }

    //########################################

    public function getBuildIndexSelect(Ess_M2ePro_Model_Listing $listing)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('mlpv' => Mage::getResourceModel('M2ePro/Listing_Product_Variation')->getMainTable()),
                array(
                    'listing_product_id'
                )
            )
            ->joinInner(
                array('melpv' => Mage::getResourceModel('M2ePro/Ebay_Listing_Product_Variation')->getMainTable()),
                'mlpv.id = melpv.listing_product_variation_id',
                array(
                    new Zend_Db_Expr('MIN(`melpv`.`online_price`) as variation_min_price'),
                    new Zend_Db_Expr('MAX(`melpv`.`online_price`) as variation_max_price')
                )
            )
            ->joinInner(
                array('mlp' => Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable()),
                'mlpv.listing_product_id = mlp.id',
                array()
            )
            ->where('mlp.listing_id = ?', (int)$listing->getId())
            ->where('melpv.status != ?', Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)
            ->group('mlpv.listing_product_id');

        return $select;
    }

    //########################################
}