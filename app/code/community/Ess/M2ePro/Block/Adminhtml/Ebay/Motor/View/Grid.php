<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_View_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $listingProductId = array();

    private $compatibilityType = null;

    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayMotorViewGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('component');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setPagerVisibility(false);
        $this->setDefaultLimit(false);
        //------------------------------
    }

    // ########################################

    public function setListingProductId($listingProductId)
    {
        $this->listingProductId = $listingProductId;
        return $this;
    }

    public function getListingProductId()
    {
        return $this->listingProductId;
    }

    public function setCompatibilityType($compatibilityType)
    {
        $this->compatibilityType = $compatibilityType;
        return $this;
    }

    public function getCompatibilityType()
    {
        return $this->compatibilityType;
    }

    // ########################################

    protected function getExistingItems(array $ids)
    {
        $typeIdentifier = $this->getCompatibilityHelper()->getIdentifierKey($this->getCompatibilityType());

        $select = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from(
                $this->getCompatibilityHelper()->getDictionaryTable($this->getCompatibilityType()),
                array($typeIdentifier)
            )
            ->where('`'.$typeIdentifier.'` IN (?)', $ids);

        return $select->query()->fetchAll(PDO::FETCH_COLUMN);
    }

    protected function getCollectionItems()
    {
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject(
            'Listing_Product', (int)$this->getListingProductId()
        );

        /** @var Ess_M2ePro_Model_Magento_Product $magentoProduct */
        $magentoProduct = $listingProduct->getMagentoProduct();

        $compatibilityAttribute = $this->getCompatibilityHelper()->getAttribute($this->getCompatibilityType());
        $attributeValue = $magentoProduct->getAttributeValue($compatibilityAttribute);

        $parsedValue = $this->getCompatibilityHelper()->parseAttributeValue($attributeValue);
        if (empty($parsedValue)) {
            return array();
        }

        $existingItems = $this->getExistingItems(array_keys($parsedValue));

        $items = array();
        foreach ($parsedValue as $id => $compatibleItem) {
            $itemData = array(
                'id'       => $id,
                'note'     => $compatibleItem['note'],
                'is_exist' => in_array($id, $existingItems),
            );

            $items[$id] = new Varien_Object($itemData);
        }

        return $items;
    }

    protected function _prepareCollection()
    {
        $collection = new Varien_Data_Collection();
        foreach ($this->getCollectionItems() as $item) {
            $collection->addItem($item);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $typeIdentifierTitle = 'ePID';
        if ($this->getCompatibilityType() == Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_KTYPE) {
            $typeIdentifierTitle = 'kType';
        }

        $this->addColumn('id', array(
            'header'    => $typeIdentifierTitle,
            'align'     => 'left',
            'width'     => '50px',
            'index'     => 'id',
            'type'      => 'text',
            'filter_index' => 'id',
            'filter_condition_callback' => array($this, '_customColumnFilter'),
        ));

        $this->addColumn('note', array(
            'header'    => Mage::helper('M2ePro')->__('Note'),
            'align'     => 'left',
            'width'     => '250px',
            'index'     => 'note',
            'type'      => 'text',
            'filter_index' => 'note',
            'filter_condition_callback' => array($this, '_customColumnFilter'),
            'frame_callback' => array($this, 'callbackColumnNote')
        ));

        return parent::_prepareColumns();
    }

    // ########################################

    public function callbackColumnNote($value, $row, $column, $isExport)
    {
        $editLabel = Mage::helper('M2ePro')->__('Edit Note');
        $addLabel = Mage::helper('M2ePro')->__('Add Note');
        $saveLabel = Mage::helper('M2ePro')->__('Save Note');
        $cancelLabel = Mage::helper('M2ePro')->__('Cancel');

        $addStyle = '';
        $editStyle = '';

        $value ? ($addStyle = ' style="display: none;"') : ($editStyle = ' style="display: none;"');

        return <<<HTML

<div id="note_{$row['id']}">
    <span id="note_view_{$row['id']}">{$value}</span>
    <div id="note_edit_{$row['id']}_container" style="display: none">
        <textarea id="note_edit_{$row['id']}"></textarea>
        <br/>
    </div>
    <span id="note_edit_link_{$row['id']}"{$editStyle}>
        <br/>
        <a href="javascript:void(0)"
           onclick="EbayMotorCompatibilityHandlerObj.switchNoteEditMode('{$row['id']}')">{$editLabel}</a>
    </span>
    <span id="note_add_link_{$row['id']}"{$addStyle}>
        <a href="javascript:void(0)"
           onclick="EbayMotorCompatibilityHandlerObj.switchNoteEditMode('{$row['id']}')">{$addLabel}</a>
    </span>
    <span id="note_save_link_{$row['id']}" style="display: none">
        <a href="javascript:void(0)"
           onclick="EbayMotorCompatibilityHandlerObj.saveNote('{$row['id']}')">{$saveLabel}</a>
    </span>
    &nbsp;&nbsp;&nbsp;
    <span id="note_cancel_link_{$row['id']}" style="display: none">
        <a href="javascript:void(0)"
           onclick="EbayMotorCompatibilityHandlerObj.switchNoteEditMode('{$row['id']}', true)">{$cancelLabel}</a>
    </span>
</div>

HTML;

    }

    // ########################################

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setUseSelectAll(false);
        //--------------------------------

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('M2ePro')->__('Delete'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('add_note', array(
            'label' => Mage::helper('M2ePro')->__('Set Note'),
            'url'   => '',
        ));

        return parent::_prepareMassaction();
    }

    protected function getNoFilterMassactionColumn()
    {
        return true;
    }

    // ########################################

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/adminhtml_ebay_listing/motorViewGrid',
            array(
                'listing_product_id' => $this->getListingProductId(),
                'compatibility_type' => $this->getCompatibilityType()
            )
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection() && $column->getFilterConditionCallback()) {
            call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
        }
        return $this;
    }

    // ####################################

    protected function _customColumnFilter($collection, $column)
    {
        $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
        $condition = $column->getFilter()->getCondition();
        $value = array_pop($condition);

        if ($field && isset($condition)) {
            $this->_filterByField($field, $value);
        }

        return $this;
    }

    //--------------------------------

    protected function _filterByField($field, $value)
    {
        $filteredCollection = new Varien_Data_Collection();
        $value = str_replace(array(' ','%','\\','\''),'',$value);

        foreach ($this->getCollection()->getItems() as $item) {
            if (strpos($item->getData($field),$value) !== false) {
                $filteredCollection->addItem($item);
            }
        }
        $this->setCollection($filteredCollection);
    }

    // ####################################

    protected function _setCollectionOrder($column)
    {
        $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
        $direction = $column->getDir();

        if ($field && isset($direction)) {
            $this->_orderByColumn($field, $direction);
        }

        return $this;
    }

    //--------------------------------

    protected function _orderByColumn($column, $direction)
    {
        $sortedCollection = new Varien_Data_Collection();

        $collection = $this->getCollection()->toArray();
        $collection = $collection['items'];

        $sortByColumn = array();
        foreach ($collection as $item) {
            $sortByColumn[] = $item[$column];
        }

        strtolower($direction) == 'asc' && array_multisort($sortByColumn, SORT_ASC, $collection);
        strtolower($direction) == 'desc' && array_multisort($sortByColumn, SORT_DESC, $collection);

        foreach ($collection as $item) {
            $sortedCollection->addItem(new Varien_Object($item));
        }
        $this->setCollection($sortedCollection);
    }

    // ####################################

    public function getRowClass($row)
    {
        return !$row->getData('is_exist') ? 'invalid-row' : '';
    }

    // ####################################

    /**
     * @return Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility
     */
    private function getCompatibilityHelper()
    {
        return Mage::helper('M2ePro/Component_Ebay_Motor_Compatibility');
    }

    // ####################################

    protected function _toHtml()
    {
        $additionalCss = <<<CSS

<style>
    body {
        background: none;
    }

    #messages {
        display: none;
    }
    .wrapper {
        min-width: inherit;
    }

    .footer {
        display: none;
    }

    .middle {
        padding: 0px;
        background: none;
    }
</style>

CSS;

        $additionalHtml =<<<HTML

<input type="hidden" id="compatibility_view_listing_product_id" value="{$this->getListingProductId()}">

HTML;

        $urls = json_encode(array(
            'adminhtml_ebay_listing/setNoteToCompatibilityList' =>
                $this->getUrl('*/adminhtml_ebay_listing/setNoteToCompatibilityList'),
            'adminhtml_ebay_listing/deleteIdsFromCompatibilityList' =>
                $this->getUrl('*/adminhtml_ebay_listing/deleteIdsFromCompatibilityList'),
        ));

        $translations = json_encode(array(
            'Please select Items you want to perform the Action on.'
                => Mage::helper('M2ePro')->__('Please select Items you want to perform the Action on.'),
            'Set Note' => Mage::helper('M2ePro')->__('Set Note'),
        ));

        $additionalJs = <<<JAVASCRIPT
<script text="text/javascript">

    EbayMotorCompatibilityHandlerObj = new EbayMotorCompatibilityHandler(
            null,
            '{$this->getCompatibilityType()}',
            null,
            null,
            null
        );
    EbayMotorCompatibilityHandlerObj.setMode('edit');
    EbayMotorCompatibilityHandlerObj.initCompatibilityViewGrid();

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});
    top.M2ePro.translator.add({$translations});

</script>

JAVASCRIPT;

        return $additionalCss . $additionalHtml . parent::_toHtml() . $additionalJs;

    }

    // ####################################
}