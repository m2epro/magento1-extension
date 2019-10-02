<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Category_Grid
    extends Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Grid
{
    protected $_selectedIds = array();

    protected $_currentCategoryId;

    //########################################

    protected function getCollectionIds()
    {
        $ids = $this->getData('collection_ids');
        if ($ids !== null) {
            return $ids;
        }

        /* We use the default store view due to this
         * app/code/community/Ess/M2ePro/Block/Adminhtml/Walmart/Listing/Product/Grid.php
         * $collection->setStoreId(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
         */
        $ids = Mage::helper('M2ePro/Magento_Category')->getProductsFromCategories(
            array($this->getCurrentCategoryId()), Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
        );

        $this->setData('collection_ids', $ids);
        return $ids;
    }

    //########################################

    protected function _prepareMassaction()
    {
        $this->getMassactionBlock()->setFormFieldName('ids');

        $ids = $this->getRequest()->getPost($this->getMassactionBlock()->getFormFieldNameInternal());

        if ($this->getRequest()->isXmlHttpRequest() && !$this->getRequest()->getParam('category_change')) {
            return parent::_prepareMassaction();
        }

        $ids = array_filter(explode(',', $ids));
        $ids = array_merge($ids, $this->getSelectedIds());
        $ids = array_intersect($ids, $this->getCollectionIds());
        $ids = array_values(array_unique($ids));

        $this->getRequest()->setPost($this->getMassactionBlock()->getFormFieldNameInternal(), implode(',', $ids));

        return parent::_prepareMassaction();
    }

    //########################################

    public function setSelectedIds(array $ids)
    {
        $this->_selectedIds = $ids;
        return $this;
    }

    public function getSelectedIds()
    {
        return $this->_selectedIds;
    }

    // ---------------------------------------

    public function setCurrentCategoryId($currentCategoryId)
    {
        $this->_currentCategoryId = $currentCategoryId;
        return $this;
    }

    public function getCurrentCategoryId()
    {
        return $this->_currentCategoryId;
    }

    //########################################

    public function setCollection($collection)
    {
        $collection->joinTable(
            array('ccp' => 'catalog/category_product'),
            'product_id=entity_id',
            array('category_id' => 'category_id')
        );

        $collection->addFieldToFilter('category_id', $this->_currentCategoryId);

        parent::setCollection($collection);
    }

    //########################################

    public function getSelectedProductsCallback()
    {
        return <<<JS
var add_category_products = function(callback) {

    saveSelectedProducts(function(transport) {

        new Ajax.Request('{$this->getUrl('*/*/getSessionProductsIds', array('_current' => true))}', {
            method: 'get',
            onSuccess: function(transport) {
                var massGridObj = {$this->getMassactionBlock()->getJsObjectName()};

                massGridObj.initialCheckedString = massGridObj.checkedString;

                var response = transport.responseText.evalJSON();
                var ids = response['ids'].join(',');

                if (ids == '') {
                    alert(M2ePro.text.select_items_message);
                    return false;
                }

                ProductGridHandlerObj.addListingHandlerObj.add(ids, 'view', '');
            }
        });

    });
};
JS;

    }

    //########################################

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $js = '';

        if (!$this->getRequest()->isXmlHttpRequest() || $this->getRequest()->getParam('category_change')) {
            $jsObjectName = $this->getMassactionBlock()->getJsObjectName();
            $checkedString = implode(',', array_intersect($this->getCollectionIds(), $this->_selectedIds));

            $js .= <<<HTML
<script type="text/javascript">
    {$jsObjectName}.checkedString = '{$checkedString}';
    {$jsObjectName}.initCheckboxes();
    {$jsObjectName}.checkCheckboxes();
    {$jsObjectName}.updateCount();

    {$jsObjectName}.initialCheckedString = {$jsObjectName}.checkedString;
</script>
HTML;
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $html . $js;
        }

        return <<<HTML
<div class="columns">
    <div class="side-col">{$this->getTreeBlock()->toHtml()}</div>
    <div class="main-col">{$html}</div>
    <script type="text/javascript">
        {$this->getSelectedProductsCallback()}
    </script>
</div>
{$js}
HTML;

    }

    //########################################
}
