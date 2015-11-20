<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var $itemsCollection Ess_M2ePro_Model_Mysql4_Order_Item_Collection */
    private $itemsCollection = NULL;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonOrderGrid');
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
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Order');

        $collection->getSelect()
                   ->joinLeft(
                       array('so' => Mage::getSingleton('core/resource')->getTableName('sales/order')),
                       '(so.entity_id = `main_table`.magento_order_id)',
                       array('magento_order_num' => 'increment_id'));

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

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        $this->itemsCollection = Mage::helper('M2ePro/Component_Amazon')
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
            'width'  => '110px',
            'frame_callback' => array($this, 'callbackColumnMagentoOrder')
        ));

        $this->addColumn('amazon_order_id', array(
            'header' => Mage::helper('M2ePro')->__('Amazon Order #'),
            'align'  => 'left',
            'width'  => '110px',
            'index'  => 'amazon_order_id',
            'frame_callback' => array($this, 'callbackColumnAmazonOrderId')
        ));

        $this->addColumn('amazon_order_items', array(
            'header' => Mage::helper('M2ePro')->__('Items'),
            'align'  => 'left',
            'index'  => 'amazon_order_items',
            'sortable' => false,
            'width'  => '*',
            'frame_callback' => array($this, 'callbackColumnItems'),
            'filter_condition_callback' => array($this, 'callbackFilterItems')
        ));

        $this->addColumn('buyer', array(
            'header' => Mage::helper('M2ePro')->__('Buyer'),
            'align'  => 'left',
            'index'  => 'buyer_name',
            'width'  => '120px',
            'frame_callback' => array($this, 'callbackColumnBuyer'),
            'filter_condition_callback' => array($this, 'callbackFilterBuyer')
        ));

        $this->addColumn('paid_amount', array(
            'header' => Mage::helper('M2ePro')->__('Total Paid'),
            'align'  => 'left',
            'width'  => '110px',
            'index'  => 'paid_amount',
            'type'   => 'number',
            'frame_callback' => array($this, 'callbackColumnTotal')
        ));

        $this->addColumn('is_afn_channel', array(
            'header' => Mage::helper('M2ePro')->__('Fulfillment'),
            'width' => '100px',
            'index' => 'is_afn_channel',
            'filter_index' => 'second_table.is_afn_channel',
            'type' => 'options',
            'sortable' => false,
            'options' => array(
                0 => Mage::helper('M2ePro')->__('Merchant'),
                1 => Mage::helper('M2ePro')->__('Amazon')
            ),
            'frame_callback' => array($this, 'callbackColumnAfnChannel')
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

        $helper = Mage::helper('M2ePro');

        $this->addColumn('status', array(
            'header'  => Mage::helper('M2ePro')->__('Status'),
            'align'   => 'left',
            'width'   => '50px',
            'index'   => 'status',
            'filter_index' => 'second_table.status',
            'type'    => 'options',
            'options' => array(
                Ess_M2ePro_Model_Amazon_Order::STATUS_PENDING             => $helper->__('Pending'),
                Ess_M2ePro_Model_Amazon_Order::STATUS_UNSHIPPED           => $helper->__('Unshipped'),
                Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED_PARTIALLY   => $helper->__('Partially Shipped'),
                Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED             => $helper->__('Shipped'),
                Ess_M2ePro_Model_Amazon_Order::STATUS_INVOICE_UNCONFIRMED => $helper->__('Invoice Not Confirmed'),
                Ess_M2ePro_Model_Amazon_Order::STATUS_UNFULFILLABLE       => $helper->__('Unfulfillable'),
                Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED            => $helper->__('Canceled')
            ),
            'frame_callback' => array($this, 'callbackColumnStatus')
        ));

        $back = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_common_order/index', array(
            'tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_AMAZON
        ));

        $this->addColumn('action', array(
            'header'   => Mage::helper('M2ePro')->__('Action'),
            'width'    => '80px',
            'type'     => 'action',
            'getter'   => 'getId',
            'renderer' => 'M2ePro/adminhtml_grid_column_renderer_action',
            'actions' => array(
                array(
                    'caption' => Mage::helper('M2ePro')->__('View'),
                    'url'     => array('base' => '*/adminhtml_common_amazon_order/view'),
                    'field'   => 'id'
                ),
                array(
                    'caption' => Mage::helper('M2ePro')->__('Edit Shipping Address'),
                    'url'     => array('base' => '*/adminhtml_common_amazon_order/editShippingAddress/back/'.$back.'/'),
                    'field'   => 'id'
                ),
                array(
                    'caption' => Mage::helper('M2ePro')->__('Create Order'),
                    'url'     => array('base' => '*/adminhtml_common_amazon_order/createMagentoOrder'),
                    'field'   => 'id'
                ),
                array(
                    'caption' => Mage::helper('M2ePro')->__('Mark As Shipped'),
                    'field'   => 'id',
                    'onclick_action' => 'OrderMerchantFulfillmentHandlerObj.markAsShippedAction'
                ),
                array(
                    'caption' => Mage::helper('M2ePro')->__('Amazon\'s Shipping Services'),
                    'field'   => 'id',
                    'onclick_action' => 'OrderMerchantFulfillmentHandlerObj.getPopupAction'
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
             'url'      => $this->getUrl('*/adminhtml_common_amazon_order/updateShippingStatus'),
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

    public function callbackColumnAmazonOrderId($value, $row, $column, $isExport)
    {
        $orderId = Mage::helper('M2ePro')->escapeHtml($row->getData('amazon_order_id'));
        $url = Mage::helper('M2ePro/Component_Amazon')->getOrderUrl($orderId, $row->getData('marketplace_id'));

        $primeImageHtml = '';
        if ($row['is_prime']) {
            $primeImageHtml = '<div><img src="' . $this->getSkinUrl('M2ePro/images/prime.png') . '" /></div>';
        }

        return <<<HTML
<a href="{$url}" target="_blank">{$orderId}</a> {$primeImageHtml}
HTML;
    }

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
        if ($orderLogsCollection->count() <= 0) {
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
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 'Last Order Action was completed successfully.',
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 'Last Order Action was completed with error(s).',
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 'Last Order Action was completed with warning(s).'
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

                $skuHtml = <<<STRING
<span style="padding-left: 10px;"><b>{$skuLabel}:</b>&nbsp;{$sku}</span><br/>
STRING;
            }

            $generalIdLabel = Mage::helper('M2ePro')->__($item->getIsIsbnGeneralId() ? 'ISBN' : 'ASIN');
            $generalId = Mage::helper('M2ePro')->escapeHtml($item->getGeneralId());

            $itemUrl = Mage::helper('M2ePro/Component_Amazon')->getItemUrl($item->getGeneralId(),
                                                                           $row->getData('marketplace_id'));

            $itemLink = '<a href="'.$itemUrl.'" target="_blank">'.$generalId.'</a>';

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

        $html = Mage::helper('M2ePro')->escapeHtml($row->getData('buyer_name'));

        if ($row->getData('buyer_email') != '') {
            $html .= '<br/>';
            $html .= '&lt;' . Mage::helper('M2ePro')->escapeHtml($row->getData('buyer_email')) . '&gt;';
        }

        return $html;
    }

    public function callbackColumnTotal($value, $row, $column, $isExport)
    {
        $currency = $row->getData('currency');

        if (empty($currency)) {
            /** @var Ess_M2ePro_Model_Marketplace $marketplace */
            $marketplace = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
                'Marketplace', $row->getData('marketplace_id')
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
            Ess_M2ePro_Model_Amazon_Order::STATUS_PENDING  => 'gray',
            Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED  => 'green',
            Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED => 'red'
        );

        $color = isset($statusColors[$status]) ? $statusColors[$status] : 'black';
        $value = '<span style="color: '.$color.';">'.$value.'</span>';

        if ($row->isLockedObject('update_order_status')) {
            $value .= '<br/>';
            $value .= '<span style="color: gray;">['
                      .Mage::helper('M2ePro')->__('Status Update in Progress...').']</span>';
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

        $orderItemsCollection->getSelect()->where('title LIKE ? OR sku LIKE ? or general_id LIKE ?', '%'.$value.'%');

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
                ->where('buyer_email LIKE ? OR buyer_name LIKE ?', '%'.$value.'%');
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_common_amazon_order/grid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        $back = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_common_order/index', array(
            'tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_AMAZON
        ));

        return $this->getUrl('*/adminhtml_common_amazon_order/view', array('id' => $row->getId(), 'back' => $back));
    }

    protected function _toHtml()
    {
        return $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_order_merchantFulfillment')->toHtml()
            . parent::_toHtml();
    }

    //########################################
}