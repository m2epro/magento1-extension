<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Listing_Other_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Log_Grid_Abstract
{
    protected $viewComponentHelper = NULL;

    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialize view
        //------------------------------
        $view = Mage::helper('M2ePro/View')->getCurrentView();
        $this->viewComponentHelper = Mage::helper('M2ePro/View')->getComponentHelper($view);
        //------------------------------

        $channel = $this->getRequest()->getParam('channel');

        // Initialization block
        //------------------------------
        $this->setId($view . ucfirst($channel) . 'ListingOtherLogGrid' . $this->getEntityId());
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    // ####################################

    protected function _prepareCollection()
    {
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Get collection logs
        //--------------------------------
        $collection = Mage::getModel('M2ePro/Listing_Other_Log')->getCollection();
        //--------------------------------

        // Join amazon_listings_table
        //--------------------------------
        $collection->getSelect()
            ->joinLeft(array('lo' => Mage::getResourceModel('M2ePro/Listing_Other')->getMainTable()),
                       '(`main_table`.listing_other_id = `lo`.id)',
                       array(
                           'account_id'     => 'lo.account_id',
                           'marketplace_id' => 'lo.marketplace_id'
                       )
            )
            ->joinLeft(array('ea' => Mage::getResourceModel('M2ePro/Ebay_Account')->getMainTable()),
                             '(`lo`.account_id = `ea`.account_id)',
                             array('account_mode' => 'ea.mode')
            );
        //--------------------------------

        // Set listing filter
        //--------------------------------
        if (isset($listingData['id'])) {
            $collection->addFieldToFilter('`main_table`.listing_other_id', $listingData['id']);
        }
        //--------------------------------

        // prepare components
        //--------------------------------
        $channel = $this->getRequest()->getParam('channel');
        if (!empty($channel) && $channel != Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::CHANNEL_ID_ALL) {
            $collection->getSelect()->where('main_table.component_mode = ?', $channel);
        } else {
            $components = $this->viewComponentHelper->getActiveComponents();
            $collection->getSelect()
                ->where('main_table.component_mode IN(\''.implode('\',\'',$components).'\')
                        OR main_table.component_mode IS NULL');
        }
        //--------------------------------

        // we need sort by id also, because create_date may be same for some adjacents entries
        //--------------------------------
        if ($this->getRequest()->getParam('sort', 'create_date') == 'create_date') {
            $collection->setOrder('id', $this->getRequest()->getParam('dir', 'DESC'));
        }
        //--------------------------------

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $columnTitles = $this->getColumnTitles();

        $this->addColumn('create_date', array(
            'header'    => $columnTitles['create_date'],
            'align'     => 'left',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'width'     => '150px',
            'index'     => 'create_date',
            'filter_index' => 'main_table.create_date',
        ));

        $this->addColumn('action', array(
            'header'    => $columnTitles['action'],
            'align'     => 'left',
            'width'     => '250px',
            'type'      => 'options',
            'index'     => 'action',
            'sortable'  => false,
            'filter_index' => 'main_table.action',
            'options' => $this->getActionTitles(),
        ));

        $this->addColumn('identifier', array(
            'header' => $columnTitles['identifier'],
            'align'  => 'left',
            'width'  => '100px',
            'type'   => 'text',
            'index'  => 'identifier',
            'filter_index' => 'main_table.identifier',
            'frame_callback' => array($this, 'callbackColumnIdentifier')
        ));

        $this->addColumn('title', array(
            'header'    => $columnTitles['title'],
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'title',
            'filter_index' => 'main_table.title',
            'frame_callback' => array($this, 'callbackColumnTitle')
        ));

        $this->addColumn('description', array(
            'header'    => $columnTitles['description'],
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'description',
            'filter_index' => 'main_table.description',
            'frame_callback' => array($this, 'callbackDescription')
        ));

        $this->addColumn('initiator', array(
            'header'=> $columnTitles['initiator'],
            'width' => '80px',
            'index' => 'initiator',
            'align' => 'right',
            'type'  => 'options',
            'sortable'  => false,
            'options' => $this->_getLogInitiatorList(),
            'frame_callback' => array($this, 'callbackColumnInitiator')
        ));

        $this->addColumn('type', array(
            'header'=> $columnTitles['type'],
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
        //--------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //--------------------------------
    }

    // ####################################

    public function callbackColumnIdentifier($value, $row, $column, $isExport)
    {
        $identifier = Mage::helper('M2ePro')->__('N/A');

        if (is_null($value) || $value === '') {
            return $identifier;
        }

        $accountMode   = $row->getData('account_mode');
        $marketplaceId = $row->getData('marketplace_id');

        switch ($row->getData('component_mode')) {
            case Ess_M2ePro_Helper_Component_Ebay::NICK:
                $url = Mage::helper('M2ePro/Component_Ebay')->getItemUrl($value, $accountMode, $marketplaceId);
                $identifier = '<a href="' . $url . '" target="_blank">' . $value . '</a>';
                break;

            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl($value, $marketplaceId);
                $identifier = '<a href="' . $url . '" target="_blank">' . $value . '</a>';
                break;

            case Ess_M2ePro_Helper_Component_Buy::NICK:
                $url = Mage::helper('M2ePro/Component_Buy')->getItemUrl($value);
                $identifier = '<a href="' . $url . '" target="_blank">' . $value . '</a>';
                break;
        }

        return $identifier;
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return '<span>' . Mage::helper('M2ePro')->escapeHtml($value) . '</span>';
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/listingOtherGrid', array(
            '_current'=>true,
            'channel' => $this->getRequest()->getParam('channel')
        ));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################

    abstract protected function getColumnTitles();

    // ####################################

    abstract protected function getActionTitles();

    // ####################################
}