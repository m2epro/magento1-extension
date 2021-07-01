<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Mapping_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('listingMappingGrid');

        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);

        /** @var Ess_M2ePro_Model_Account $account */
        $accountId = $this->getRequest()->getParam('account_id');
        if ($account = Mage::helper('M2ePro/Component')->getCachedUnknownObject('Account', $accountId)) {
            $this->_storeId = $account->getChildObject()->getRelatedStoreId(
                $this->getRequest()->getParam('marketplace_id')
            );
        }
    }

    protected function _prepareCollection()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );

        $collection->setStoreId($this->_storeId)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('type_id');

        $collection->joinStockItem(
            array(
                'qty'         => 'qty',
                'is_in_stock' => 'is_in_stock'
            )
        );

        $collection->addFieldToFilter(
            array(
                array(
                    'attribute' => 'type_id',
                    'in'        => Mage::helper('M2ePro/Magento_Product')->getOriginKnownTypes()
                ),
            )
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id',
            array(
                'header'       => Mage::helper('M2ePro')->__('Product ID'),
                'align'        => 'right',
                'type'         => 'number',
                'width'        => '100px',
                'index'        => 'entity_id',
                'filter_index' => 'entity_id',
                'store_id'     => $this->_storeId,
                'renderer'     => 'M2ePro/adminhtml_grid_column_renderer_productId'
            )
        );

        $this->addColumn(
            'title',
            array(
                'header'                    => Mage::helper('M2ePro')->__('Product Title / Product SKU'),
                'align'                     => 'left',
                'type'                      => 'text',
                'width'                     => '200px',
                'index'                     => 'name',
                'filter_index'              => 'name',
                'frame_callback'            => array($this, 'callbackColumnTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $this->addColumn(
            'type',
            array(
                'header'       => Mage::helper('M2ePro')->__('Type'),
                'align'        => 'left',
                'width'        => '120px',
                'type'         => 'options',
                'sortable'     => false,
                'index'        => 'type_id',
                'filter_index' => 'type_id',
                'options'      => Mage::helper('M2ePro/Magento_Product')->getTypesOptionArray()
            )
        );

        $this->addColumn(
            'stock_availability',
            array(
                'header'         => Mage::helper('M2ePro')->__('Stock Availability'),
                'width'          => '100px',
                'index'          => 'is_in_stock',
                'filter_index'   => 'is_in_stock',
                'type'           => 'options',
                'sortable'       => false,
                'options'        => array(
                    1 => Mage::helper('M2ePro')->__('In Stock'),
                    0 => Mage::helper('M2ePro')->__('Out of Stock')
                ),
                'frame_callback' => array($this, 'callbackColumnStockAvailability')
            )
        );

        $this->addColumn(
            'actions',
            array(
                'header'         => Mage::helper('M2ePro')->__('Actions'),
                'align'          => 'left',
                'type'           => 'text',
                'width'          => '125px',
                'filter'         => false,
                'sortable'       => false,
                'frame_callback' => array($this, 'callbackColumnActions'),
            )
        );

    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $value = '<div style="margin-left: 3px">' . Mage::helper('M2ePro')->escapeHtml($value);

        $tempSku = $row->getData('sku');
        if ($tempSku === null) {
            $tempSku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();
        }

        $value .= '<br/><strong>' . Mage::helper('M2ePro')->__('SKU') . ':</strong> ';
        $value .= Mage::helper('M2ePro')->escapeHtml($tempSku) . '</div>';

        return $value;
    }

    public function callbackColumnType($value, $row, $column, $isExport)
    {
        return '<div style="margin-left: 3px">' . Mage::helper('M2ePro')->escapeHtml($value) . '</div>';
    }

    public function callbackColumnStockAvailability($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('is_in_stock') <= 0) {
            return '<span style="color: red;">' . $value . '</span>';
        }

        return $value;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $actions = '&nbsp;<a href="javascript:void(0);"';
        $actions .= 'onclick="' . $this->getData('mapping_handler_js') . '.';
        $actions .= $this->getData('mapping_action') . '(' . $row->getId() . ');">';
        $actions .= Mage::helper('M2ePro')->__('Link To This Product') . '</a>';

        return $actions;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            array(
                array('attribute' => 'sku', 'like' => '%' . $value . '%'),
                array('attribute' => 'name', 'like' => '%' . $value . '%')
            )
        );

    }

    //########################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<HTML
<script type="text/javascript">

    $$('#listingOtherMappingGrid div.grid th').each(function(el) {
        el.style.padding = '2px 4px';
    });

    $$('#listingOtherMappingGrid div.grid td').each(function(el) {
        el.style.padding = '2px 4px';
    });

</script>
HTML;

        return parent::_toHtml() . $javascriptsMain;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl(
            $this->getData('grid_url'),
            array(
                '_current' => true,
                'component_mode' => $this->getRequest()->getParam('component_mode')
            )
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################
}
