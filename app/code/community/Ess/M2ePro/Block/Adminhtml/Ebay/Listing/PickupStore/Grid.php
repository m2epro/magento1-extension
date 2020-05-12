<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_PickupStore_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    /** @var Ess_M2ePro_Model_Ebay_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->_listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $this->setId('ebayListingPickupStoreGrid'.$this->_listing->getId());

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

    protected function _prepareCollection()
    {
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getData();

        // ---------------------------------------
        // Get collection
        // ---------------------------------------
        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );
        $collection->setListingProductModeOn()
                   ->setListing($listingData['id'])
                   ->setStoreId($listingData['store_id']);

        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');
        $collection->getSelect()->columns('entity_id AS category_product_id');
        // ---------------------------------------

        // Join listing product tables
        // ---------------------------------------
        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'lp_id' => 'id',
                'ebay_status' => 'status',
                'component_mode' => 'component_mode',
                'additional_data' => 'additional_data'
            ),
            '{{table}}.listing_id='.(int)$listingData['id']
        );
        $collection->joinTable(
            array('elp' => 'M2ePro/Ebay_Listing_Product'),
            'listing_product_id=lp_id',
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
                'template_category_id'  => 'template_category_id',
            )
        );
        $collection->joinTable(
            array('ei' => 'M2ePro/Ebay_Item'),
            'id=ebay_item_id',
            array('item_id' => 'item_id',),
            null,
            'left'
        );

        $collection->getSelect()->join(
            array('elpps' => Mage::getResourceModel('M2ePro/Ebay_Listing_Product_PickupStore')->getMainTable()),
            'elp.listing_product_id=elpps.listing_product_id',
            array(
                'id' => 'id',
                'account_pickup_store_id' => 'account_pickup_store_id'
            )
        );

        $collection->getSelect()->joinLeft(
            array('meaps' => Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore')->getMainTable()),
            'elpps.account_pickup_store_id = meaps.id',
            array(
                'pickup_store_id' => 'id',
                'store_name'  => 'name',
                'location_id' => 'location_id',
                'phone'       => 'phone',
                'postal_code' => 'postal_code',
                'country'     => 'country',
                'region'      => 'region',
                'city'        => 'city',
                'address_1'   => 'address_1',
                'address_2'   => 'address_2')
        );

        $collection->getSelect()->joinLeft(
            array('meapss' => Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore_State')->getMainTable()),
            'meapss.account_pickup_store_id = meaps.id AND meapss.sku = elp.online_sku',
            array(
                'pickup_store_product_qty' => 'IF(
                    (`meapss`.`online_qty` IS NULL),
                    `t`.`variations_qty`,
                    `meapss`.`online_qty`
                )',
                'state_id' => 'id',
                'is_in_processing' => 'is_in_processing',
                'is_added' => 'is_added',
                'is_deleted' => 'is_deleted'
            )
        );

        $collection->getSelect()->joinLeft(
            new Zend_Db_Expr(
                '(
                SELECT
                    `mlpv`.`listing_product_id`,
                    `meapss`.`account_pickup_store_id`,
                    SUM(`meapss`.`online_qty`) as `variations_qty`,
                    SUM(`meapss`.`is_in_processing`) as `variations_processing`,
                    SUM(`meapss`.`is_added`) as `variations_added`,
                    COUNT(`meapss`.`is_in_processing`) as `count_variations_in_state`
                FROM `'. Mage::getResourceModel('M2ePro/Listing_Product_Variation')->getMainTable() .'` AS `mlpv`
                INNER JOIN `' .
                Mage::getResourceModel('M2ePro/Ebay_Listing_Product_Variation')->getMainTable().'` AS `melpv`
                    ON (`mlpv`.`id` = `melpv`.`listing_product_variation_id`)
                INNER JOIN `' .
                Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore_State')->getMainTable().'` AS meapss
                    ON (meapss.sku = melpv.online_sku)
                WHERE `melpv`.`status` != ' . Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED . '
                GROUP BY `meapss`.`account_pickup_store_id`, `mlpv`.`listing_product_id`
            )'
            ),
            'elp.listing_product_id=t.listing_product_id AND t.account_pickup_store_id = meaps.id',
            array(
                'variations_qty' => 'variations_qty',
                'variations_processing' => 'variations_processing',
                'variations_added' => 'variations_added',
                'count_variations_in_state' => 'count_variations_in_state',
            )
        );

        $collection->getSelect()->where(
            'lp.listing_id = ?', (int)$listingData['id']
        );
        // ---------------------------------------

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ?
                $column->getFilterIndex() : $column->getIndex();

            if ($columnIndex == 'pickup_store_product_qty') {
                $collection->getSelect()->order('pickup_store_product_qty ' . strtoupper($column->getDir()));
            } else {
                $collection->setOrder($columnIndex, strtoupper($column->getDir()));
            }
        }

        return $this;
    }

    //########################################

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id', array(
                'header'         => Mage::helper('M2ePro')->__('Product ID'),
                'align'          => 'right',
                'width'          => '100px',
                'type'           => 'number',
                'index'          => 'entity_id',
                'frame_callback' => array($this, 'callbackColumnListingProductId'),
            )
        );

        $this->addColumn(
            'name', array(
                'header'                    => Mage::helper('M2ePro')->__('Product Title / Product SKU'),
                'align'                     => 'left',
                'type'                      => 'text',
                'index'                     => 'online_title',
                'width'                     => '550px',
                'frame_callback'            => array($this, 'callbackColumnTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $this->addColumn(
            'account_pickup_store_id', array(
                'header'                    => Mage::helper('M2ePro')->__('Store Details'),
                'align'                     => 'left',
                'type'                      => 'text',
                'index'                     => 'id',
                'width'                     => '500px',
                'frame_callback'            => array($this, 'callbackColumnPickupStore'),
                'filter_condition_callback' => array($this, 'callbackFilterPickupStore')
            )
        );

        $this->addColumn(
            'ebay_item_id', array(
                'header'         => Mage::helper('M2ePro')->__('Item ID'),
                'align'          => 'left',
                'width'          => '100px',
                'type'           => 'text',
                'index'          => 'item_id',
                'frame_callback' => array($this, 'callbackColumnEbayItemId')
            )
        );

        $this->addColumn(
            'pickup_store_product_qty', array(
                'header'                    => Mage::helper('M2ePro')->__('Available QTY'),
                'align'                     => 'left',
                'width'                     => '110px',
                'type'                      => 'number',
                'sortable'                  => true,
                'index'                     => 'pickup_store_product_qty',
                'frame_callback'            => array($this, 'callbackColumnOnlineQty'),
                'filter_condition_callback' => array($this, 'callbackFilterOnlineQty')

            )
        );

        $this->addColumn(
            'availability', array(
                'header'                    => Mage::helper('M2ePro')->__('Availability'),
                'align'                     => 'right',
                'width'                     => '110px',
                'type'                      => 'options',
                'sortable'                  => false,
                'options'                   => array(
                    1 => Mage::helper('M2ePro')->__('Yes'),
                    0 => Mage::helper('M2ePro')->__('No')
                ),
                'index'                     => 'pickup_store_product_qty',
                'frame_callback'            => array($this, 'callbackColumnOnlineAvailability'),
                'filter_condition_callback' => array($this, 'callbackFilterOnlineAvailability')

            )
        );

        $this->addColumn(
            'delete_action', array(
                'header'         => Mage::helper('M2ePro')->__('Logs & Events'),
                'align'          => 'left',
                'type'           => 'action',
                'index'          => 'delete_action',
                'width'          => '100px',
                'filter'         => false,
                'sortable'       => false,
                'frame_callback' => array($this, 'callbackColumnLog'),
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

        $this->getMassactionBlock()->addItem(
            'unassign', array(
            'label'    => Mage::helper('M2ePro')->__('Unassign'),
            'url'      => $this->getUrl(
                'M2ePro/adminhtml_ebay_listing_pickupStore/unassign/', array(
                'listing_id' => $this->_listing->getId()
                )
            ),
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        return parent::_prepareMassaction();
    }

    protected function _prepareMassactionColumn()
    {
        $columnId = 'massaction';
        $massactionColumn = $this->getLayout()->createBlock('adminhtml/widget_grid_column')
                ->setData(
                    array(
                        'index'                     => $this->getMassactionIdField(),
                        'filter_index'              => $this->getMassactionIdFilter(),
                        'type'                      => 'massaction',
                        'name'                      => $this->getMassactionBlock()->getFormFieldName(),
                        'align'                     => 'center',
                        'is_system'                 => true,
                        'filter_condition_callback' => array($this, 'callbackFilterCheckboxes')
                    )
                );

        if ($this->getNoFilterMassactionColumn()) {
            $massactionColumn->setData('filter', false);
        }

        $massactionColumn->setSelected($this->getMassactionBlock()->getSelected())
            ->setGrid($this)
            ->setId($columnId);

        $oldColumns = $this->_columns;
        $this->_columns = array();
        $this->_columns[$columnId] = $massactionColumn;
        $this->_columns = array_merge($this->_columns, $oldColumns);
        return $this;
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
            $sku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('catalog_product_id'))
                        ->getSku();
        }

        $onlineSku = $row->getData('online_sku');
        !empty($onlineSku) && $sku = $onlineSku;

        $valueHtml .= '<br/><strong>' . Mage::helper('M2ePro')->__('SKU') . ':</strong>&nbsp;'
                      . Mage::helper('M2ePro')->escapeHtml($sku);

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Ebay')
            ->getCachedObject('Listing_Product', $row->getData('listing_product_id'));

        if (!$listingProduct->getChildObject()->isVariationsReady()) {
            return '<div style="padding: 2px 4px;">'.$valueHtml.'</div>';
        }

        $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));
        $productAttributes = array_keys($additionalData['variations_sets']);

        $valueHtml .= '<div style="font-size: 11px; font-weight: bold; color: grey; margin: 7px 0 10px 7px">';
        $valueHtml .= implode(', ', $productAttributes);
        $valueHtml .= '</div>';

        $linkContent = Mage::helper('M2ePro')->__('Show Variations');
        $vpmt = Mage::helper('M2ePro')->__('Variations of &quot;%s&quot; ', $title);
        $vpmt = addslashes($vpmt);

        $itemId = $this->getData('item_id');

        if (!empty($itemId)) {
            $vpmt .= '('. $itemId .')';
        }

        $linkTitle = Mage::helper('M2ePro')->__('Open Manage Variations Tool');
        $listingProductId = (int)$row->getData('listing_product_id');
        $pickupStoreId = $row->getData('pickup_store_id');

        $valueHtml .= <<<HTML
<div style="float: left; margin: 0 0 0 7px">
<a href="javascript:"
onclick="EbayListingPickupStoreGridHandlerObj.openVariationPopUp({$listingProductId}, '{$vpmt}', '{$pickupStoreId}')"
title="{$linkTitle}">{$linkContent}</a>&nbsp;
</div>
HTML;

        return '<div style="padding: 0 4px;">'.$valueHtml.'</div>';
    }

    public function callbackColumnEbayItemId($value, $row, $column, $isExport)
    {
        if ($row->getData('ebay_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if ($value === null || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getData();

        $url = $this->getUrl(
            '*/adminhtml_ebay_listing/gotoEbay/', array(
                'item_id'        => $value,
                'account_id'     => $listingData['account_id'],
                'marketplace_id' => $listingData['marketplace_id']
            )
        );
        return '<a href="' . $url . '" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnOnlineQty($value, $row, $column, $isExport)
    {
        if ($row->getData('ebay_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        $sku = $row->getData('online_sku');
        if (empty($sku)) {
            return Mage::helper('M2ePro')->__('Adding to Store');
        }

        $qty = $row->getData('pickup_store_product_qty');
        if ($qty === null || $row->getData('is_added')) {
            $qty = Mage::helper('M2ePro')->__('Adding to Store');
        }

        $variationsAdded = $row->getData('variations_added');
        $countVariationsInState = $row->getData('count_variations_in_state');

        if ($countVariationsInState !== null
            && $variationsAdded !== null
            && $countVariationsInState == $variationsAdded
        ) {
            $qty = Mage::helper('M2ePro')->__('Adding to Store');
        }

        $inProgressHtml = '';
        if ((bool)$row->getData('is_in_processing') || (bool)$row->getData('variations_processing')) {
            $inProgressLabel = Mage::helper('M2ePro')->__('In Progress');
            $inProgressHtml = '&nbsp;<div style="color: #605fff">'.$inProgressLabel.'</div>';
        }

        return $qty . $inProgressHtml;
    }

    public function callbackColumnOnlineAvailability($value, $row, $column, $isExport)
    {
        if ($row->getData('ebay_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        $qty = $row->getData('pickup_store_product_qty');
        $variationsAdded = $row->getData('variations_added');
        $countVariationsInState = $row->getData('count_variations_in_state');

        if ($qty === null || $row->getData('is_added') || ($countVariationsInState !== null &&
            $variationsAdded !== null && $countVariationsInState == $variationsAdded)) {
            return Mage::helper('M2ePro')->__('Adding to Store');
        }

        if ($qty <= 0) {
            return '<span style="color: red;">'.Mage::helper('M2ePro')->__('Out Of Stock').'</span>';
        }

        return '<span>'.Mage::helper('M2ePro')->__('In Stock').'</span>';
    }

    public function callbackColumnPickupStore($value, $row, $column, $isExport)
    {
        $name = $row->getData('store_name');
        $locationId = $row->getData('location_id');
        $countryCode = $row->getData('country');

        $country = $countryCode;
        $countries = Mage::helper('M2ePro/Magento')->getCountries();

        foreach ($countries as $country) {
            if ($country['country_id'] == $countryCode) {
                $country = $country['name'];
                break;
            }
        }

        $region = $row->getData('region');
        $city = $row->getData('city');
        $addressOne = $row->getData('address_1');
        $addressTwo = $row->getData('address_2');

        $helper = Mage::helper('M2ePro');

        $addressHtml = "{$country}, {$region}, {$city} <br/> {$addressOne}";
        if (!empty($addressTwo)) {
            $addressHtml .= ',' . $addressTwo;
        }

        $addressHtml .= ', ' .$row->getData('postal_code');

        return <<<HTML
        <style>
            .column-pickup-store {
                padding: 2px 4px;
            }

            .column-pickup-store li:nth-child(2) {
                margin-bottom: 16px;
            }
        </style>
        <div>
            <ul class="column-pickup-store">
                <li><span>{$name}</span></li>
                <li><strong>{$helper->__('Location ID')}:</strong>&nbsp;<span>{$locationId}</span></li>
                <li>
                    <strong>{$helper->__('Address')}:</strong><br/>
                    <div>
                        {$addressHtml}
                    </div>
                </li>
            </ul>
        </div>
HTML
;
    }

    public function callbackColumnLog($value, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Ebay')
            ->getCachedObject('Listing_Product', $row->getData('listing_product_id'));

        if ($listingProduct->getChildObject()->isVariationsReady()) {
            return '';
        }

        $logIcon = $this->getViewLogIconHtml($row->getData('state_id'), $row->getData('id'));

        if (!empty($logIcon)) {
            $logIcon .= '<input type="hidden"
                                id="product_row_order_'.$row->getData('id').'"
                                value="'.$row->getData('id').'"
                                listing-product-pickup-store-state="'.$row->getData('state_id').'"/>';
        }

        return $logIcon;
    }

    // ---------------------------------------

    protected function callbackFilterCheckboxes($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $selected = $column->getData('selected');

        if ($value == null || $value == 0 && empty($selected)) {
            return;
        }

        if ($value == 1 && empty($selected)) {
            $selected = array(0);
        }

        $query = 'elpps.id ' . ((int)$value ? 'IN' : 'NOT IN' ) . '('.implode(',', $selected).')';
        $collection->getSelect()->where($query);
    }

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
                array('attribute'=>'online_title','like'=>'%'.$value.'%')
            )
        );
    }

    protected function callbackFilterOnlineQty($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $from = '';
        if (isset($value['from'])) {
            $from = '(meapss.online_qty >= ' . (int)$value['from']
                    . ' OR t.variations_qty >= ' . (int)$value['from'] .')';
        }

        $to = '';
        if (isset($value['to'])) {
            $to = '(meapss.online_qty <= ' . (int)$value['to']
                  . ' OR t.variations_qty <= ' . (int)$value['to'] .')';
        }

        $collection->getSelect()->where(
            $from . (!empty($from) && !empty($to) ? ' AND ' : '') . $to
        );
    }

    protected function callbackFilterOnlineAvailability($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            'meapss.online_qty ' . ((int)$value ? '>' : '<=' ) . ' 0' .
            ' OR t.variations_qty ' . ((int)$value ? '>' : '<=' ) . ' 0'
        );
    }

    protected function callbackFilterPickupStore($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $countryCode = '';
        $countries = Mage::helper('M2ePro/Magento')->getCountries();

        foreach ($countries as $country) {
            $pos = strpos(strtolower($country['name']), strtolower($value));
            if ($pos !== false) {
                $countryCode = $country['country_id'];
                break;
            }
        }

        $countryCode = !empty($countryCode) ? $countryCode : $value;
        $collection->getSelect()->where(
            "meaps.name LIKE '%{$value}%'
            OR meaps.location_id LIKE '%{$value}%'
            OR meaps.country LIKE '%{$countryCode}%'
            OR meaps.region LIKE '%{$value}%'
            OR meaps.city LIKE '%{$value}%'
            OR meaps.address_1 LIKE '%{$value}%'
            OR meaps.address_2 LIKE '%{$value}%'
            OR meaps.postal_code LIKE '%{$value}%'"
        );
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/adminhtml_ebay_listing_pickupStore/pickupStoreGrid', array(
                '_current' => true
            )
        );
    }

    //########################################

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
                array('id', 'action_id','action','type','description','create_date')
            )
            ->where('`account_pickup_store_state_id` = ?', $stateId)
            ->where('`action_id` IS NOT NULL')
            ->where('`action` IN (?)', $this->getAvailableActions())
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

    protected function getAvailableActions()
    {
        return array(
            Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_UNKNOWN,
            Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_ADD_PRODUCT,
            Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_UPDATE_QTY,
            Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_DELETE_PRODUCT,
        );
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

    protected function _toHtml()
    {
        $allIds = array();
        foreach ($this->getCollection()->getItems() as $item) {
            $allIds[] = $item['id'];
        }

        $allIdsStr  = implode(',', $allIds);

        $urls = Mage::helper('M2ePro')->jsonEncode(
            array(
            '*/assign' => $this->getUrl('M2ePro/adminhtml_ebay_listing_pickupStore/assign/'),
            '*/unassign' => $this->getUrl('M2ePro/adminhtml_ebay_listing_pickupStore/unassign/'),
            '*/pickupStoreGrid' => $this->getUrl(
                'M2ePro/adminhtml_ebay_listing_pickupStore/pickupStoreGrid/', array(
                'id' => $this->_listing->getId()
                )
            ),
            'variationProduct' => $this->getUrl(
                'M2ePro/adminhtml_ebay_listing_pickupStore_variation_product_show/variation/'
            ),
            '*/productsStep' => $this->getUrl('M2ePro/adminhtml_ebay_listing_pickupStore/productsStep/'),
            '*/storesStep' => $this->getUrl('M2ePro/adminhtml_ebay_listing_pickupStore/storesStep/'),
            '*/logGrid' => $this->getUrl('M2ePro/adminhtml_ebay_listing_pickupStore/logGrid/'),
            )
        );
        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
            'Assign Products to Stores' => Mage::helper('M2ePro')->__('Assign Products to Stores'),
            'Log For Sku' => Mage::helper('M2ePro')->__('Log For Sku')
            )
        );

        $css = "<style>
                    #{$this->getId()}_table .massaction-checkbox{
                        display: block;
                        margin: 2px auto 2px;
                    }
                </style>";

        $javascriptsMain = <<<HTML
        <script type="text/javascript">

            M2ePro.url.add({$urls});
            M2ePro.translator.add({$translations});

            EbayListingPickupStoreGridHandlerObj = new EbayListingPickupStoreGridHandler();
            EbayListingPickupStoreGridHandlerObj.gridId = '{$this->getId()}';

            var init = function () {
                window['{$this->getId()}' + '_massactionJsObject'].setGridIds('{$allIdsStr}');
            };

            {$this->isAjax} ? init()
                            : Event.observe(window, 'load', init);

        </script>
HTML;

        return parent::_toHtml() . $css . $javascriptsMain;
    }

    //########################################
}
