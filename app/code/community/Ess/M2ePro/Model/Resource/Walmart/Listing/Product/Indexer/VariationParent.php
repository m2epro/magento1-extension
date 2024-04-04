<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Indexer_VariationParent
    extends Ess_M2ePro_Model_Resource_Component_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Walmart_Listing_Product_Indexer_VariationParent', 'listing_product_id');
    }

    //########################################

    public function getTrackedFields()
    {
        return array(
            'online_price',
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
        if (!$listing->isComponentModeWalmart()) {
            throw new Ess_M2ePro_Model_Exception_Logic("Wrong component provided [{$listing->getComponentMode()}]");
        }

        $select = $this->getBuildIndexSelect($listing);

        $createDate = new DateTime('now', new DateTimeZone('UTC'));
        $createDate = $createDate->format('Y-m-d H:i:s');

        $select->columns(
            array(
            new Zend_Db_Expr($this->_getWriteAdapter()->quote($listing->getId())),
            new Zend_Db_Expr($this->_getWriteAdapter()->quote($createDate))
            )
        );

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
                array('malp' => Mage::getResourceModel('M2ePro/Walmart_Listing_Product')->getMainTable()),
                array(
                    'variation_parent_id',
                    new Zend_Db_Expr(
                        "MIN(malp.online_price) as variation_min_price"
                    ),
                    new Zend_Db_Expr(
                        "MAX(malp.online_price) as variation_max_price"
                    ),
                )
            )
            ->joinInner(
                array('mlp' => Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable()),
                'malp.listing_product_id = mlp.id',
                array()
            )
            ->where(
                'mlp.status IN (?)', array(
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
                Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE,
                )
            )
            ->where('mlp.listing_id = ?', (int)$listing->getId())
            ->where('malp.variation_parent_id IS NOT NULL')
            ->group('malp.variation_parent_id');

        return $select;
    }

    //########################################
}
