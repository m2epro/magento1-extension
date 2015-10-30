<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var $itemsCollection Ess_M2ePro_Model_Mysql4_Order_Item_Collection */
    private $itemsCollection = NULL;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayOrderGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('purchase_create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
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
                       '(mea.account_id = `main_table`.account_id)',
                       array('account_mode' => 'mode'))
                   ->joinLeft(
                       array('so' => Mage::getSingleton('core/resource')->getTableName('sales/order')),
                       '(so.entity_id = `main_table`.magento_order_id)',
                       array('magento_order_num' => 'increment_id'));

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

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        $this->itemsCollection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Order_Item')
            ->addFieldToFilter('order_id', array('in' => $this->getCollection()->getColumnValues('id')));

        return parent::_afterLoadCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('purchase_create_date', array(
            'header' => Mage::helper('M2ePro')->__('Sale Date'),
            'align'  => 'left',
            'type'   => 'datetime',
            'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'  => 'purchase_create_date',
            'width'  => '170px'
        ));

        $this->addColumn('magento_order_num', array(
            'header' => Mage::helper('M2ePro')->__('Magento Order #'),
            'align'  => 'left',
            'index'  => 'so.increment_id',
            'width'  => '200px',
            'frame_callback' => array($this, 'callbackColumnMagentoOrder')
        ));

        $this->addColumn('ebay_order_id', array(
            'header' => Mage::helper('M2ePro')->__('eBay Order #'),
            'align'  => 'left',
            'width'  => '110px',
            'index'  => 'ebay_order_id',
            'frame_callback' => array($this, 'callbackColumnEbayOrder'),
            'filter_condition_callback' => array($this, 'callbackFilterEbayOrderId')
        ));

        $this->addColumn('ebay_order_items', array(
            'header' => Mage::helper('M2ePro')->__('Items'),
            'align'  => 'left',
            'index'  => 'ebay_order_items',
            'sortable' => false,
            'width'  => '*',
            'frame_callback' => array($this, 'callbackColumnItems'),
            'filter_condition_callback' => array($this, 'callbackFilterItems')
        ));

        $this->addColumn('buyer', array(
            'header' => Mage::helper('M2ePro')->__('Buyer'),
            'align'  => 'left',
            'index'  => 'buyer_user_id',
            'frame_callback' => array($this, 'callbackColumnBuyer'),
            'filter_condition_callback' => array($this, 'callbackFilterBuyer'),
            'width'  => '120px'
        ));

        $this->addColumn('paid_amount', array(
            'header' => Mage::helper('M2ePro')->__('Total Paid'),
            'align'  => 'left',
            'width'  => '110px',
            'index'  => 'paid_amount',
            'type'   => 'number',
            'frame_callback' => array($this, 'callbackColumnTotal')
        ));

        $this->addColumn('reservation_state', array(
            'header' => Mage::helper('M2ePro')->__('Reservation'),
            'align'  => 'left',
            'width'  => '50px',
            'index'  => 'reservation_state',
            'type'   => 'options',
            'options' => array(
                Ess_M2ePro_Model_Order_Reserve::STATE_UNKNOWN  => Mage::helper('M2ePro')->__('Not Reserved'),
                Ess_M2ePro_Model_Order_Reserve::STATE_PLACED   => Mage::helper('M2ePro')->__('Reserved'),
                Ess_M2ePro_Model_Order_Reserve::STATE_RELEASED => Mage::helper('M2ePro')->__('Released'),
                Ess_M2ePro_Model_Order_Reserve::STATE_CANCELED => Mage::helper('M2ePro')->__('Canceled'),
            )
        ));

        $this->addColumn('checkout_status', array(
            'header' => Mage::helper('M2ePro')->__('Checkout'),
            'align'  => 'left',
            'width'  => '50px',
            'index'  => 'checkout_status',
            'type'   => 'options',
            'options' => array(
                Ess_M2ePro_Model_Ebay_Order::CHECKOUT_STATUS_INCOMPLETE => Mage::helper('M2ePro')->__('No'),
                Ess_M2ePro_Model_Ebay_Order::CHECKOUT_STATUS_COMPLETED  => Mage::helper('M2ePro')->__('Yes')
            )
        ));

        $this->addColumn('payment_status', array(
            'header' => Mage::helper('M2ePro')->__('Paid'),
            'align'  => 'left',
            'width'  => '50px',
            'index'  => 'payment_status',
            'type'   => 'options',
            'options' => array(
                0 => Mage::helper('M2ePro')->__('No'),
                1 => Mage::helper('M2ePro')->__('Yes')
            ),
            'frame_callback' => array($this, 'callbackColumnPayment'),
            'filter_condition_callback' => array($this, 'callbackFilterPaymentCondition')
        ));

        $this->addColumn('shipping_status', array(
            'header' => Mage::helper('M2ePro')->__('Shipped'),
            'align'  => 'left',
            'width'  => '50px',
            'index'  => 'shipping_status',
            'type'   => 'options',
            'options' => array(
                0 => Mage::helper('M2ePro')->__('No'),
                1 => Mage::helper('M2ePro')->__('Yes')
            ),
            'frame_callback' => array($this, 'callbackColumnShipping'),
            'filter_condition_callback' => array($this, 'callbackFilterShippingCondition')
        ));

        $back = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebay_order/index', array());

        $this->addColumn('action', array(
            'header'  => Mage::helper('M2ePro')->__('Action'),
            'width'   => '80px',
            'type'    => 'action',
            'getter'  => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('M2ePro')->__('View'),
                    'url'     => array('base' => '*/adminhtml_ebay_order/view'),
                    'field'   => 'id'
                ),
                array(
                    'caption' => Mage::helper('M2ePro')->__('Edit Shipping Address'),
                    'url'     => array('base' => '*/adminhtml_ebay_order/editShippingAddress/', 'params' => array(
                        'back' => $back
                    )),
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
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        // Set mass-action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem('reservation_place', array(
             'label'    => Mage::helper('M2ePro')->__('Reserve QTY'),
             'url'      => $this->getUrl('*/adminhtml_order/reservationPlace'),
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('reservation_cancel', array(
             'label'    => Mage::helper('M2ePro')->__('Cancel QTY Reserve'),
             'url'      => $this->getUrl('*/adminhtml_order/reservationCancel'),
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('ship', array(
             'label'    => Mage::helper('M2ePro')->__('Mark Order(s) as Shipped'),
             'url'      => $this->getUrl('*/adminhtml_ebay_order/updateShippingStatus'),
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('pay', array(
             'label'    => Mage::helper('M2ePro')->__('Mark Order(s) as Paid'),
             'url'      => $this->getUrl('*/adminhtml_ebay_order/updatePaymentStatus'),
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('resend_shipping', array(
             'label'    => Mage::helper('M2ePro')->__('Resend Shipping Information'),
             'url'      => $this->getUrl('*/adminhtml_order/resubmitShippingInfo'),
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnMagentoOrder($value, $row, $column, $isExport)
    {
        $magentoOrderId = $row['magento_order_id'];
        $magentoOrderNumber = Mage::helper('M2ePro')->escapeHtml($row['magento_order_num']);

        $returnString = Mage::helper('M2ePro')->__('N/A');

        if ($row['magento_order_id']) {
            if ($row['magento_order_num']) {
                $orderUrl = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $magentoOrderId));
                $returnString = '<a href="' . $orderUrl . '" target="_blank">' . $magentoOrderNumber . '</a>';
            } else {
                $returnString = '<span style="color: red;">'.Mage::helper('M2ePro')->__('Deleted').'</span>';
            }
        }

        return $returnString.$this->getViewLogIconHtml($row->getId());
    }

    private function getViewLogIconHtml($orderId)
    {
        $orderId = (int)$orderId;

        // Prepare collection
        // ---------------------------------------
        $orderLogsCollection = Mage::getModel('M2ePro/Order_Log')->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->setOrder('id', 'DESC');
        $orderLogsCollection->getSelect()
            ->limit(3);
        // ---------------------------------------

        // Prepare logs data
        // ---------------------------------------
        if ($orderLogsCollection->getSize() <= 0) {
            return '';
        }

        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        $logRows = array();
        foreach ($orderLogsCollection as $log) {
            $logRows[] = array(
                'type' => $log->getData('type'),
                'text' => Mage::helper('M2ePro/View')->getModifiedLogMessage($log->getData('description')),
                'initiator' => $this->getInitiatorForAction($log->getData('initiator')),
                'date' => Mage::app()->getLocale()->date(strtotime($log->getData('create_date')))->toString($format)
            );
        }
        // ---------------------------------------

        $tips = array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 'Last order Action was completed successfully.',
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 'Last order Action was completed with error(s).',
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 'Last order Action was completed with warning(s).'
        );

        $icons = array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 'normal',
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 'error',
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 'warning'
        );

        $summary = $this->getLayout()->createBlock('M2ePro/adminhtml_log_grid_summary', '', array(
            'entity_id' => $orderId,
            'rows' => $logRows,
            'tips' => $tips,
            'icons' => $icons,
            'view_help_handler' => 'OrderHandlerObj.viewOrderHelp',
            'hide_help_handler' => 'OrderHandlerObj.hideOrderHelp',
        ));

        return $summary->toHtml();
    }

    public function getInitiatorForAction($initiator)
    {
        $string = '';

        switch ((int)$initiator) {
            case Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN:
                $string = '';
                break;
            case Ess_M2ePro_Helper_Data::INITIATOR_USER:
                $string = Mage::helper('M2ePro')->__('Manual');
                break;
            case Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION:
                $string = Mage::helper('M2ePro')->__('Automatic');
                break;
        }

        return $string;
    }

    // ---------------------------------------

    public function callbackColumnEbayOrder($value, $row, $column, $isExport)
    {
        $returnString = str_replace('-', '-<br/>', $value);

        if ($row['selling_manager_id'] > 0) {
            $returnString .= '<br/> [ <b>SM: </b> # ' . $row['selling_manager_id'] . ' ]';
        }

        return $returnString;
    }

    public function callbackColumnItems($value, $row, $column, $isExport)
    {
        /** @var $items Ess_M2ePro_Model_Order_Item[] */
        $items = $this->itemsCollection->getItemsByColumnValue('order_id', $row->getData('id'));

        $html = '';
        $gridId = $this->getId();

        foreach ($items as $item) {
            if ($html != '') {
                $html .= '<br/>';
            }

            $isShowEditLink = false;

            $product = $item->getProduct();
            if (!is_null($product)) {
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

                $js = "{OrderEditItemHandlerObj.edit('{$gridId}', {$orderItemId});}";

                $editItemHtml = <<<HTML
<span>&nbsp;<a href="javascript:void(0);" onclick="{$js}">[{$orderItemEditLabel}]</a></span>
HTML;
            }

            $skuHtml = '';
            if ($item->getSku()) {
                $skuLabel = Mage::helper('M2ePro')->__('SKU');
                $sku = Mage::helper('M2ePro')->escapeHtml($item->getSku());

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
                $item->getItemId(), $row->getData('account_mode'), (int)$row->getData('marketplace_id')
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
        $returnString = '';
        $returnString .= Mage::helper('M2ePro')->escapeHtml($row->getData('buyer_name')) . '<br/>';

        $buyerEmail = $row->getData('buyer_email');
        if ($buyerEmail && $buyerEmail != 'Invalid Request') {
            $returnString .= '&lt;' . $buyerEmail  . '&gt;<br/>';
        }

        $returnString .= Mage::helper('M2ePro')->escapeHtml($row->getData('buyer_user_id'));

        return $returnString;
    }

    public function callbackColumnTotal($value, $row, $column, $isExport)
    {
        return Mage::getSingleton('M2ePro/Currency')->formatPrice(
            $row->getData('currency'), $row->getData('paid_amount')
        );
    }

    public function callbackColumnShipping($value, $row, $column, $isExport)
    {
        if ($row->getData('shipping_status') == Ess_M2ePro_Model_Ebay_Order::SHIPPING_STATUS_COMPLETED) {
            return Mage::helper('M2ePro')->__('Yes');
        } else {
            return Mage::helper('M2ePro')->__('No');
        }
    }

    public function callbackColumnPayment($value, $row, $column, $isExport)
    {
        if ($row->getData('payment_status') == Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED) {
            return Mage::helper('M2ePro')->__('Yes');
        } else {
            return Mage::helper('M2ePro')->__('No');
        }
    }

    //########################################

    protected function callbackFilterEbayOrderId($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $collection
            ->getSelect()
                ->where('ebay_order_id LIKE ? OR selling_manager_id LIKE ?', '%'.$value.'%');
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
                ->where('item_id LIKE ? OR title LIKE ? OR sku LIKE ? OR transaction_id LIKE ?', '%'.$value.'%');

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
                ->where('buyer_email LIKE ? OR buyer_user_id LIKE ? OR buyer_name LIKE ?', '%'.$value.'%');
    }

    protected function callbackFilterPaymentCondition($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value === null) {
            return;
        }
        $filterType = ($value == 1) ? 'eq' : 'neq';
        $this->getCollection()->addFieldToFilter(
            'payment_status', array($filterType => Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED)
        );
    }

    protected function callbackFilterShippingCondition($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value === null) {
            return;
        }
        $filterType = ($value == 1) ? 'eq' : 'neq';
        $this->getCollection()->addFieldToFilter(
            'shipping_status', array($filterType => Ess_M2ePro_Model_Ebay_Order::SHIPPING_STATUS_COMPLETED)
        );
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_order/grid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        $back = Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_ebay_order/index'
        );

        return $this->getUrl('*/adminhtml_ebay_order/view', array('id' => $row->getId(), 'back' => $back));
    }

    //########################################

    protected function _toHtml()
    {
        $tempGridIds = array();
        Mage::helper('M2ePro/Component_Ebay')->isActive() && $tempGridIds[] = $this->getId();

        $generalBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_order_general');
        $generalBlock->setGridIds($tempGridIds);

        return $generalBlock->toHtml() . parent::_toHtml();
    }

    //########################################
}