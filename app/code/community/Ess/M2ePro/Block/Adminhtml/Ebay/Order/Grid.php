<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var $_itemsCollection Ess_M2ePro_Model_Resource_Order_Item_Collection */
    protected $_itemsCollection = null;

    /** @var $_notesCollection Ess_M2ePro_Model_Resource_Order_Note_Collection */
    protected $_notesCollection = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayOrderGrid');

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
        /** @var $collection Varien_Data_Collection_Db */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order');

        $collection->getSelect()
            ->joinLeft(
                array('mea' => Mage::getResourceModel('M2ePro/Ebay_Account')->getMainTable()),
                'mea.account_id = `main_table`.account_id',
                array('account_mode' => 'mode')
            )
            ->joinLeft(
                array(
                    'so' => Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('sales/order')
                ),
                '(so.entity_id = `main_table`.magento_order_id)',
                array('magento_order_num' => 'increment_id')
            );

        // Add Filter By Account
        // ---------------------------------------
        if ($accountId = $this->getRequest()->getParam('ebayAccount')) {
            $collection->addFieldToFilter('main_table.account_id', $accountId);
        }

        // ---------------------------------------

        // Add Filter By Marketplace
        // ---------------------------------------
        if ($marketplaceId = $this->getRequest()->getParam('ebayMarketplace')) {
            $collection->addFieldToFilter('main_table.marketplace_id', $marketplaceId);
        }

        // ---------------------------------------

        // Add Not Created Magento Orders Filter
        // ---------------------------------------
        if ($this->getRequest()->getParam('not_created_only')) {
            $collection->addFieldToFilter('magento_order_id', array('null' => true));
        }

        // ---------------------------------------

        // Add Order Status column
        // ---------------------------------------
        $shippingCompleted = Ess_M2ePro_Model_Ebay_Order::SHIPPING_STATUS_COMPLETED;
        $paymentCompleted = Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED;

        $statusList = array(
            'pending'   => Ess_M2ePro_Model_Ebay_Order::STATUS_PENDING,
            'unshipped' => Ess_M2ePro_Model_Ebay_Order::STATUS_UNSHIPPED,
            'shipped'   => Ess_M2ePro_Model_Ebay_Order::STATUS_SHIPPED,
            'canceled'  => Ess_M2ePro_Model_Ebay_Order::STATUS_CANCELED
        );

        $collection->getSelect()->columns(
            array(
                'status' => new Zend_Db_Expr(
                    "IF (
                        `cancellation_status` = 1,
                        {$statusList['canceled']},
                        IF (
                            `shipping_status` = {$shippingCompleted},
                            {$statusList['shipped']},
                            IF (
                                `payment_status` = {$paymentCompleted},
                                {$statusList['unshipped']},
                                {$statusList['pending']}
                            )
                        )
                    )"
                )
            )
        );

        // ---------------------------------------

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        $this->_itemsCollection = Mage::helper('M2ePro/Component_Ebay')
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
            'magento_order_num',
            array(
                'header'         => Mage::helper('M2ePro')->__('Magento Order #'),
                'align'          => 'left',
                'index'          => 'so.increment_id',
                'width'          => '200px',
                'frame_callback' => array($this, 'callbackColumnMagentoOrder')
            )
        );

        $this->addColumn(
            'ebay_order_id',
            array(
                'header'                    => Mage::helper('M2ePro')->__('eBay Order #'),
                'align'                     => 'left',
                'width'                     => '145px',
                'index'                     => 'ebay_order_id',
                'frame_callback'            => array($this, 'callbackColumnEbayOrder'),
                'filter'                    => 'M2ePro/adminhtml_ebay_grid_column_filter_orderId',
                'filter_condition_callback' => array($this, 'callbackFilterEbayOrderId')
            )
        );

        $this->addColumn(
            'ebay_order_items',
            array(
                'header'                    => Mage::helper('M2ePro')->__('Items'),
                'align'                     => 'left',
                'index'                     => 'ebay_order_items',
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
                'index'                     => 'buyer_user_id',
                'frame_callback'            => array($this, 'callbackColumnBuyer'),
                'filter_condition_callback' => array($this, 'callbackFilterBuyer'),
                'width'                     => '120px'
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

        $helper = Mage::helper('M2ePro');

        $this->addColumn(
            'status',
            array(
                'header'                    => Mage::helper('M2ePro')->__('Status'),
                'align'                     => 'left',
                'width'                     => '50px',
                'index'                     => 'status',
                'type'                      => 'options',
                'options'                   => array(
                    Ess_M2ePro_Model_Ebay_Order::STATUS_PENDING          => $helper->__('Pending'),
                    Ess_M2ePro_Model_Ebay_Order::STATUS_PENDING_RESERVED => $helper->__('Pending / QTY Reserved'),
                    Ess_M2ePro_Model_Ebay_Order::STATUS_UNSHIPPED        => $helper->__('Unshipped'),
                    Ess_M2ePro_Model_Ebay_Order::STATUS_SHIPPED          => $helper->__('Shipped'),
                    Ess_M2ePro_Model_Ebay_Order::STATUS_CANCELED         => $helper->__('Canceled')
                ),
                'frame_callback'            => array($this, 'callbackColumnStatus'),
                'filter_condition_callback' => array($this, 'callbackFilterStatus'),
            )
        );

        $back = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebay_order/index', array());

        $this->addColumn(
            'action',
            array(
                'header'    => Mage::helper('M2ePro')->__('Action'),
                'width'     => '80px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('M2ePro')->__('View'),
                        'url'     => array('base' => '*/adminhtml_ebay_order/view'),
                        'field'   => 'id'
                    ),
                    array(
                        'caption' => Mage::helper('M2ePro')->__('Edit Shipping Address'),
                        'url'     => array(
                            'base'   => '*/adminhtml_ebay_order/editShippingAddress/',
                            'params' => array(
                                'back' => $back
                            )
                        ),
                        'field'   => 'id'
                    ),
                    array(
                        'caption' => Mage::helper('M2ePro')->__('Create Order'),
                        'url'     => array('base' => '*/adminhtml_ebay_order/createMagentoOrder'),
                        'field'   => 'id'
                    ),
                    array(
                        'caption' => Mage::helper('M2ePro')->__('Mark As Paid'),
                        'url'     => array('base' => '*/adminhtml_ebay_order/updatePaymentStatus'),
                        'field'   => 'id'
                    ),
                    array(
                        'caption' => Mage::helper('M2ePro')->__('Mark As Shipped'),
                        'url'     => array('base' => '*/adminhtml_ebay_order/updateShippingStatus'),
                        'field'   => 'id'
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

        $groups = array(
            'general' => Mage::helper('M2ePro')->__('General'),
        );

        $this->getMassactionBlock()->setGroups($groups);

        // Set mass-action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem(
            'reservation_place',
            array(
                'label'   => Mage::helper('M2ePro')->__('Reserve QTY'),
                'url'     => $this->getUrl('*/adminhtml_order/reservationPlace'),
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'general'
        );

        $this->getMassactionBlock()->addItem(
            'reservation_cancel',
            array(
                'label'   => Mage::helper('M2ePro')->__('Cancel QTY Reserve'),
                'url'     => $this->getUrl('*/adminhtml_order/reservationCancel'),
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'general'
        );

        $this->getMassactionBlock()->addItem(
            'ship',
            array(
                'label'   => Mage::helper('M2ePro')->__('Mark Order(s) as Shipped'),
                'url'     => $this->getUrl('*/adminhtml_ebay_order/updateShippingStatus'),
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'general'
        );

        $this->getMassactionBlock()->addItem(
            'pay',
            array(
                'label'   => Mage::helper('M2ePro')->__('Mark Order(s) as Paid'),
                'url'     => $this->getUrl('*/adminhtml_ebay_order/updatePaymentStatus'),
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'general'
        );

        $this->getMassactionBlock()->addItem(
            'resend_shipping',
            array(
                'label'   => Mage::helper('M2ePro')->__('Resend Shipping Information'),
                'url'     => $this->getUrl('*/adminhtml_order/resubmitShippingInfo'),
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'general'
        );

        $this->getMassactionBlock()->addItem(
            'create_order',
            array(
                'label'   => Mage::helper('M2ePro')->__('Create Magento Order'),
                'url'     => $this->getUrl('*/adminhtml_ebay_order/createMagentoOrder'),
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'general'
        );

        return parent::_prepareMassaction();
    }

    //########################################

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

    public function callbackColumnEbayOrder($value, $row, $column, $isExport)
    {
        $back = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebay_order/index');
        $itemUrl = $this->getUrl('*/adminhtml_ebay_order/view', array('id' => $row->getId(), 'back' => $back));

        $returnString = <<<HTML
<a href="{$itemUrl}">{$value}</a>
HTML;
        if ($row['selling_manager_id']) {
            $returnString .= '<br/> [ <b>SM: </b> # ' . $row['selling_manager_id'] . ' ]';
        }

        /** @var $notes Ess_M2ePro_Model_Order_Note[] */
        $notes = $this->_notesCollection->getItemsByColumnValue('order_id', $row->getData('id'));

        if ($notes) {
            $htmlNotesCount = Mage::helper('M2ePro/Data')->__(
                'You have a custom note for the order. It can be reviewed on the order detail page.'
            );

            $returnString .= <<<HTML
<div class="note_icon" style="display: inline-block; margin-left: 5px; width: 16px;">
    <img class="tool-tip-image"
         style="vertical-align: middle; cursor: inherit"
         src="{$this->getSkinUrl('M2ePro/images/fam_book_open.png')}">
    <span class="tool-tip-message tool-tip-message" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/fam_book_open.png')}" style="width: 18px; height: 18px">
        <div class="ebay-identifiers">
           {$htmlNotesCount}
        </div>
    </span>
</div>
HTML;
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
                $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

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

                $skuHtml = <<<HTML
<span style="padding-left: 10px;"><b>{$skuLabel}:</b>&nbsp;{$sku}</span><br/>
HTML;
            }

            $variation = $item->getChildObject()->getVariationOptions();
            $variationHtml = '';

            if (!empty($variation)) {
                $optionsLabel = Mage::helper('M2ePro')->__('Options');

                $additionalHtml = '';
                if ($isShowEditLink) {
                    $additionalHtml = $editItemHtml;
                }

                $variationHtml .= <<<HTML
<span style="padding-left: 10px;"><b>{$optionsLabel}:</b>{$additionalHtml}</span><br/>
HTML;

                foreach ($variation as $optionName => $optionValue) {
                    $optionName = Mage::helper('M2ePro')->escapeHtml($optionName);
                    $optionValue = Mage::helper('M2ePro')->escapeHtml($optionValue);

                    $variationHtml .= <<<HTML
<span style="padding-left: 20px;"><b><i>{$optionName}</i>:</b>&nbsp;{$optionValue}</span><br/>
HTML;
                }
            }

            $qtyLabel = Mage::helper('M2ePro')->__('QTY');
            $qty = (int)$item->getQtyPurchased();

            $transactionHtml = <<<HTML
<span style="padding-left: 10px;"><b>{$qtyLabel}:</b>&nbsp;{$qty}</span><br/>
HTML;

            if ($item->getTransactionId()) {
                $transactionLabel = Mage::helper('M2ePro')->__('Transaction');
                $transactionId = Mage::helper('M2ePro')->escapeHtml($item->getTransactionId());

                $transactionHtml .= <<<HTML
<span style="padding-left: 10px;"><b>{$transactionLabel}:</b>&nbsp;{$transactionId}</span>
HTML;
            }

            $itemUrl = Mage::helper('M2ePro/Component_Ebay')->getItemUrl(
                $item->getItemId(),
                $row->getData('account_mode'),
                (int)$row->getData('marketplace_id')
            );
            $itemLabel = Mage::helper('M2ePro')->__('Item');
            $itemId = Mage::helper('M2ePro')->escapeHtml($item->getItemId());
            $itemTitle = Mage::helper('M2ePro')->escapeHtml($item->getTitle());

            $html .= <<<HTML
<b>{$itemLabel}: #</b> <a href="{$itemUrl}" target="_blank">{$itemId}</a><br/>
{$itemTitle}<br/>
<small>{$skuHtml}{$variationHtml}{$transactionHtml}</small>
HTML;
        }

        return $html;
    }

    public function callbackColumnBuyer($value, $row, $column, $isExport)
    {
        $returnString = Mage::helper('M2ePro')->escapeHtml($row->getData('buyer_name')) . '<br/>';
        $returnString .= Mage::helper('M2ePro')->escapeHtml($row->getData('buyer_user_id'));

        return $returnString;
    }

    public function callbackColumnTotal($value, $row, $column, $isExport)
    {
        return Mage::getSingleton('M2ePro/Currency')->formatPrice(
            $row->getData('currency'),
            $row->getData('paid_amount')
        );
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $status = $row->getData('status');

        $statusColors = array(
            Ess_M2ePro_Model_Ebay_Order::STATUS_PENDING  => 'gray',
            Ess_M2ePro_Model_Ebay_Order::STATUS_SHIPPED  => 'green',
            Ess_M2ePro_Model_Ebay_Order::STATUS_CANCELED => 'red'
        );

        $color = isset($statusColors[$status]) ? $statusColors[$status] : 'black';
        $value = '<span style="color: ' . $color . ';">' . $value . '</span>';

        return $value;
    }

    //########################################

    protected function callbackFilterEbayOrderId($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if (empty($value)) {
            return;
        }

        if (!empty($value['value'])) {
            $collection
                ->getSelect()
                ->where('ebay_order_id LIKE ? OR selling_manager_id LIKE ?', '%' . $value['value'] . '%');
        }
    }

    protected function callbackFilterItems($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $orderItemsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order_Item');

        $orderItemsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $orderItemsCollection->getSelect()->columns('order_id');
        $orderItemsCollection->getSelect()->distinct(true);

        $orderItemsCollection
            ->getSelect()
            ->where('item_id LIKE ? OR title LIKE ? OR sku LIKE ? OR transaction_id LIKE ?', '%' . $value . '%');

        $ordersIds = $orderItemsCollection->getColumnValues('order_id');
        $collection->addFieldToFilter('main_table.id', array('in' => $ordersIds));
    }

    protected function callbackFilterBuyer($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $collection
            ->getSelect()
            ->where('buyer_email LIKE ? OR buyer_user_id LIKE ? OR buyer_name LIKE ?', '%' . $value . '%');
    }

    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        if ($value == Ess_M2ePro_Model_Ebay_Order::STATUS_CANCELED) {
            $collection->addFieldToFilter('cancellation_status', 1);
            return;
        }

        $collection->addFieldToFilter('cancellation_status', 0);
        switch ($value) {
            case Ess_M2ePro_Model_Ebay_Order::STATUS_SHIPPED:
                $collection->addFieldToFilter(
                    'shipping_status',
                    Ess_M2ePro_Model_Ebay_Order::SHIPPING_STATUS_COMPLETED
                );
                break;

            case Ess_M2ePro_Model_Ebay_Order::STATUS_UNSHIPPED:
                $collection->addFieldToFilter(
                    'payment_status',
                    Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED
                );
                $collection->addFieldToFilter(
                    'shipping_status',
                    array('neq' => Ess_M2ePro_Model_Ebay_Order::SHIPPING_STATUS_COMPLETED)
                );
                break;

            case Ess_M2ePro_Model_Ebay_Order::STATUS_PENDING:
                $collection->addFieldToFilter(
                    'payment_status',
                    array('neq' => Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED)
                );
                $collection->addFieldToFilter(
                    'shipping_status',
                    array('neq' => Ess_M2ePro_Model_Ebay_Order::SHIPPING_STATUS_COMPLETED)
                );
                break;
            case Ess_M2ePro_Model_Ebay_Order::STATUS_PENDING_RESERVED:
                $collection->addFieldToFilter(
                    'payment_status',
                    array('neq' => Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED)
                );
                $collection->addFieldToFilter(
                    'reservation_state',
                    array(Ess_M2ePro_Model_Order_Reserve::STATE_PLACED)
                );
                break;
        }
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_order/grid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function _toHtml()
    {
        $tempGridIds = array();
        Mage::helper('M2ePro/Component_Ebay')->isEnabled() && $tempGridIds[] = $this->getId();

        $generalBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_order_general');
        $generalBlock->setGridIds($tempGridIds);

        return $generalBlock->toHtml() . parent::_toHtml();
    }

    //########################################
}
