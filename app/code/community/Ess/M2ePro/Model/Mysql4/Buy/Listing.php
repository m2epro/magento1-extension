<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Buy_Listing
    extends Ess_M2ePro_Model_Mysql4_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Buy_Listing', 'listing_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################

    public function updateStatisticColumns()
    {
        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $listingProductTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();
        $buyListingProductTable = Mage::getResourceModel('M2ePro/Buy_Listing_Product')->getMainTable();

        $select = $this->_getReadAdapter()
                       ->select()
                       ->from(
                            array('lp' => $listingProductTable),
                            new Zend_Db_Expr('SUM(`online_qty`)')
                       )
                       ->join(
                            array('blp' => $buyListingProductTable),
                            'lp.id = blp.listing_product_id',
                            array()
                       )
                       ->where("`listing_id` = `{$listingTable}`.`id`")
                       ->where("`status` = ?",(int)Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);

        $query = "UPDATE `{$listingTable}`
                  SET `items_active_count` =  IFNULL((".$select->__toString()."),0)
                  WHERE `component_mode` = '".Ess_M2ePro_Helper_Component_Buy::NICK."'";

        $this->_getWriteAdapter()->query($query);
    }

    //########################################

    public function setSynchStatusNeed($newData, $oldData, $listingProducts)
    {
        $this->setSynchStatusNeedByListing($newData,$oldData,$listingProducts);
        $this->setSynchStatusNeedBySellingFormatTemplate($newData,$oldData,$listingProducts);
        $this->setSynchStatusNeedBySynchronizationTemplate($newData,$oldData,$listingProducts);
    }

    // ---------------------------------------

    public function setSynchStatusNeedByListing($newData, $oldData, $listingsProducts)
    {
        $listingsProductsIds = array();
        foreach ($listingsProducts as $listingProduct) {
            $listingsProductsIds[] = (int)$listingProduct['id'];
        }

        if (empty($listingsProductsIds)) {
            return;
        }

        unset(
            $newData['template_selling_format_id'], $oldData['template_selling_format_id'],
            $newData['template_synchronization_id'], $oldData['template_synchronization_id']
        );

        if (!$this->isDifferent($newData,$oldData)) {
            return;
        }

        $templates = array('listing');

        $this->_getWriteAdapter()->update(
            Mage::getSingleton('core/resource')->getTableName('M2ePro/Listing_Product'),
            array(
                'synch_status' => Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_NEED,
                'synch_reasons' => new Zend_Db_Expr(
                    "IF(synch_reasons IS NULL,
                        '".implode(',',$templates)."',
                        CONCAT(synch_reasons,'".','.implode(',',$templates)."')
                    )"
                )
            ),
            array('id IN ('.implode(',', $listingsProductsIds).')')
        );
    }

    public function setSynchStatusNeedBySellingFormatTemplate($newData, $oldData, $listingsProducts)
    {
        $newSellingFormatTemplate = Mage::helper('M2ePro/Component_Buy')->getCachedObject(
            'Template_SellingFormat', $newData['template_selling_format_id'], NULL, array('template')
        );

        $oldSellingFormatTemplate = Mage::helper('M2ePro/Component_Buy')->getCachedObject(
            'Template_SellingFormat', $oldData['template_selling_format_id'], NULL, array('template')
        );

        Mage::getResourceModel('M2ePro/Buy_Template_SellingFormat')->setSynchStatusNeed(
            $newSellingFormatTemplate->getDataSnapshot(),
            $oldSellingFormatTemplate->getDataSnapshot(),
            $listingsProducts
        );
    }

    public function setSynchStatusNeedBySynchronizationTemplate($newData, $oldData, $listingsProducts)
    {
        $newSynchTemplate = Mage::helper('M2ePro/Component_Buy')->getCachedObject(
            'Template_Synchronization', $newData['template_synchronization_id'], NULL, array('template')
        );

        $oldSynchTemplate = Mage::helper('M2ePro/Component_Buy')->getCachedObject(
            'Template_Synchronization', $oldData['template_synchronization_id'], NULL, array('template')
        );

        Mage::getResourceModel('M2ePro/Buy_Template_Synchronization')->setSynchStatusNeed(
            $newSynchTemplate->getDataSnapshot(),
            $oldSynchTemplate->getDataSnapshot(),
            $listingsProducts
        );
    }

    // ---------------------------------------

    public function isDifferent($newData, $oldData)
    {
        $ignoreFields = array(
            $this->getIdFieldName(),
            'id', 'title',
            'component_mode',
            'create_date', 'update_date'
        );

        $dataConversions = array(
            array('field' => 'shipping_one_day_value', 'type' => 'float'),
            array('field' => 'shipping_two_day_value', 'type' => 'float'),
            array('field' => 'shipping_standard_value', 'type' => 'float'),
            array('field' => 'shipping_expedited_value', 'type' => 'float'),
        );

        foreach ($dataConversions as $data) {
            $type = $data['type'] . 'val';

            array_key_exists($data['field'],$newData) && $newData[$data['field']] = $type($newData[$data['field']]);
            array_key_exists($data['field'],$oldData) && $oldData[$data['field']] = $type($oldData[$data['field']]);
        }

        foreach ($ignoreFields as $ignoreField) {
            unset($newData[$ignoreField],$oldData[$ignoreField]);
        }

        return (count(array_diff_assoc($newData,$oldData)) > 0);
    }

    //########################################
}