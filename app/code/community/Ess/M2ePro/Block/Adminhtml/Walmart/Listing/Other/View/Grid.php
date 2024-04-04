<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Other_View_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        /** @var $this->connRead Varien_Db_Adapter_Pdo_Mysql */
        $this->connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $this->setId('walmartListingOtherGrid');

        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Other');

        $collection->getSelect()->joinLeft(
            array('mp' => Mage::getResourceModel('M2ePro/Marketplace')->getMainTable()),
            'mp.id = main_table.marketplace_id',
            array('marketplace_title' => 'mp.title')
        )->joinLeft(
            array('am' => Mage::getResourceModel('M2ePro/Walmart_Marketplace')->getMainTable()),
            'am.marketplace_id = main_table.marketplace_id',
            array('currency' => 'am.default_currency')
        );

        // Add Filter By Account
        if ($this->getRequest()->getParam('account')) {
            $collection->addFieldToFilter(
                'main_table.account_id',
                $this->getRequest()->getParam('account')
            );
        }

        // Add Filter By Marketplace
        if ($this->getRequest()->getParam('marketplace')) {
            $collection->addFieldToFilter(
                'main_table.marketplace_id',
                $this->getRequest()->getParam('marketplace')
            );
        }

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id',
            array(
                'header'                    => Mage::helper('M2ePro')->__('Product ID'),
                'align'                     => 'left',
                'width'                     => '80px',
                'type'                      => 'number',
                'index'                     => 'product_id',
                'filter_index'              => 'product_id',
                'frame_callback'            => array($this, 'callbackColumnProductId'),
                'filter'                    => 'M2ePro/adminhtml_grid_column_filter_productId',
                'filter_condition_callback' => array($this, 'callbackFilterProductId')
            )
        );

        $this->addColumn(
            'title',
            array(
                'header'                    => Mage::helper('M2ePro')->__('Title / SKU'),
                'align'                     => 'left',
                'type'                      => 'text',
                'index'                     => 'title',
                'filter_index'              => 'second_table.title',
                'frame_callback'            => array($this, 'callbackColumnProductTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $this->addColumn(
            'gtin',
            array(
                'header'                    => Mage::helper('M2ePro')->__('GTIN'),
                'align'                     => 'left',
                'width'                     => '160px',
                'type'                      => 'text',
                'index'                     => 'gtin',
                'filter_index'              => 'gtin',
                'show_edit_identifier'      => false,
                'renderer'                  => 'M2ePro/adminhtml_walmart_grid_column_renderer_gtin',
                'filter_condition_callback' => array($this, 'callbackFilterGtin')
            )
        );

        $this->addColumn(
            'online_qty',
            array(
                'header'                    => Mage::helper('M2ePro')->__('QTY'),
                'align'                     => 'right',
                'width'                     => '160px',
                'type'                      => 'number',
                'index'                     => 'online_qty',
                'filter_index'              => 'online_qty',
                'renderer'                  => 'M2ePro/adminhtml_walmart_grid_column_renderer_qty',
                'filter_condition_callback' => array($this, 'callbackFilterQty')
            )
        );

        $priceColumn = array(
            'header'                    => Mage::helper('M2ePro')->__('Price'),
            'align'                     => 'right',
            'width'                     => '160px',
            'type'                      => 'number',
            'index'                     => 'online_price',
            'filter_index'              => 'online_price',
            'frame_callback'            => array($this, 'callbackColumnPrice'),
            'filter_condition_callback' => array($this, 'callbackFilterPrice')
        );

        $this->addColumn('online_price', $priceColumn);

        $this->addColumn(
            'status',
            array(
                'header'         => Mage::helper('M2ePro')->__('Status'),
                'width'          => '170px',
                'index'          => 'status',
                'filter_index'   => 'main_table.status',
                'type'           => 'options',
                'sortable'       => false,
                'options'        => array(
                    Ess_M2ePro_Model_Listing_Product::STATUS_LISTED  => Mage::helper('M2ePro')->__('Active'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE => Mage::helper('M2ePro')->__('Inactive'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED => Mage::helper('M2ePro')->__('Incomplete')
                ),
                'frame_callback' => array($this, 'callbackColumnStatus')
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set mass-action identifiers
        // ---------------------------------------
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        $this->getMassactionBlock()->setGroups(
            array(
                'mapping' => Mage::helper('M2ePro')->__('Linking'),
                'other'   => Mage::helper('M2ePro')->__('Other')
            )
        );

        // Set mass-action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem(
            'autoMapping',
            array(
                'label'   => Mage::helper('M2ePro')->__('Link Item(s) Automatically'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'mapping'
        );
        $this->getMassactionBlock()->addItem(
            'moving',
            array(
                'label'   => Mage::helper('M2ePro')->__('Move Item(s) to Listing'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'other'
        );
        $this->getMassactionBlock()->addItem(
            'removing',
            array(
                'label'   => Mage::helper('M2ePro')->__('Remove Item(s)'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'other'
        );
        $this->getMassactionBlock()->addItem(
            'unmapping',
            array(
                'label'   => Mage::helper('M2ePro')->__('Unlink Item(s)'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'mapping'
        );

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            $productTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('title'));
            $productTitle = Mage::helper('M2ePro')->escapeJs($productTitle);
            if (strlen($productTitle) > 60) {
                $productTitle = substr($productTitle, 0, 60) . '...';
            }

            $htmlValue = '&nbsp;<a href="javascript:void(0);"
                                    onclick="ListingOtherMappingObj.openPopUp(
                                    ' . (int)$row->getId() . ',
                                    \'' . $productTitle . '\'
                                    );">' . Mage::helper('M2ePro')->__('Link') . '</a>';

            return $htmlValue;
        }

        $htmlValue = '&nbsp<a href="'
            . $this->getUrl(
                'adminhtml/catalog_product/edit',
                array('id' => $row->getData('product_id'))
            )
            . '" target="_blank">'
            . $row->getData('product_id')
            . '</a>';

        $htmlValue .= '&nbsp&nbsp&nbsp<a href="javascript:void(0);"'
            . ' onclick="WalmartListingOtherGridObj.movingHandler.getGridHtml('
            . Mage::helper('M2ePro')->jsonEncode(array((int)$row->getData('id')))
            . ')">'
            . Mage::helper('M2ePro')->__('Move')
            . '</a>';

        return $htmlValue;
    }

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        if ($value === null) {
            $value = '<i style="color:gray;">receiving...</i>';
        } else {
            $value = '<span>' . Mage::helper('M2ePro')->escapeHtml($value) . '</span>';
        }

        $tempSku = $row->getData('sku');
        empty($tempSku) && $tempSku = Mage::helper('M2ePro')->__('N/A');

        $value .= '<br/><strong>'
            . Mage::helper('M2ePro')->__('SKU')
            . ':</strong> '
            . Mage::helper('M2ePro')->escapeHtml($tempSku);

        return $value;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ($value === null || $value === '' ||
            ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED &&
                !$row->getData('is_online_price_invalid'))) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $currency = Mage::helper('M2ePro/Component_Walmart')
            ->getCachedObject('Marketplace', $row->getData('marketplace_id'))
            ->getChildObject()
            ->getDefaultCurrency();

        return Mage::app()->getLocale()->currency($currency)->toCurrency($value);
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        switch ($row->getData('status')) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $value = '<span style="color: green;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE:
                $value = '<span style="color: red;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $value = '<span style="color: orange; font-weight: bold;">' . $value . '</span>';
                break;

            default:
                break;
        }

        /** @var Ess_M2ePro_Model_Listing_Other $listingOther */
        $listingOther = Mage::helper('M2ePro/Component_Walmart')
            ->getObject('Listing_Other', $row->getData('id'));

        $statusChangeReasons = $listingOther->getChildObject()->getStatusChangeReasons();

        return $value . $this->getStatusChangeReasons($statusChangeReasons);
    }

    //########################################

    protected function callbackFilterProductId($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = '';

        if (isset($value['from']) && $value['from'] != '') {
            $where .= 'product_id >= ' . (int)$value['from'];
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $where .= 'product_id <= ' . (int)$value['to'];
        }

        if (isset($value['is_mapped']) && $value['is_mapped'] !== '') {
            if (!empty($where)) {
                $where = '(' . $where . ') AND ';
            }

            if ($value['is_mapped']) {
                $where .= 'product_id IS NOT NULL';
            } else {
                $where .= 'product_id IS NULL';
            }
        }

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('second_table.title LIKE ? OR second_table.sku LIKE ?', '%' . $value . '%');
    }

    protected function callbackFilterQty($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = '';

        if (isset($value['from']) && $value['from'] != '') {
            $where .= 'online_qty >= ' . (int)$value['from'];
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $where .= 'online_qty <= ' . (int)$value['to'];
        }

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterGtin($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = <<<SQL
gtin LIKE '%{$value}%' OR
upc LIKE '%{$value}%' OR
wpid LIKE '%{$value}%' OR
item_id LIKE '%{$value}%'
SQL;

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterPrice($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = '';

        if (isset($value['from']) && $value['from'] != '') {
            $where .= 'online_price >= ' . (float)$value['from'];
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $where .= 'online_price <= ' . (float)$value['to'];
        }

        $collection->getSelect()->where($where);
    }

    //########################################

    protected function getStatusChangeReasons($statusChangeReasons)
    {
        if (empty($statusChangeReasons)) {
            return '';
        }

        $html = '<li style="margin-bottom: 5px;">'
            . implode('</li><li style="margin-bottom: 5px;">', $statusChangeReasons)
            . '</li>';

        return <<<HTML
        <div style="display: inline-block; width: 16px; margin-left: 3px;">
            <img class="tool-tip-image"
                 style="vertical-align: middle;"
                 src="{$this->getSkinUrl('M2ePro/images/i_icon.png')}">
            <span class="tool-tip-message tool-tip-message tip-left" style="display:none;">
                <img src="{$this->getSkinUrl('M2ePro/images/i_logo.png')}">
                <ul>
                    {$html}
                </ul>
            </span>
        </div>
HTML;
    }

    //########################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<HTML
<script type="text/javascript">

    if (typeof WalmartListingOtherGridObj != 'undefined') {
        WalmartListingOtherGridObj.afterInitPage();
    }

    Event.observe(window, 'load', function() {
        setTimeout(function() {
            WalmartListingOtherGridObj.afterInitPage();
        }, 350);
    });

</script>
HTML;

        return parent::_toHtml() . $javascriptsMain;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_walmart_listing_other/grid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################
}
