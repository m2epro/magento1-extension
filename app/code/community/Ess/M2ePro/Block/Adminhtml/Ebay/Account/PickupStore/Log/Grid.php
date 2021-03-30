<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_PickupStore_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Log_AbstractGrid
{
    protected $_listingProductPickupStoreStateId;

    //########################################

    public function setListingProductPickupStoreStateId($listingProductPickupStoreStateId)
    {
        $this->_listingProductPickupStoreStateId = $listingProductPickupStoreStateId;
    }

    public function getListingProductPickupStoreStateId()
    {
        return $this->_listingProductPickupStoreStateId;
    }

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayListingPickupStoreLogGrid');

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        // ---------------------------------------

        $this->isAjax = Mage::helper('M2ePro')->jsonEncode($this->getRequest()->isXmlHttpRequest());
    }

    //########################################

    protected function _prepareCollection()
    {
        $pickupStoreCollection = Mage::getModel('M2ePro/Ebay_Account_PickupStore_Log')->getCollection();
        $pickupStoreCollection->addFieldToFilter(
            'account_pickup_store_state_id', $this->getListingProductPickupStoreStateId()
        );

        // Set collection to grid
        $this->setCollection($pickupStoreCollection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'action', array(
            'header'    => Mage::helper('M2ePro')->__('Action'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'options',
            'index'     => 'action',
            'sortable'  => false,
            'filter_index' => 'action',
            'options' => $this->getActionTitles()
            )
        );

        $this->addColumn(
            'location_id', array(
            'header'    => Mage::helper('M2ePro')->__('Name / Location ID'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'location_id',
            'frame_callback' => array($this, 'callbackColumnTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $this->addColumn(
            'description', array(
            'header'    => Mage::helper('M2ePro')->__('Description'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'description',
            'filter_index' => 'main_table.description',
            'frame_callback' => array($this, 'callbackColumnDescription')
            )
        );

        $this->addColumn(
            'type', array(
            'header'=> Mage::helper('M2ePro')->__('Type'),
            'width' => '80px',
            'index' => 'type',
            'align' => 'right',
            'type'  => 'options',
            'sortable'  => false,
            'options' => $this->getLogTypeList(),
            'frame_callback' => array($this, 'callbackColumnType')
            )
        );

        $this->addColumn(
            'create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'type'      => 'datetime',
            'index'     => 'create_date',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $name = $row->getData('location_title');
        $locationId = $row->getData('location_id');

        $locationIdLabel = Mage::helper('M2ePro')->__('Location ID');

        return "{$name} <br/>
                <strong>{$locationIdLabel}</strong>: {$locationId} <br/>";
    }

    // ---------------------------------------

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            "main_table.location_title LIKE '%{$value}%'
            OR main_table.location_id LIKE '%{$value}%'"
        );
    }

    //########################################

    protected function getLogTypeList()
    {
        return array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE => Mage::helper('M2ePro')->__('Notice'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => Mage::helper('M2ePro')->__('Success'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => Mage::helper('M2ePro')->__('Warning'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => Mage::helper('M2ePro')->__('Error')
        );
    }

    protected function getActionTitles()
    {
        return Mage::helper('M2ePro/Module_Log')->getActionsTitlesByClass('Ebay_Account_PickupStore_Log');
    }

    //########################################

    protected function _toHtml()
    {
        $style = "<style>
                    #ebayListingPickupStoreLogGrid {
                        padding: 10px 0;
                    }
                    .grid th, .grid td {
                        padding: 5px 4px 5px 4px !important;
                    }
                  </style>";
        $javaScriptsMain = <<<HTML
        <script>

            ProductGridObj = new ListingProductGrid();
            ProductGridObj.setGridId('{$this->getJsObjectName()}');

            var init = function () {
                {$this->getJsObjectName()}.doFilter = ProductGridObj.setFilter;
                {$this->getJsObjectName()}.resetFilter = ProductGridObj.resetFilter;
            };

            {$this->isAjax} ? init()
                            : Event.observe(window, 'load', init);

        </script>
HTML;

        return parent::_toHtml() . $style . $javaScriptsMain;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/*/logGridAjax', array(
            '_current' => true,
            'listing_product_pickup_store_state' => $this->getListingProductPickupStoreStateId()
            )
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

    protected function getComponentMode()
    {
        return Ess_M2ePro_Helper_Component_Ebay::NICK;
    }

    //########################################
}
