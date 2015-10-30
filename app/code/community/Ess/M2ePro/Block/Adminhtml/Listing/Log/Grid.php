<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Listing_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Log_Grid_Abstract
{
    protected $viewComponentHelper = NULL;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialize view
        // ---------------------------------------
        $view = Mage::helper('M2ePro/View')->getCurrentView();
        $this->viewComponentHelper = Mage::helper('M2ePro/View')->getComponentHelper($view);
        // ---------------------------------------

        $channel = $this->getRequest()->getParam('channel');

        // Initialization block
        // ---------------------------------------
        $this->setId($view . ucfirst($channel) . 'ListingLogGrid' . $this->getEntityId());
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        // Get collection logs
        // ---------------------------------------
        $collection = Mage::getModel('M2ePro/Listing_Log')->getCollection();
        // ---------------------------------------

        // Set listing filter
        // ---------------------------------------
        if ($this->getEntityId()) {
            if ($this->isListingProductLog() && $this->getListingProduct()->isComponentModeAmazon() &&
                $this->getListingProduct()->getChildObject()->getVariationManager()->isRelationParentType()) {
                $collection->addFieldToFilter(
                    array(
                        self::LISTING_PRODUCT_ID_FIELD,
                        self::LISTING_PARENT_PRODUCT_ID_FIELD
                    ),
                    array(
                        array(
                            'attribute' => self::LISTING_PRODUCT_ID_FIELD,
                            'eq' => $this->getEntityId()
                        ),
                        array(
                            'attribute' => self::LISTING_PARENT_PRODUCT_ID_FIELD,
                            'eq' => $this->getEntityId()
                        )
                    )
                );
            } else {
                $collection->addFieldToFilter($this->getEntityField(), $this->getEntityId());
            }
        }
        // ---------------------------------------

        // prepare components
        // ---------------------------------------
        $channel = $this->getRequest()->getParam('channel');
        if (!empty($channel) && $channel != Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::CHANNEL_ID_ALL) {
            $collection->getSelect()->where('component_mode = ?', $channel);
        } else {
            $components = $this->viewComponentHelper->getActiveComponents();
            $collection->getSelect()
                ->where('component_mode IN(\'' . implode('\',\'', $components) . '\') OR component_mode IS NULL');
        }
        // ---------------------------------------

        // we need sort by id also, because create_date may be same for some adjustment entries
        // ---------------------------------------
        if ($this->getRequest()->getParam('sort', 'create_date') == 'create_date') {
            $collection->setOrder('id', $this->getRequest()->getParam('dir', 'DESC'));
        }
        // ---------------------------------------

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'width'     => '150px',
            'index'     => 'create_date'
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('M2ePro')->__('Action'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'options',
            'index'     => 'action',
            'sortable'  => false,
            'filter_index' => 'main_table.action',
            'options' => $this->getActionTitles()
        ));

        if (!$this->getEntityId()) {
            $this->addColumn('listing_title', array(
                'header'    => Mage::helper('M2ePro')->__('Listing Title / ID'),
                'align'     => 'left',
                'width'     => '150px',
                'type'      => 'text',
                'index'     => 'listing_title',
                'filter_index' => 'main_table.listing_title',
                'frame_callback' => array($this, 'callbackColumnListingTitleID'),
                'filter_condition_callback' => array($this, 'callbackFilterListingTitleID')
            ));
        }

        if (!$this->isListingProductLog()) {
            $this->addColumn('product_title', array(
                'header' => Mage::helper('M2ePro')->__('Product Title / ID'),
                'align' => 'left',
                'width'     => '280px',
                'type' => 'text',
                'index' => 'product_title',
                'filter_index' => 'main_table.product_title',
                'frame_callback' => array($this, 'callbackColumnProductTitleID'),
                'filter_condition_callback' => array($this, 'callbackFilterProductTitleID')
            ));
        }

        if ($this->isListingProductLog() && $this->getListingProduct()->isComponentModeAmazon() &&
            ($this->getListingProduct()->getChildObject()->getVariationManager()->isRelationParentType() ||
             $this->getListingProduct()->getChildObject()->getVariationManager()->isRelationChildType() ||
             $this->getListingProduct()->getChildObject()->getVariationManager()->isIndividualType())) {

            $this->addColumn('attributes', array(
                'header' => Mage::helper('M2ePro')->__('Variation'),
                'align' => 'left',
                'width'     => '250px',
                'index' => 'additional_data',
                'sortable'  => false,
                'filter_index' => 'main_table.additional_data',
                'frame_callback' => array($this, 'callbackColumnAttributes'),
                'filter_condition_callback' => array($this, 'callbackFilterAttributes')
            ));
        }

        $this->addColumn('description', array(
            'header'    => Mage::helper('M2ePro')->__('Description'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'description',
            'filter_index' => 'main_table.description',
            'frame_callback' => array($this, 'callbackDescription')
        ));

        $this->addColumn('initiator', array(
            'header'=> Mage::helper('M2ePro')->__('Run Mode'),
            'width' => '80px',
            'index' => 'initiator',
            'align' => 'right',
            'type'  => 'options',
            'sortable'  => false,
            'options' => $this->_getLogInitiatorList(),
            'frame_callback' => array($this, 'callbackColumnInitiator')
        ));

        $this->addColumn('type', array(
            'header'=> Mage::helper('M2ePro')->__('Type'),
            'width' => '80px',
            'index' => 'type',
            'align' => 'right',
            'type'  => 'options',
            'sortable'  => false,
            'options' => $this->_getLogTypeList(),
            'frame_callback' => array($this, 'callbackColumnType')
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------
    }

    //########################################

    public function callbackColumnListingTitleID($value, $row, $column, $isExport)
    {
        if (strlen($value) > 50) {
            $value = substr($value, 0, 50) . '...';
        }

        $value = Mage::helper('M2ePro')->escapeHtml($value);

        if ($row->getData('listing_id')) {

            $url = $this->getUrl(
                '*/adminhtml_'.$row->getData('component_mode').'_listing/view',
                array('id' => $row->getData('listing_id'))
            );

            $value = '<a target="_blank" href="'.$url.'">' .
                         $value .
                     '</a><br/>ID: '.$row->getData('listing_id');
        }

        return $value;
    }

    public function callbackColumnProductTitleID($value, $row, $column, $isExport)
    {
        if (!$row->getData('product_id')) {
            return $value;
        }

        $url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getData('product_id')));
        $value = '<a target="_blank" href="'.$url.'" target="_blank">'.
                     Mage::helper('M2ePro')->escapeHtml($value).
                 '</a><br/>ID: '.$row->getData('product_id');

        $additionalData = json_decode($row->getData('additional_data'), true);
        if (empty($additionalData['variation_options'])) {
            return $value;
        }

        $value .= '<div style="font-size: 11px; color: grey;">';
        foreach ($additionalData['variation_options'] as $attribute => $option) {
            !$option && $option = '--';
            $value .= '<strong>'.
                          Mage::helper('M2ePro')->escapeHtml($attribute) .
                      '</strong>:&nbsp;'.
                      Mage::helper('M2ePro')->escapeHtml($option) . '<br/>';
        }
        $value .= '</div>';

        return $value;
    }

    public function callbackColumnAttributes($value, $row, $column, $isExport)
    {
        $additionalData = json_decode($row->getData('additional_data'), true);
        if (empty($additionalData['variation_options'])) {
            return '';
        }

        $result = '<div style="font-size: 11px; color: grey;">';
        foreach ($additionalData['variation_options'] as $attribute => $option) {
            !$option && $option = '--';
            $result .= '<strong>'.
                           Mage::helper('M2ePro')->escapeHtml($attribute) .
                       '</strong>:&nbsp;'.
                       Mage::helper('M2ePro')->escapeHtml($option) . '<br/>';
        }
        $result .= '</div>';

        return $result;
    }

    //########################################

    protected function callbackFilterListingTitleID($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $where = 'listing_title LIKE ' . $collection->getSelect()->getAdapter()->quote('%'. $value .'%');
        is_numeric($value) && $where .= ' OR listing_id = ' . $value;

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterProductTitleID($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $where = 'product_title LIKE ' . $collection->getSelect()->getAdapter()->quote('%'. $value .'%');
        is_numeric($value) && $where .= ' OR product_id = ' . $value;

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterAttributes($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('additional_data LIKE ?', '%'. $value .'%');
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/'.$this->getActionName(), array(
            '_current'=>true,
            'channel' => $this->getRequest()->getParam('channel')
        ));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    abstract protected function getActionTitles();

    //########################################
}