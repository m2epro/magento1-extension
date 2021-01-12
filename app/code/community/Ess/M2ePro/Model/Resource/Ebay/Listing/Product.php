<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Ebay_Listing_Product
    extends Ess_M2ePro_Model_Resource_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Listing_Product', 'listing_product_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################

    public function getTemplateCategoryIds(array $listingProductIds, $columnName, $returnNull = false)
    {
        $stmt = $this->_getReadAdapter()
            ->select()
            ->from(array('elp' => $this->getMainTable()))
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array($columnName))
            ->where('listing_product_id IN (?)', $listingProductIds);

        !$returnNull && $stmt->where("{$columnName} IS NOT NULL");

        foreach ($stmt->query()->fetchAll() as $row) {
            $id = $row[$columnName] !== null ? (int)$row[$columnName] : null;
            if (!$returnNull) {
                continue;
            }

            $ids[$id] = $id;
        }

        return array_values($ids);
    }

    //########################################

    public function assignTemplatesToProducts(
        $productsIds,
        $categoryTemplateId = null,
        $categorySecondaryTemplateId = null,
        $storeCategoryTemplateId = null,
        $storeCategorySecondaryTemplateId = null
    ) {
        if (empty($productsIds)) {
            return;
        }

        $bind = array(
            'template_category_id'                 => $categoryTemplateId,
            'template_category_secondary_id'       => $categorySecondaryTemplateId,
            'template_store_category_id'           => $storeCategoryTemplateId,
            'template_store_category_secondary_id' => $storeCategorySecondaryTemplateId
        );
        $bind = array_filter($bind);

        $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            $bind,
            array('listing_product_id IN (?)' => $productsIds)
        );
    }

    //########################################
}
