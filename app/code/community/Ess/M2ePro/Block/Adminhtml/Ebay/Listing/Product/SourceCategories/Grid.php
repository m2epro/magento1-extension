<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_SourceCategories_Grid
    extends Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_Grid
{
    private $selectedIds = array();

    private $currentCategoryId = NULL;

    //########################################

    private function getCollectionIds()
    {
        if (!is_null($ids = $this->getData('collection_ids'))) {
            return $ids;
        }

        $ids = Mage::helper('M2ePro/Magento_Category')->getProductsFromCategories(
            array($this->getCurrentCategoryId()), $this->_getStore()->getId()
        );

        $this->setData('collection_ids',$ids);
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

        $ids = array_filter(explode(',',$ids));
        $ids = array_merge($ids,$this->getSelectedIds());
        $ids = array_intersect($ids,$this->getCollectionIds());
        $ids = array_values(array_unique($ids));

        $this->getRequest()->setPost($this->getMassactionBlock()->getFormFieldNameInternal(),implode(',',$ids));

        return parent::_prepareMassaction();
    }

    //########################################

    public function setSelectedIds(array $ids)
    {
        $this->selectedIds = $ids;
        return $this;
    }

    public function getSelectedIds()
    {
        return $this->selectedIds;
    }

    // ---------------------------------------

    public function setCurrentCategoryId($currentCategoryId)
    {
        $this->currentCategoryId = $currentCategoryId;
        return $this;
    }

    public function getCurrentCategoryId()
    {
        return $this->currentCategoryId;
    }

    //########################################

    public function setCollection($collection)
    {
        $collection->joinTable(
            array('ccp' => 'catalog/category_product'),
            'product_id=entity_id',
            array('category_id' => 'category_id')
        );

        $collection->addFieldToFilter('category_id', $this->currentCategoryId);

        parent::setCollection($collection);
    }

    //########################################

    protected function getSelectedProductsCallback()
    {
        return <<<JS
(function() {
    return function(callback) {

        saveSelectedProducts(function(transport) {

            new Ajax.Request('{$this->getUrl('*/*/getSessionProductsIds', array('_current' => true))}', {
                method: 'get',
                onSuccess: function(transport) {
                    var massGridObj = {$this->getMassactionBlock()->getJsObjectName()};

                    massGridObj.initialCheckedString = massGridObj.checkedString;

                    var response = transport.responseText.evalJSON();
                    var ids = response['ids'].join(',');

                    callback(ids);
                }
            });

        });
    }
})()
JS;

    }

    //########################################

    protected function _toHtml()
    {
        $html = parent::_toHtml();

        $js = '';

        if ($this->getRequest()->getParam('category_change')) {
            $checkedString = implode(',', array_intersect($this->getCollectionIds(), $this->selectedIds));
            $js .= <<<HTML
<script type="text/javascript">
    {$this->getMassactionBlock()->getJsObjectName()}.checkedString = '{$checkedString}';
    {$this->getMassactionBlock()->getJsObjectName()}.initCheckboxes();
    {$this->getMassactionBlock()->getJsObjectName()}.checkCheckboxes();
    {$this->getMassactionBlock()->getJsObjectName()}.updateCount();

    {$this->getMassactionBlock()->getJsObjectName()}.initialCheckedString =
        {$this->getMassactionBlock()->getJsObjectName()}.checkedString;
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
</div>
{$js}
HTML;

    }

    //########################################
}