<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Template_Category
    extends Ess_M2ePro_Model_Mysql4_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Template_Category', 'id');
    }

    // ########################################

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

        $templates = array('categoryTemplate');

        Mage::getSingleton('core/resource')->getConnection('core_write')->update(
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
            'title',
            'create_date', 'update_date'
        );

        foreach ($ignoreFields as $ignoreField) {
            unset($newData[$ignoreField],$oldData[$ignoreField]);
        }

        !isset($newData['specifics']) && $newData['specifics'] = array();
        !isset($oldData['specifics']) && $oldData['specifics'] = array();

        foreach ($newData['specifics'] as $key => $newSpecific) {
            unset($newData['specifics'][$key]['id'], $newData['specifics'][$key]['template_category_id']);
        }
        foreach ($oldData['specifics'] as $key => $oldSpecific) {
            unset($oldData['specifics'][$key]['id'], $oldData['specifics'][$key]['template_category_id']);
        }

        ksort($newData);
        ksort($oldData);
        array_walk($newData['specifics'],'ksort');
        array_walk($oldData['specifics'],'ksort');

        return md5(json_encode($newData)) !== md5(json_encode($oldData));
    }

    // ########################################
}