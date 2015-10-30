<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Template_SellingFormat
    extends Ess_M2ePro_Model_Mysql4_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Template_SellingFormat', 'template_selling_format_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################

    public function setSynchStatusNeed($newData, $oldData, $listingsProducts)
    {
        $listingsProductsIds = array();
        foreach ($listingsProducts as $listingProduct) {
            $listingsProductsIds[] = (int)$listingProduct['id'];
        }

        if (empty($listingsProductsIds)) {
            return;
        }

        if (!$this->isDifferent($newData,$oldData)) {
            return;
        }

        $templates = array('sellingFormatTemplate');

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

    // ---------------------------------------

    public function isDifferent($newData, $oldData)
    {
        $ignoreFields = array(
            $this->getIdFieldName(),
            'id', 'title', 'component_mode',
            'is_custom_template',
            'create_date', 'update_date'
        );

        foreach ($ignoreFields as $ignoreField) {
            unset($newData[$ignoreField],$oldData[$ignoreField]);
        }

        return (count(array_diff_assoc($newData,$oldData)) > 0);
    }

    //########################################
}