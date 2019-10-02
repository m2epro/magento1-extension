<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_PickupStore_Variation_Product_View_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_listingProductId;
    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct;

    protected $_pickupStoreId;

    //########################################

    /**
     * @param mixed $listingProductId
     */
    public function setListingProductId($listingProductId)
    {
        $this->_listingProductId = $listingProductId;
    }
    /**
     * @return mixed
     */
    public function getListingProductId()
    {
        return $this->_listingProductId;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        if (empty($this->_listingProduct)) {
            $this->_listingProduct = Mage::helper('M2ePro/Component_Ebay')
                                         ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->_listingProduct;
    }

    // ---------------------------------------

    /**
     * @param $pickupStoreId
     */
    public function setPickupStoreId($pickupStoreId)
    {
        $this->_pickupStoreId = $pickupStoreId;
    }

    /**
     * @return mixed
     */
    public function getPickupStoreId()
    {
        return $this->_pickupStoreId;
    }

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayPickupStoreVariationProductGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        // Get collection
        // ---------------------------------------
        /** @var Ess_M2ePro_Model_Resource_Ebay_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product_Variation');
        $collection->getSelect()->where('main_table.listing_product_id = ?', (int)$this->getListingProductId());
        $collection->getSelect()->group('main_table.id');
        // ---------------------------------------

        // ---------------------------------------
        $collection->getSelect()->join(
            array('mlpvo' => Mage::getResourceModel('M2ePro/Listing_Product_Variation_Option')->getMainTable()),
            '`mlpvo`.`listing_product_variation_id`=`main_table`.`id`'
        );

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array(
                'id'                 => 'main_table.id',
                'listing_product_id' => 'main_table.listing_product_id',
                'additional_data'    => 'main_table.additional_data',
                'add'                => 'second_table.add',
                'delete'             => 'second_table.delete',
                'online_price'       => 'second_table.online_price',
                'online_sku'         => 'second_table.online_sku',
                'available_qty'      => new Zend_Db_Expr('(second_table.online_qty - second_table.online_qty_sold)'),
                'online_qty_sold'    => 'second_table.online_qty_sold',
                'status'             => 'second_table.status',
                'attributes'       => 'GROUP_CONCAT(`mlpvo`.`attribute`, \'==\', `mlpvo`.`option` SEPARATOR \'||\')',
                'products_ids'     => 'GROUP_CONCAT(`mlpvo`.`attribute`, \'==\', `mlpvo`.`product_id` SEPARATOR \'||\')'
            )
        );

        $resultCollection = new Varien_Data_Collection_Db(Mage::getResourceModel('core/config')->getReadConnection());
        $resultCollection->getSelect()->reset()->from(
            array('main_table' => $collection->getSelect()),
            array(
                'id',
                'listing_product_id',
                'additional_data',
                'add',
                'delete',
                'online_price',
                'available_qty',
                'online_sku',
                'online_qty_sold',
                'status',
                'attributes',
                'products_ids',
                'account_pickup_store_id',
                'store_name',
                'store_online_qty',
                'state_id',
                'is_added',
                'is_deleted',
                'is_in_processing'
            )
        );
        $collection->getSelect()->join(
            array('elpp' => Mage::getResourceModel('M2ePro/Ebay_Listing_Product_PickupStore')->getMainTable()),
            'elpp.listing_product_id=main_table.listing_product_id',
            array('account_pickup_store_id')
        );
        $collection->getSelect()->joinLeft(
            array('eap' => Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore')->getMainTable()),
            'eap.id=elpp.account_pickup_store_id',
            array('store_name' => 'name')
        );
        $collection->getSelect()->joinLeft(
            array('eaps' => Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore_State')->getMainTable()),
            'eaps.sku=online_sku AND eaps.account_pickup_store_id=eap.id',
            array(
                'store_online_qty'   => 'online_qty',
                'state_id'           => 'id',
                'is_in_processing'   => 'is_in_processing',
                'is_added'           => 'is_added',
                'is_deleted'         => 'is_deleted',
            )
        );
        $collection->getSelect()->where('eap.id = ?', $this->getPickupStoreId());
        // ---------------------------------------

        // Set collection to grid
        $this->setCollection($resultCollection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'variation', array(
            'header' => Mage::helper('M2ePro')->__('Magento Variation'),
            'align' => 'left',
            'sortable' => false,
            'index' => 'attributes',
            'filter_index' => 'attributes',
            'frame_callback' => array($this, 'callbackColumnVariations'),
            'filter' => 'M2ePro/adminhtml_grid_column_filter_attributesOptions',
            'options' => $this->getVariationsAttributes(),
            'filter_condition_callback' => array($this, 'callbackFilterVariations')
            )
        );

        $this->addColumn(
            'online_sku', array(
            'header'    => Mage::helper('M2ePro')->__('SKU'),
            'align'     => 'left',
            'width'     => '300px',
            'index'     => 'online_sku',
            'filter_index' => 'online_sku',
            'frame_callback' => array($this, 'callbackColumnOnlineSku')
            )
        );

        $this->addColumn(
            'store_online_qty', array(
            'header'    => Mage::helper('M2ePro')->__('Available QTY'),
            'align'     => 'right',
            'type'      => 'number',
            'width'     => '100px',
            'index'     => 'store_online_qty',
            'frame_callback' => array($this, 'callbackColumnStoreOnlineQty')
            )
        );

        $this->addColumn(
            'availability', array(
            'header'    => Mage::helper('M2ePro')->__('Availability'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'options',
            'sortable'  => false,
            'options'   => array(
                1 => Mage::helper('M2ePro')->__('Yes'),
                0 => Mage::helper('M2ePro')->__('No')
            ),
            'index'     => 'pickup_store_product_qty',
            'frame_callback' => array($this, 'callbackColumnOnlineAvailability'),
            'filter_condition_callback' => array($this, 'callbackFilterOnlineAvailability')

            )
        );

        $this->addColumn(
            'store_log', array(
            'header'    => Mage::helper('M2ePro')->__('Logs'),
            'align'     => 'left',
            'type'      => 'text',
            'width'     => '100px',
            'index'     => 'store_log',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnLog'),
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnVariations($value, $row, $column, $isExport)
    {
        $attributes = $this->parseGroupedData($row->getData('attributes'));
        $productsIds = $this->parseGroupedData($row->getData('products_ids'));
        $uniqueProductsIds = count(array_unique($productsIds)) > 1;

        $html = '<div class="m2ePro-variation-attributes" style="margin-left: 5px;">';
        if (!$uniqueProductsIds) {
            $url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => reset($productsIds)));
            $html .= '<a href="' . $url . '" target="_blank">';
        }

        foreach ($attributes as $attribute => $option) {
            $optionHtml = '<b>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                '</b>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option);

            if ($uniqueProductsIds) {
                $url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $productsIds[$attribute]));
                $html .= '<a href="' . $url . '" target="_blank">' . $optionHtml . '</a><br/>';
            } else {
                $html .= $optionHtml . '<br/>';
            }
        }

        if (!$uniqueProductsIds) {
            $html .= '</a>';
        }

        $html .= '</div>';

        return $html;
    }

    public function callbackColumnOnlineSku($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED &&
            ($value === null || $value === '')) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if ($value === null || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return $value;
    }

    public function callbackColumnStoreOnlineQty($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED &&
            ($value === null || $value === '')) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if ($value === null || $value === '' || $row->getData('is_added')) {
            $value =  Mage::helper('M2ePro')->__('Adding to Store');
        }

        $inProgressHtml = '';
        if ((bool)$row->getData('is_in_processing')) {
            $inProgressLabel = Mage::helper('M2ePro')->__('In Progress');
            $inProgressHtml = '&nbsp;<div style="color: #605fff">'.$inProgressLabel.'</div>';
        }

        return $value . $inProgressHtml;
    }

    public function callbackColumnOnlineAvailability($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        $qty = $row->getData('store_online_qty');
        if ($qty === null || $row->getData('is_added')) {
            return Mage::helper('M2ePro')->__('Adding to Store');
        }

        if ($qty <= 0) {
            return '<span style="color: red;">'.Mage::helper('M2ePro')->__('Out Of Stock').'</span>';
        }

        return '<span>'.Mage::helper('M2ePro')->__('In Stock').'</span>';
    }

    public function callbackColumnLog($value, $row, $column, $isExport)
    {
        $logIcon = $this->getViewLogIconHtml($row->getData('state_id'), $row->getData('id'));

        if (!empty($logIcon)) {
            $logIcon .= '<input type="hidden"
                                id="product_row_order_'.$row->getData('id').'"
                                value="'.$row->getData('id').'"
                                listing-product-pickup-store-state="'.$row->getData('state_id').'"/>';
        }

        return $logIcon;
    }

    public function getViewLogIconHtml($stateId, $columnId)
    {
        $stateId = (int)$stateId;

        // Get last messages
        // ---------------------------------------
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $dbSelect = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore_Log')->getMainTable(),
                array('action_id','action','type','description','create_date')
            )
            ->where('`account_pickup_store_state_id` = ?', $stateId)
            ->where('`action_id` IS NOT NULL')
            ->order(array('id DESC'))
            ->limit(30);

        $logRows = $connRead->fetchAll($dbSelect);
        // ---------------------------------------

        // Get grouped messages by action_id
        // ---------------------------------------
        $actionsRows = array();
        $tempActionRows = array();
        $lastActionId = false;

        foreach ($logRows as $row) {
            $row['description'] = Mage::helper('M2ePro/View')->getModifiedLogMessage($row['description']);

            if ($row['action_id'] !== $lastActionId) {
                if (!empty($tempActionRows)) {
                    $actionsRows[] = array(
                        'type' => $this->getMainTypeForActionId($tempActionRows),
                        'date' => $this->getMainDateForActionId($tempActionRows),
                        'action' => $this->getActionForAction($tempActionRows[0]),
                        'initiator' => Mage::helper('M2ePro')->__('Automatic'),
                        'items' => $tempActionRows
                    );
                    $tempActionRows = array();
                }

                $lastActionId = $row['action_id'];
            }

            $tempActionRows[] = $row;
        }

        if (!empty($tempActionRows)) {
            $actionsRows[] = array(
                'type' => $this->getMainTypeForActionId($tempActionRows),
                'date' => $this->getMainDateForActionId($tempActionRows),
                'action' => $this->getActionForAction($tempActionRows[0]),
                'initiator' => Mage::helper('M2ePro')->__('Automatic'),
                'items' => $tempActionRows
            );
        }

        if (empty($actionsRows)) {
            return '';
        }

        foreach ($actionsRows as &$actionsRow) {
            usort(
                $actionsRow['items'], function($a, $b)
                {
                $sortOrder = array(
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 1,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 2,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 3,
                );

                return $sortOrder[$a["type"]] > $sortOrder[$b["type"]];
                }
            );
        }

        $tips = array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 'Last Action was completed successfully.',
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 'Last Action was completed with error(s).',
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 'Last Action was completed with warning(s).'
        );

        $icons = array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 'normal',
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 'error',
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 'warning'
        );

        $summary = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_log_grid_summary', '', array(
            'entity_id' => (int)$columnId,
            'rows' => $actionsRows,
            'tips' => $tips,
            'icons' => $icons,
            'view_help_handler' => 'EbayListingPickupStoreGridHandlerObj.viewItemHelp',
            'hide_help_handler' => 'EbayListingPickupStoreGridHandlerObj.hideItemHelp',
            )
        );

        $pickupStoreState = Mage::getModel('M2ePro/Ebay_Account_PickupStore_State')
            ->load($stateId);
        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
            'Log For Sku' => Mage::helper('M2ePro')->__('Log For Sku (%s%)', $pickupStoreState->getSku())
            )
        );

        $html = "<script>M2ePro.translator.add({$translations});</script>";

        return $html . $summary->toHtml();
    }

    public function getActionForAction($actionRows)
    {
        $string = '';

        switch ($actionRows['action']) {
            case Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_UNKNOWN:
                $string = Mage::helper('M2ePro')->__('Unknown');
                break;
            case Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_ADD_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Add');
                break;
            case Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_UPDATE_QTY:
                $string = Mage::helper('M2ePro')->__('Update');
                break;
            case Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_DELETE_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Delete');
                break;
        }

        return $string;
    }

    public function getMainTypeForActionId($actionRows)
    {
        $type = Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS;

        foreach ($actionRows as $row) {
            if ($row['type'] == Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR) {
                $type = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
                break;
            }

            if ($row['type'] == Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING) {
                $type = Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;
            }
        }

        return $type;
    }

    public function getMainDateForActionId($actionRows)
    {
        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        return Mage::app()->getLocale()->date(strtotime($actionRows[0]['create_date']))->toString($format);
    }

    //########################################

    public function callbackFilterVariations($collection, $column)
    {
        $values = $column->getFilter()->getValue();

        if ($values == null && !is_array($values)) {
            return;
        }

        foreach ($values as $value) {
            if (is_array($value) && !empty($value['value'])) {
                $collection->addFieldToFilter(
                    'attributes',
                    array('regexp'=> $value['attr'].'=='.$value['value'])
                );
            }
        }
    }

    protected function callbackFilterOnlineAvailability($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $query = 'store_online_qty' . ((int)$value ? '>' : '<=' ) . ' 0';
        $collection->getSelect()->where($query);
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/adminhtml_ebay_listing_pickupStore_variation_product_show/variationsGridAjax',
            array('_current' => true)
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function _toHtml()
    {
        $additionalCss = <<<HTML
        <style>
            body {
                background: none;
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

            td.help_line .hl_close {
                margin-top: -6px;
            }

            td.help_line .hl_header {
                padding: 0 0 10px !important;
            }

        </style>
HTML;

        $urls = Mage::helper('M2ePro')->jsonEncode(
            array('*/logGrid' => $this->getUrl('M2ePro/adminhtml_ebay_listing_pickupStore/logGrid/'))
        );
        $javascriptMain = <<<HTML
        <script type="text/javascript">
            Event.observe(window, 'load', function() {

                if (typeof M2ePro == 'undefined') {
                    M2ePro = {};
                    M2ePro.url = {};
                    M2ePro.formData = {};
                    M2ePro.customData = {};
                    M2ePro.text = {};
                }

                M2ePro.url.add({$urls});
                CommonHandler.prototype.scroll_page_to_top = function() { return; };

                EbayListingPickupStoreGridHandlerObj = new EbayListingPickupStoreGridHandler();
                EbayListingPickupStoreGridHandlerObj.gridId = '{$this->getId()}';

                VariationsGridHandlerObj = new EbayListingVariationProductManageVariationsGridHandler(
                    'ebayPickupStoreVariationProductGrid'
                );

                setTimeout(function() {
                    VariationsGridHandlerObj.afterInitPage();
                }, 350);
            });

            if (typeof VariationsGridHandlerObj != 'undefined') {
                VariationsGridHandlerObj.afterInitPage();
            }

        </script>
HTML;

        return  $additionalCss . parent::_toHtml() .$javascriptMain;
    }

    //########################################

    protected function getVariationsAttributes()
    {
        if ($this->variationAttributes === null) {
            $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
            $tableVariation = Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_listing_product_variation');
            $tableOption = Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_listing_product_variation_option');

            $select = $connRead->select();
            $select->from(array('mlpv' => $tableVariation), array())
                ->join(
                    array('mlpvo' => $tableOption),
                    'mlpvo.listing_product_variation_id = mlpv.id',
                    array('attribute')
                )
                ->where('listing_product_id = ?', (int)$this->getListingProductId());

            $attributes = Mage::getResourceModel('core/config')->getReadConnection()->fetchCol($select);

            $this->variationAttributes = array_unique($attributes);
        }

        return $this->variationAttributes;
    }

    protected function parseGroupedData($data)
    {
        $result = array();

        $variationData = explode('||', $data);
        foreach ($variationData as $variationAttribute) {
            $value = explode('==', $variationAttribute);
            $result[$value[0]] = $value[1];
        }

        return $result;
    }

    //########################################
}
