<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_Category_Group_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    private $isGridPrepared = false;

    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingAutoActionModeCategoryGroupGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    // ########################################

    protected function _prepareGrid()
    {
        if (!$this->isGridPrepared) {
            parent::_prepareGrid();
            $this->isGridPrepared = true;
        }
        return $this;
    }

    public function prepareGrid()
    {
        return $this->_prepareGrid();
    }

    // ########################################

    protected function _prepareCollection()
    {
        // Get collection logs
        //--------------------------------
        $categoriesCollection = Mage::getModel('M2ePro/Listing_Auto_Category')->getCollection();
        $categoriesCollection->getSelect()->reset(Zend_Db_Select::FROM);
        $categoriesCollection->getSelect()->from(
            array('mlac' => Mage::getResourceModel('M2ePro/Listing_Auto_Category')->getMainTable())
        );
        $categoriesCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $categoriesCollection->getSelect()->columns(new Zend_Db_Expr('GROUP_CONCAT(`category_id`)'));
        $categoriesCollection->getSelect()->where('mlac.group_id = main_table.id');

        $collection = Mage::getModel('M2ePro/Listing_Auto_Category_Group')->getCollection();
        $collection->addFieldToFilter('main_table.listing_id', $this->getRequest()->getParam('listing_id'));
        $collection->getSelect()->columns(
            array('categories' => new Zend_Db_Expr('('.$categoriesCollection->getSelect().')'))
        );
        //--------------------------------

        // we need sort by id also, because create_date may be same for some adjustment entries
        //--------------------------------
        if ($this->getRequest()->getParam('sort', 'create_date') == 'create_date') {
            $collection->setOrder('id', $this->getRequest()->getParam('dir', 'DESC'));
        }
        //--------------------------------

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    // ########################################

    protected function _prepareColumns()
    {
        $this->addColumn('title', array(
            'header'    => Mage::helper('M2ePro')->__('Title'),
            'align'     => 'left',
            'width'     => '300px',
            'type'      => 'text',
            'escape'    => true,
            'index'     => 'title',
            'filter_index' => 'title'
        ));

        $this->addColumn('categories', array(
            'header'    => Mage::helper('M2ePro')->__('Categories'),
            'align'     => 'left',
//            'width'     => '300px',
            'type'      => 'text',
            'sortable'  => false,
            'filter'    => false,
            'frame_callback' => array($this, 'callbackColumnCategories')
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '50px',
            'type'      => 'text',
            'sortable'  => false,
            'filter'    => false,
            'actions'   => array(
                0 => array(
                    'label' => Mage::helper('M2ePro')->__('Edit Rule'),
                    'value' => 'categoryStepOne'
                ),
                1 => array(
                    'label' => Mage::helper('M2ePro')->__('Delete Rule'),
                    'value' => 'categoryDeleteGroup'
                )
            ),
            'frame_callback' => array($this, 'callbackColumnActions')
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //--------------------------------
    }

    // ########################################

    public function callbackColumnCategories($value, $row, $column, $isExport)
    {
        $groupId = (int)$row->getData('id');
        $categories = array_filter(explode(',', $row->getData('categories')));
        $count = count($categories);

        if ($count == 0 || $count > 3) {
            $total = Mage::helper('M2ePro')->__('Total');
            $html = "<strong>{$total}:&nbsp;</strong>&nbsp;{$count}";

            if (count($categories) > 3) {
                $details = Mage::helper('M2ePro')->__('details');
                $html .= <<<HTML
&nbsp;
[<a href="javascript: void(0);" onclick="ListingAutoActionHandlerObj.categoryStepOne({$groupId});">{$details}</a>]
HTML;
            }

            return $html;
        }

        $html = '';

        foreach ($categories as $categoryId) {
            $path = Mage::helper('M2ePro/Magento_Category')->getPath($categoryId);

            if (empty($path)) {
                continue;
            }

            if ($html != '') {
                $html .= '<br/>';
            }

            $path = implode(' > ', $path);
            $html .= '<span style="font-style: italic;">' . Mage::helper('M2ePro')->escapeHtml($path) . '</span>';
        }

        return $html;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $actions = $column->getActions();
        $id = (int)$row->getData('id');

        if (count($actions) == 1) {
            $action = reset($actions);
            $onclick = 'ListingAutoActionHandlerObj[\''.$action['value'].'\']('.$id.');';
            return '<a href="javascript: void(0);" onclick="' . $onclick . '">'.$action['label'].'</a>';
        }

        $optionsHtml = '<option></option>';

        foreach ($actions as $option) {
            $optionsHtml .= <<<HTML
            <option value="{$option['value']}">{$option['label']}</option>
HTML;
        }

        return <<<HTML
<div style="padding: 5px;">
    <select
        style="width: 100px;"
        onchange="ListingAutoActionHandlerObj[this.value]({$id});">
        {$optionsHtml}
    </select>
</div>
HTML;
    }

    // ########################################

    public function getRowUrl($item)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_listing_autoAction/getCategoryGroupGrid', array('_current' => true));
    }

    // ########################################
}