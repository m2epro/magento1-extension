<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var $_itemsCollection Ess_M2ePro_Model_Resource_Order_Item_Collection */
    protected $_itemsCollection = null;

    /** @var $_notesCollection Ess_M2ePro_Model_Resource_Order_Note_Collection */
    protected $_notesCollection = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonOrderGrid');

        $this->setDefaultSort('purchase_create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
    }

    protected function _prepareCollection()
    {
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Order');

        $collection->getSelect()
            ->joinLeft(
                array('so' => Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('sales/order')),
                '(so.entity_id = `main_table`.magento_order_id)',
                array('magento_order_num' => 'increment_id')
            );

        // Add Filter By Account
        // ---------------------------------------
        if ($accountId = $this->getRequest()->getParam('amazonAccount')) {
            $collection->addFieldToFilter('main_table.account_id', $accountId);
        }

        // ---------------------------------------

        // Add Filter By Marketplace
        // ---------------------------------------
        if ($marketplaceId = $this->getRequest()->getParam('amazonMarketplace')) {
            $collection->addFieldToFilter('main_table.marketplace_id', $marketplaceId);
        }

        // ---------------------------------------

        // Add Not Created Magento Orders Filter
        // ---------------------------------------
        if ($this->getRequest()->getParam('not_created_only')) {
            $collection->addFieldToFilter('magento_order_id', array('null' => true));
        }

        // ---------------------------------------

        // Add Not sent Invoice or Credit Memo Filter
        // ---------------------------------------
        if ($this->getRequest()->getParam('invoice_or_creditmemo_not_sent')) {
            $collection->addFieldToFilter('is_invoice_sent', 0);
            $collection->addFieldToFilter('is_credit_memo_sent', 0);
        }

        // ---------------------------------------

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        $this->_itemsCollection = Mage::helper('M2ePro/Component_Amazon')
            ->getCollection('Order_Item')
            ->addFieldToFilter('order_id', array('in' => $this->getCollection()->getColumnValues('id')));

        // ---------------------------------------

        $this->_notesCollection = Mage::getModel('M2ePro/Order_Note')
            ->getCollection()
            ->addFieldToFilter('order_id', array('in' => $this->getCollection()->getColumnValues('id')));

        // ---------------------------------------

        return parent::_afterLoadCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'purchase_create_date',
            array(
                'header' => Mage::helper('M2ePro')->__('Sale Date'),
                'align'  => 'left',
                'type'   => 'datetime',
                'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                'index'  => 'purchase_create_date',
                'width'  => '170px'
            )
        );

        $this->addColumn(
            'shipping_date_to',
            array(
                'header' => Mage::helper('M2ePro')->__('Ship By Date'),
                'align'  => 'left',
                'type'   => 'datetime',
                'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                'index'  => 'shipping_date_to',
                'width'  => '170px'
            )
        );

        $this->addColumn(
            'delivery_date',
            array(
                'header' => Mage::helper('M2ePro')->__('Deliver By Date'),
                'align'  => 'left',
                'type'   => 'datetime',
                'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                'index'  => 'delivery_date_from',
                'width'  => '170px',
                'frame_callback' => array($this, 'callbackDeliveryDate'),
            )
        );

        $this->addColumn(
            'magento_order_num',
            array(
                'header'         => Mage::helper('M2ePro')->__('Magento Order #'),
                'align'          => 'left',
                'index'          => 'so.increment_id',
                'width'          => '110px',
                'frame_callback' => array($this, 'callbackColumnMagentoOrder')
            )
        );

        $this->addColumn(
            'amazon_order_id',
            array(
                'header'         => Mage::helper('M2ePro')->__('Amazon Order #'),
                'align'          => 'left',
                'width'          => '160px',
                'index'          => 'amazon_order_id',
                'frame_callback' => array($this, 'callbackColumnAmazonOrderId')
            )
        );

        $this->addColumn(
            'amazon_order_items',
            array(
                'header'                    => Mage::helper('M2ePro')->__('Items'),
                'align'                     => 'left',
                'index'                     => 'amazon_order_items',
                'sortable'                  => false,
                'width'                     => '*',
                'frame_callback'            => array($this, 'callbackColumnItems'),
                'filter_condition_callback' => array($this, 'callbackFilterItems')
            )
        );

        $this->addColumn(
            'buyer',
            array(
                'header'                    => Mage::helper('M2ePro')->__('Buyer'),
                'align'                     => 'left',
                'index'                     => 'buyer_name',
                'width'                     => '120px',
                'frame_callback'            => array($this, 'callbackColumnBuyer'),
                'filter_condition_callback' => array($this, 'callbackFilterBuyer')
            )
        );

        $this->addColumn(
            'paid_amount',
            array(
                'header'         => Mage::helper('M2ePro')->__('Total Paid'),
                'align'          => 'left',
                'width'          => '110px',
                'index'          => 'paid_amount',
                'type'           => 'number',
                'frame_callback' => array($this, 'callbackColumnTotal')
            )
        );

        $this->addColumn(
            'is_afn_channel',
            array(
                'header'         => Mage::helper('M2ePro')->__('Fulfillment'),
                'width'          => '100px',
                'index'          => 'is_afn_channel',
                'filter_index'   => 'second_table.is_afn_channel',
                'type'           => 'options',
                'sortable'       => false,
                'options'        => array(
                    0 => Mage::helper('M2ePro')->__('Merchant'),
                    1 => Mage::helper('M2ePro')->__('Amazon')
                ),
                'frame_callback' => array($this, 'callbackColumnAfnChannel')
            )
        );

        $helper = Mage::helper('M2ePro');

        $this->addColumn(
            'status',
            array(
                'header'         => Mage::helper('M2ePro')->__('Status'),
                'align'          => 'left',
                'width'          => '50px',
                'index'          => 'status',
                'filter_index'   => 'second_table.status',
                'type'           => 'options',
                'options'        => array(
                    Ess_M2ePro_Model_Amazon_Order::STATUS_PENDING                => $helper->__('Pending'),
                    Ess_M2ePro_Model_Amazon_Order::STATUS_PENDING_RESERVED       =>
                        $helper->__('Pending / QTY Reserved'),
                    Ess_M2ePro_Model_Amazon_Order::STATUS_UNSHIPPED              => $helper->__('Unshipped'),
                    Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED_PARTIALLY      => $helper->__('Partially Shipped'),
                    Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED                => $helper->__('Shipped'),
                    Ess_M2ePro_Model_Amazon_Order::STATUS_INVOICE_UNCONFIRMED    =>
                        $helper->__('Invoice Not Confirmed'),
                    Ess_M2ePro_Model_Amazon_Order::STATUS_UNFULFILLABLE          => $helper->__('Unfulfillable'),
                    Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED               => $helper->__('Canceled'),
                    Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELLATION_REQUESTED =>
                        $helper->__('Unshipped (Cancellation Requested)'),
                ),
                'frame_callback' => array($this, 'callbackColumnStatus'),
                'filter_condition_callback' => array($this, 'callbackFilterStatus')
            )
        );

        $back = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_amazon_order/index');

        $this->addColumn(
            'action',
            array(
                'header'    => Mage::helper('M2ePro')->__('Action'),
                'width'     => '80px',
                'type'      => 'action',
                'getter'    => 'getId',
                'renderer'  => 'M2ePro/adminhtml_grid_column_renderer_action',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('M2ePro')->__('View'),
                        'url'     => array('base' => '*/adminhtml_amazon_order/view'),
                        'field'   => 'id'
                    ),
                    array(
                        'caption' => Mage::helper('M2ePro')->__('Edit Shipping Address'),
                        'url'     => array(
                            'base' => '*/adminhtml_amazon_order/editShippingAddress/back/' . $back . '/'
                        ),
                        'field'   => 'id'
                    ),
                    array(
                        'caption' => Mage::helper('M2ePro')->__('Create Order'),
                        'url'     => array('base' => '*/adminhtml_amazon_order/createMagentoOrder'),
                        'field'   => 'id'
                    ),
                    array(
                        'caption'        => Mage::helper('M2ePro')->__('Mark As Shipped'),
                        'field'          => 'id',
                        'onclick_action' => 'AmazonOrderMerchantFulfillmentObj.markAsShippedAction'
                    ),
                    array(
                        'caption'        => Mage::helper('M2ePro')->__('Amazon\'s Shipping Services'),
                        'field'          => 'id',
                        'onclick_action' => 'AmazonOrderMerchantFulfillmentObj.getPopupAction'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem(
            'reservation_place',
            array(
                'label'   => Mage::helper('M2ePro')->__('Reserve QTY'),
                'url'     => $this->getUrl('*/adminhtml_order/reservationPlace'),
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        $this->getMassactionBlock()->addItem(
            'reservation_cancel',
            array(
                'label'   => Mage::helper('M2ePro')->__('Cancel QTY Reserve'),
                'url'     => $this->getUrl('*/adminhtml_order/reservationCancel'),
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        $this->getMassactionBlock()->addItem(
            'ship',
            array(
                'label'   => Mage::helper('M2ePro')->__('Mark Order(s) as Shipped'),
                'url'     => $this->getUrl('*/adminhtml_amazon_order/updateShippingStatus'),
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        $this->getMassactionBlock()->addItem(
            'resend_shipping',
            array(
                'label'   => Mage::helper('M2ePro')->__('Resend Shipping Information'),
                'url'     => $this->getUrl('*/adminhtml_order/resubmitShippingInfo'),
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        $this->getMassactionBlock()->addItem(
            'resend_invoice_creditmemo',
            array(
                'label'   => Mage::helper('M2ePro')->__('Resend Invoice / Credit Memo'),
                'url'     => $this->getUrl('*/adminhtml_amazon_order/resendInvoiceCreditmemo'),
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        $this->getMassactionBlock()->addItem(
            'create_order',
            array(
                'label'   => Mage::helper('M2ePro')->__('Create Magento Order'),
                'url'     => $this->getUrl('*/adminhtml_amazon_order/createMagentoOrder'),
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnAmazonOrderId($value, $row, $column, $isExport)
    {
        $helper = Mage::helper('M2ePro');
        $back = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_amazon_order/index');
        $itemUrl = $this->getUrl('*/adminhtml_amazon_order/view', array('id' => $row->getId(), 'back' => $back));

        $orderId = Mage::helper('M2ePro')->escapeHtml($row->getData('amazon_order_id'));
        $url = Mage::helper('M2ePro/Component_Amazon')->getOrderUrl($orderId, $row->getData('marketplace_id'));

        $returnString = <<<HTML
<a href="{$itemUrl}">{$orderId}</a>
HTML;

        $primeImageHtml = '';
        if ($row['is_prime']) {
            $primeImageHtml = '<div><img src="' . $this->getSkinUrl('M2ePro/images/prime.png') . '" /></div>';
        }

        $businessImageHtml = '';
        if ($row['is_business']) {
            $businessImageHtml = '<div><img width="100px" src="'
                . $this->getSkinUrl('M2ePro/images/amazon-business.png')
                . '" /></div>';
        }

        $returnString .= <<<HTML
<a title="{$helper->__('View on Amazon')}" target="_blank" href="{$url}">
<img style="margin-bottom: -3px; float: right"
 src="{$this->getSkinUrl('M2ePro/images/view_amazon.png')}" alt="{$helper->__('View on Amazon')}" /></a>
HTML;

        $returnString .= $primeImageHtml;
        $returnString .= $businessImageHtml;

        /** @var $notes Ess_M2ePro_Model_Order_Note[] */
        $notes = $this->_notesCollection->getItemsByColumnValue('order_id', $row->getData('id'));

        if ($notes) {
            $htmlNotesCount = $helper->__(
                'You have a custom note for the order. It can be reviewed on the order detail page.'
            );

            $returnString .= <<<HTML
<div class="note_icon" style="display: inline-block; margin-left: 2px; width:16px;">
    <img class="tool-tip-image"
         style="vertical-align: middle; cursor: inherit"
         src="{$this->getSkinUrl('M2ePro/images/fam_book_open.png')}">
    <span class="tool-tip-message tool-tip-message" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/fam_book_open.png')}" style="width: 18px; height: 18px">
        <div class="amazon-identifiers">
           {$htmlNotesCount}
        </div>
    </span>
</div>
HTML;
        }

        return $returnString;
    }

    public function callbackDeliveryDate($value, $row, $column, $isExport)
    {
        $deliveryDate = $row->getChildObject()->getData('delivery_date_from');
        if (empty($deliveryDate)) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return $this->formatDate(
            $deliveryDate,
            \Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM,
            true
        );
    }

    public function callbackColumnMagentoOrder($value, $row, $column, $isExport)
    {
        $magentoOrderId = $row['magento_order_id'];
        $magentoOrderNumber = Mage::helper('M2ePro')->escapeHtml($row['magento_order_num']);

        $returnString = Mage::helper('M2ePro')->__('N/A');

        if ($magentoOrderId !== null) {
            if ($row['magento_order_num']) {
                $orderUrl = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $magentoOrderId));
                $returnString = '<a href="' . $orderUrl . '" target="_blank">' . $magentoOrderNumber . '</a>';
            } else {
                $returnString = '<span style="color: red;">' . Mage::helper('M2ePro')->__('Deleted') . '</span>';
            }
        }

        /** @var Ess_M2ePro_Block_Adminhtml_Grid_Column_Renderer_ViewLogIcon_Order $viewLogIcon */
        $viewLogIcon = $this->getLayout()->createBlock('M2ePro/Adminhtml_Grid_Column_Renderer_ViewLogIcon_Order');
        $logIconHtml = $viewLogIcon->render($row);

        if ($logIconHtml !== '') {
            return '<div style="min-width: 100px">' . $returnString . $logIconHtml . '</div>';
        }

        return $returnString;
    }

    public function callbackColumnItems($value, $row, $column, $isExport)
    {
        /** @var $items Ess_M2ePro_Model_Order_Item[] */
        $items = $this->_itemsCollection->getItemsByColumnValue('order_id', $row->getData('id'));

        $html = '';
        $gridId = $this->getId();

        foreach ($items as $item) {
            if ($html != '') {
                $html .= '<br/>';
            }

            $isShowEditLink = false;

            try {
                $product = $item->getProduct();
            } catch (Ess_M2ePro_Model_Exception $e) {
                $product = null;
                $logModel = Mage::getModel('M2ePro/Order_Log');
                $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

                $logModel->addMessage(
                    $row->getData('id'),
                    $e->getMessage(),
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                );
            }

            if ($product !== null) {
                /** @var Ess_M2ePro_Model_Magento_Product $magentoProduct */
                $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
                $magentoProduct->setProduct($product);

                $associatedProducts = $item->getAssociatedProducts();
                $associatedOptions = $item->getAssociatedOptions();

                if ($magentoProduct->isProductWithVariations()
                    && empty($associatedOptions)
                    && empty($associatedProducts)
                ) {
                    $isShowEditLink = true;
                }
            }

            $editItemHtml = '';
            if ($isShowEditLink) {
                $orderItemId = $item->getId();
                $orderItemEditLabel = Mage::helper('M2ePro')->__('edit');

                $js = "{OrderEditItemObj.edit('{$gridId}', {$orderItemId});}";

                $editItemHtml = <<<HTML
<span>&nbsp;<a href="javascript:void(0);" onclick="{$js}">[{$orderItemEditLabel}]</a></span>
HTML;
            }

            $skuHtml = '';
            if ($item->getSku()) {
                $skuLabel = Mage::helper('M2ePro')->__('SKU');
                $sku = Mage::helper('M2ePro')->escapeHtml($item->getSku());
                if ($product !== null) {
                    $productUrl = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $product->getId()));
                    $sku = <<<STRING
<a href="{$productUrl}" target="_blank">{$sku}</a>
STRING;
                }

                $skuHtml = <<<STRING
<span style="padding-left: 10px;"><b>{$skuLabel}:</b>&nbsp;{$sku}</span><br/>
STRING;
            }

            $generalIdLabel = Mage::helper('M2ePro')->__($item->getIsIsbnGeneralId() ? 'ISBN' : 'ASIN');
            $generalId = Mage::helper('M2ePro')->escapeHtml($item->getGeneralId());

            $itemUrl = Mage::helper('M2ePro/Component_Amazon')->getItemUrl(
                $item->getGeneralId(),
                $row->getData('marketplace_id')
            );

            $itemLink = '<a href="' . $itemUrl . '" target="_blank">' . $generalId . '</a>';

            $generalIdHtml = <<<STRING
<span style="padding-left: 10px;"><b>{$generalIdLabel}:</b>&nbsp;{$itemLink}</span><br/>
STRING;

            $itemTitle = Mage::helper('M2ePro')->escapeHtml($item->getTitle());
            $qtyLabel = Mage::helper('M2ePro')->__('QTY');
            $qtyHtml = <<<HTML
<span style="padding-left: 10px;"><b>{$qtyLabel}:</b> {$item->getQtyPurchased()}</span>
HTML;

            $html .= <<<HTML
{$itemTitle}&nbsp;{$editItemHtml}<br/>
<small>{$generalIdHtml}{$skuHtml}{$qtyHtml}</small>
HTML;
        }

        return $html;
    }

    public function callbackColumnBuyer($value, $row, $column, $isExport)
    {
        if ($row->getData('buyer_name') == '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return Mage::helper('M2ePro')->escapeHtml($row->getData('buyer_name'));
    }

    public function callbackColumnTotal($value, $row, $column, $isExport)
    {
        $currency = $row->getData('currency');

        if (empty($currency)) {
            /** @var Ess_M2ePro_Model_Marketplace $marketplace */
            $marketplace = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
                'Marketplace',
                $row->getData('marketplace_id')
            );
            /** @var Ess_M2ePro_Model_Amazon_Marketplace $amazonMarketplace */
            $amazonMarketplace = $marketplace->getChildObject();

            $currency = $amazonMarketplace->getDefaultCurrency();
        }

        return Mage::getSingleton('M2ePro/Currency')->formatPrice($currency, $row->getData('paid_amount'));
    }

    public function callbackColumnAfnChannel($value, $row, $column, $isExport)
    {
        switch ($row->getData('is_afn_channel')) {
            case Ess_M2ePro_Model_Amazon_Listing_Product::IS_ISBN_GENERAL_ID_YES:
                $value = '<span style="font-weight: bold;">' . $value . '</span>';
                break;

            default:
                break;
        }

        return $value;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $status = $row->getData('status');

        $statusColors = array(
            Ess_M2ePro_Model_Amazon_Order::STATUS_PENDING                => 'gray',
            Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED                => 'green',
            Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED               => 'red',
            Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELLATION_REQUESTED => 'red'
        );

        $color = isset($statusColors[$status]) ? $statusColors[$status] : 'black';
        $value = '<span style="color: ' . $color . ';">' . $value . '</span>';

        if ($row->isSetProcessingLock('update_order_status')) {
            $value .= '<br/>';
            $value .= '<span style="color: gray;">['
                . Mage::helper('M2ePro')->__('Status Update in Progress...') . ']</span>';
        }

        return $value;
    }

    //########################################

    protected function callbackFilterItems($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $orderItemsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Order_Item');

        $orderItemsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $orderItemsCollection->getSelect()->columns('order_id');
        $orderItemsCollection->getSelect()->distinct(true);

        $orderItemsCollection->getSelect()->where(
            'title LIKE ? OR sku LIKE ? or general_id LIKE ?',
            '%' . $value . '%'
        );

        $totalResult = $orderItemsCollection->getColumnValues('order_id');
        $collection->addFieldToFilter('main_table.id', array('in' => $totalResult));
    }

    protected function callbackFilterBuyer($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $collection
            ->getSelect()
            ->where('buyer_email LIKE ? OR buyer_name LIKE ?', '%' . $value . '%');
    }

    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }
        
        switch ($value) {
            case Ess_M2ePro_Model_Amazon_Order::STATUS_PENDING:
                $collection->addFieldToFilter(
                    'second_table.status',
                    array(Ess_M2ePro_Model_Amazon_Order::STATUS_PENDING)
                );
                break;

            case Ess_M2ePro_Model_Amazon_Order::STATUS_UNSHIPPED:
                $collection->addFieldToFilter(
                    'second_table.status',
                    array(Ess_M2ePro_Model_Amazon_Order::STATUS_UNSHIPPED)
                );
                break;

            case Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED_PARTIALLY:
                $collection->addFieldToFilter(
                    'second_table.status',
                    array(Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED_PARTIALLY)
                );
                break;
            case Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED:
                $collection->addFieldToFilter(
                    'second_table.status',
                    array(Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED)
                );
                break;
            case Ess_M2ePro_Model_Amazon_Order::STATUS_UNFULFILLABLE:
                $collection->addFieldToFilter(
                    'second_table.status',
                    array(Ess_M2ePro_Model_Amazon_Order::STATUS_UNFULFILLABLE)
                );
                break;
            case Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED:
                $collection->addFieldToFilter(
                    'second_table.status',
                    array(Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED)
                );
                break;
            case Ess_M2ePro_Model_Amazon_Order::STATUS_INVOICE_UNCONFIRMED:
                $collection->addFieldToFilter(
                    'second_table.status',
                    array(Ess_M2ePro_Model_Amazon_Order::STATUS_INVOICE_UNCONFIRMED)
                );
                break;
            case Ess_M2ePro_Model_Amazon_Order::STATUS_PENDING_RESERVED:
                $collection->addFieldToFilter(
                    'second_table.status',
                    array(Ess_M2ePro_Model_Amazon_Order::STATUS_PENDING)
                );
                $collection->addFieldToFilter(
                    'reservation_state',
                    array(Ess_M2ePro_Model_Order_Reserve::STATE_PLACED)
                );
                break;
            case Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELLATION_REQUESTED:
                $collection->addFieldToFilter(
                    'second_table.status',
                    array(Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELLATION_REQUESTED)
                );
                break;
        }
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_amazon_order/grid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    protected function _toHtml()
    {
        return $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_order_merchantFulfillment')->toHtml()
            . parent::_toHtml();
    }

    //########################################
}
