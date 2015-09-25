<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Amazon_Template_Description
    extends Ess_M2ePro_Model_Mysql4_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Template_Description', 'template_description_id');
        $this->_isPkAutoIncrement = false;
    }

    // ########################################

    public function setSynchStatusNeed($newData, $oldData, $listingsProducts)
    {
        if (empty($listingsProducts)) {
            return;
        }

        $listingsProductsIds = array();
        foreach ($listingsProducts as $listingProduct) {
            $listingsProductsIds[] = $listingProduct['id'];
        }

        if (!$this->isDifferent($newData,$oldData)) {
            return;
        }

        $templates = array('descriptionTemplate');

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

    // ----------------------------------------

    public function isDifferent($newData, $oldData)
    {
        $ignoreFields = array(
            $this->getIdFieldName(),
            'id', 'title', 'component_mode',
            'create_date', 'update_date',
        );

        foreach ($ignoreFields as $ignoreField) {
            unset($newData[$ignoreField],$oldData[$ignoreField]);
        }

        $definitionNewData = isset($newData['definition']) ? $newData['definition'] : array();
        $definitionOldData = isset($oldData['definition']) ? $oldData['definition'] : array();
        unset($newData['definition'], $oldData['definition']);

        $ignoreFields = array('template_description_id', 'update_date', 'create_date');
        foreach ($ignoreFields as $ignoreField) {
            unset($definitionNewData[$ignoreField], $definitionOldData[$ignoreField]);
        }

        $specificsNewData = isset($newData['specifics']) ? $newData['specifics'] : array();
        $specificsOldData = isset($oldData['specifics']) ? $oldData['specifics'] : array();
        unset($newData['specifics'], $oldData['specifics']);

        $ignoreFields = array('id', 'template_description_id', 'update_date', 'create_date');
        foreach ($specificsNewData as $key => $newInfo) {
            foreach ($ignoreFields as $ignoreField) {
                unset($specificsNewData[$key][$ignoreField]);
            }
        }
        foreach ($specificsOldData as $key => $newInfo) {
            foreach ($ignoreFields as $ignoreField) {
                unset($specificsOldData[$key][$ignoreField]);
            }
        }

        array_walk($specificsNewData, 'ksort');
        array_walk($specificsOldData, 'ksort');

        return md5(json_encode($specificsNewData)) !== md5(json_encode($specificsOldData)) ||
               count(array_diff_assoc($definitionNewData, $definitionOldData)) ||
               count(array_diff_assoc($newData, $oldData));
    }

    // ########################################
}