<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Renderer_Qty as OnlineQty;

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_PickupStore_Step_Products_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->_listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $this->setId('ebayListingPickupStoreStepProducts');

        $this->_showAdvancedFilterProductsOption = false;
    }

    //########################################

    protected function isShowRuleBlock()
    {
        return false;
    }

    public function getAdvancedFilterButtonHtml()
    {
        return '';
    }

    //########################################

    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ?
                $column->getFilterIndex() : $column->getIndex();
            $collection->getSelect()->order($columnIndex.' '.strtoupper($column->getDir()));
        }

        return $this;
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );
        $collection->setListingProductModeOn();
        $collection->setListing($this->_listing->getId());
        $collection->setStoreId($this->_listing->getStoreId());
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id'     => 'id',
                'status' => 'status',
                'component_mode' => 'component_mode',
                'additional_data' => 'additional_data'
            ),
            '{{table}}.listing_id='.(int)$this->_listing->getData('id')
        );
        $collection->joinTable(
            array('elp' => 'M2ePro/Ebay_Listing_Product'),
            'listing_product_id=id',
            array(
                'listing_product_id'    => 'listing_product_id',
                'end_date'              => 'end_date',
                'start_date'            => 'start_date',
                'online_title'          => 'online_title',
                'online_sku'            => 'online_sku',
                'available_qty'         => new Zend_Db_Expr('(elp.online_qty - elp.online_qty_sold)'),
                'ebay_item_id'          => 'ebay_item_id',
                'online_main_category'  => 'online_main_category',
                'online_qty_sold'       => 'online_qty_sold',
                'online_bids'           => 'online_bids',
                'online_start_price'    => 'online_start_price',
                'online_current_price'  => 'online_current_price',
                'online_reserve_price'  => 'online_reserve_price',
                'online_buyitnow_price' => 'online_buyitnow_price',
                'template_category_id'  => 'template_category_id',
                'min_online_price'      => 'IF(
                    (`t`.`variation_min_price` IS NULL),
                    `elp`.`online_current_price`,
                    `t`.`variation_min_price`
                )',
                'max_online_price'      => 'IF(
                    (`t`.`variation_max_price` IS NULL),
                    `elp`.`online_current_price`,
                    `t`.`variation_max_price`
                )'
            )
        );
        $collection->joinTable(
            array('ei' => 'M2ePro/Ebay_Item'),
            'id=ebay_item_id',
            array(
                'item_id' => 'item_id',
            ),
            null,
            'left'
        );
        $collection->getSelect()->joinLeft(
            new Zend_Db_Expr(
                '(
                SELECT
                    `mlpv`.`listing_product_id`,
                    MIN(`melpv`.`online_price`) as variation_min_price,
                    MAX(`melpv`.`online_price`) as variation_max_price
                FROM `'. Mage::getResourceModel('M2ePro/Listing_Product_Variation')->getMainTable() .'` AS `mlpv`
                INNER JOIN `' .
                Mage::getResourceModel('M2ePro/Ebay_Listing_Product_Variation')->getMainTable() .
                '` AS `melpv`
                    ON (`mlpv`.`id` = `melpv`.`listing_product_variation_id`)
                WHERE `melpv`.`status` != ' . Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED . '
                GROUP BY `mlpv`.`listing_product_id`
            )'
            ),
            'elp.listing_product_id=t.listing_product_id',
            array(
                'variation_min_price' => 'variation_min_price',
                'variation_max_price' => 'variation_max_price',
            )
        );
        // ---------------------------------------

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    //########################################

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id', array(
                'header'   => Mage::helper('M2ePro')->__('Product ID'),
                'align'    => 'right',
                'width'    => '100px',
                'type'     => 'number',
                'index'    => 'entity_id',
                'store_id' => $this->_listing->getStoreId(),
                'renderer' => 'M2ePro/adminhtml_grid_column_renderer_productId'
            )
        );

        $this->addColumn(
            'name', array(
                'header'    => Mage::helper('M2ePro')->__('Product Title / Product SKU'),
                'align'     => 'left',
                'type'      => 'text',
                'index'     => 'online_title',
                'frame_callback' => array($this, 'callbackColumnTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $this->addColumn(
            'ebay_item_id', array(
                'header'         => Mage::helper('M2ePro')->__('Item ID'),
                'align'          => 'left',
                'width'          => '100px',
                'type'           => 'text',
                'index'          => 'item_id',
                'account_id'     => $this->_listing->getAccountId(),
                'marketplace_id' => $this->_listing->getMarketplaceId(),
                'renderer'       => 'M2ePro/adminhtml_ebay_grid_column_renderer_itemId'
            )
        );

        $this->addColumn(
            'available_qty', array(
                'header'   => Mage::helper('M2ePro')->__('Available QTY'),
                'align'    => 'right',
                'width'    => '50px',
                'type'     => 'number',
                'index'    => 'available_qty',
                'sortable' => true,
                'filter'   => false,
                'renderer' => 'M2ePro/adminhtml_ebay_grid_column_renderer_qty',
                'render_online_qty' => OnlineQty::ONLINE_AVAILABLE_QTY
            )
        );

        $dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
        if ($dir == 'desc') {
            $priceSortField = 'max_online_price';
        } else {
            $priceSortField = 'min_online_price';
        }

        $this->addColumn(
            'price', array(
                'header'       => Mage::helper('M2ePro')->__('Price'),
                'align'        =>'right',
                'width'        => '75px',
                'type'         => 'number',
                'currency'     => $this->_listing->getMarketplace()->getChildObject()->getCurrency(),
                'index'        => $priceSortField,
                'filter_index' => $priceSortField,
                'renderer'     => 'M2ePro/adminhtml_ebay_grid_column_renderer_minMaxPrice',
                'filter_condition_callback' => array($this, 'callbackFilterPrice')
            )
        );

        $this->addColumn(
            'status', array(
                'header'       => Mage::helper('M2ePro')->__('Status'),
                'width'        => '80px',
                'index'        => 'status',
                'filter_index' => 'status',
                'type'         => 'options',
                'sortable'     => false,
                'options'      => array(
                    Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_LISTED     => Mage::helper('M2ePro')->__('Listed'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN    => Mage::helper('M2ePro')->__('Listed (Hidden)'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_SOLD       => Mage::helper('M2ePro')->__('Sold'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED    => Mage::helper('M2ePro')->__('Stopped'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED   => Mage::helper('M2ePro')->__('Finished'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED    => Mage::helper('M2ePro')->__('Pending')
                ),
                'frame_callback' => array($this, 'callbackColumnStatus')
            )
        );

        return parent::_prepareColumns();
    }

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $title = $row->getName();
        $onlineTitle = $row->getData('online_title');
        !empty($onlineTitle) && $title = $onlineTitle;

        $title = Mage::helper('M2ePro')->escapeHtml($title);
        $valueHtml = '<span class="product-title-value">' . $title . '</span>';

        $sku = $row->getData('sku');
        if ($sku === null) {
            $sku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))
                                                           ->getSku();
        }

        $onlineSku = $row->getData('online_sku');
        !empty($onlineSku) && $sku = $onlineSku;

        $valueHtml .= '<br/>' .
            '<strong>' . Mage::helper('M2ePro')->__('SKU') . ':</strong>&nbsp;' .
            Mage::helper('M2ePro')->escapeHtml($sku);

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Ebay')
            ->getObject('Listing_Product', $row->getData('listing_product_id'));

        if (!$listingProduct->getChildObject()->isVariationsReady()) {
            return $valueHtml;
        }

        $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));
        $productAttributes = array_keys($additionalData['variations_sets']);
        $valueHtml .= '<div style="font-size: 11px; font-weight: bold; color: grey; margin: 7px 0 0 7px">';
        $valueHtml .= implode(', ', $productAttributes);
        $valueHtml .= '</div>';

        return $valueHtml;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $html = '';

        switch ((int)$row->getData('status')) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $html = '<span style="color: gray;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $html = '<span style="color: green;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN:
                $html = '<span style="color: red;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_SOLD:
                $html = '<span style="color: brown;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                $html = '<span style="color: red;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED:
                $html = '<span style="color: blue;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $html = '<span style="color: orange;">' . $value . '</span>';
                break;

            default:
                break;
        }

        return $html;
    }

    // ---------------------------------------

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            array(
                array('attribute'=>'sku','like'=>'%'.$value.'%'),
                array('attribute'=>'online_sku','like'=>'%'.$value.'%'),
                array('attribute'=>'name', 'like'=>'%'.$value.'%'),
                array('attribute'=>'online_title','like'=>'%'.$value.'%'),
            )
        );
    }

    protected function callbackFilterPrice($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $condition = '';

        if (isset($value['from']) && $value['from'] != '') {
            $condition = 'min_online_price >= \''.(float)$value['from'].'\'';
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $condition .= ' AND ';
            }

            $condition .= 'min_online_price <= \''.(float)$value['to'].'\'';
        }

        $condition = '(' . $condition . ') OR (';

        if (isset($value['from']) && $value['from'] != '') {
            $condition .= 'max_online_price >= \''.(float)$value['from'].'\'';
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $condition .= ' AND ';
            }

            $condition .= 'max_online_price <= \''.(float)$value['to'].'\'';
        }

        $condition .= ')';

        $collection->getSelect()->having($condition);
    }

    //########################################

    protected function _toHtml()
    {
        $massActionFormId = $this->getId().'_massaction-form';
        $style = "<style> #{$massActionFormId} { display: none; } </style>";
        $javascriptsMain = <<<HTML
        <script type="text/javascript">

            PickupStoreProductGridObj = new ListingProductGrid();
            PickupStoreProductGridObj.setGridId('{$this->getJsObjectName()}');

            EbayListingPickupStoreStepProductsGridObj = new EbayListingPickupStoreStepProductsGrid();
            EbayListingPickupStoreStepProductsGridObj.gridId = '{$this->getId()}';

            var init = function () {
                {$this->getJsObjectName()}.doFilter = PickupStoreProductGridObj.setFilter;
                {$this->getJsObjectName()}.resetFilter = PickupStoreProductGridObj.resetFilter;
            };

            {$this->isAjax} ? init()
                            : Event.observe(window, 'load', init);

        </script>
HTML;

        return parent::_toHtml() . $style . $javascriptsMain;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/adminhtml_ebay_listing_pickupStore/productsStepGrid',
            array('id'=>$this->_listing->getId())
        );
    }

    //########################################
}
