<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Order_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $channel = $this->getRequest()->getParam('channel');

        // Initialization block
        // ---------------------------------------
        $this->setId(ucfirst($channel) . 'OrderLogGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    protected function _prepareCollection()
    {
        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Order_Log')->getCollection();

        $collection->getSelect()->joinLeft(
            array('mo' => Mage::getResourceModel('M2ePro/Order')->getMainTable()),
            '(mo.id = `main_table`.order_id)',
            array('magento_order_id')
        );

        $collection->getSelect()->joinLeft(
            array('so' => Mage::getSingleton('core/resource')->getTableName('sales/order')),
            '(so.entity_id = `mo`.magento_order_id)',
            array('magento_order_number' => 'increment_id')
        );

        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId && !$this->getRequest()->isXmlHttpRequest()) {
            $collection->addFieldToFilter('main_table.order_id', (int)$orderId);

            /** @var Ess_M2ePro_Model_Order $order */
            $order = Mage::helper('M2ePro/Component')->getUnknownObject('Order', (int)$orderId);
            $channelOrderId = $order->getData($order->getComponentMode().'_order_id');

            $this->_setFilterValues(array(
                'channel_order_id' => $channelOrderId,
                'component_mode'   => $order->getComponentMode(),
            ));
        }

        $channel = $this->getRequest()->getParam('channel');
        if (!empty($channel) && $channel != Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::CHANNEL_ID_ALL) {
            $collection->getSelect()->where('main_table.component_mode = ?', $channel);
        } else {
            $components = Mage::helper('M2ePro/View')->getComponentHelper()->getActiveComponents();
            $collection->addFieldToFilter('main_table.component_mode', array('in'=>$components));
        }

        // ---------------------------------------
        if ($this->getRequest()->getParam('sort', 'create_date') == 'create_date') {
            $collection->setOrder('id', $this->getRequest()->getParam('dir', 'DESC'));
        }
        // ---------------------------------------

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'width'     => '165px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'create_date',
            'filter_index' => 'main_table.create_date'
        ));

        $this->addColumn('magento_order_number', array(
            'header'    => Mage::helper('M2ePro')->__('Magento Order #'),
            'align'     => 'left',
            'width'     => '150px',
            'index'     => 'so.increment_id',
            'sortable'      => false,
            'frame_callback' => array($this, 'callbackColumnMagentoOrderNumber')
        ));

        $this->addColumn('channel_order_id', array(
            'header'    => Mage::helper('M2ePro')->__('Order #'),
            'align'     => 'left',
            'width'     => '180px',
            'sortable'  => false,
            'index'     => 'channel_order_id',
            'frame_callback' => array($this, 'callbackColumnChannelOrderId'),
            'filter_condition_callback' => array($this, 'callbackFilterChannelOrderId')
        ));

        $this->addColumn('description', array(
            'header'    => Mage::helper('M2ePro')->__('Description'),
            'align'     => 'left',
            'width'     => '*',
            'index'     => 'description',
            'frame_callback' => array($this, 'callbackColumnDescription')
        ));

        $this->addColumn('initiator', array(
            'header'    => Mage::helper('M2ePro')->__('Run Mode'),
            'align'     => 'left',
            'width'     => '65px',
            'index'     => 'initiator',
            'sortable'      => false,
            'type'      => 'options',
            'options'   => array(
                Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN   => Mage::helper('M2ePro')->__('Unknown'),
                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION => Mage::helper('M2ePro')->__('Automatic'),
                Ess_M2ePro_Helper_Data::INITIATOR_USER      => Mage::helper('M2ePro')->__('Manual'),
            ),
            'frame_callback' => array($this, 'callbackColumnInitiator')
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('M2ePro')->__('Type'),
            'align'     => 'left',
            'width'     => '65px',
            'index'     => 'type',
            'type'      => 'options',
            'sortable'      => false,
            'options'   => array(
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE => Mage::helper('M2ePro')->__('Notice'),
                Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => Mage::helper('M2ePro')->__('Success'),
                Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => Mage::helper('M2ePro')->__('Warning'),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => Mage::helper('M2ePro')->__('Error'),
            ),
            'frame_callback' => array($this, 'callbackColumnType')
        ));

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnDescription($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro/View')->getModifiedLogMessage($row->getData('description'));
    }

    public function callbackColumnType($value, $row, $column, $isExport)
    {
        $type = $row->getData('type');

        switch ($type) {
            case Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS:
                $message = "<span style=\"color: green;\">{$value}</span>";
                break;
            case Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE:
                $message = "<span style=\"color: blue;\">{$value}</span>";
                break;
            case Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING:
                $message = "<span style=\"color: orange;\">{$value}</span>";
                break;
            case Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR:
            default:
                $message = "<span style=\"color: red;\">{$value}</span>";
                break;
        }

        return $message;
    }

    public function callbackColumnInitiator($value, $row, $column, $isExport)
    {
        $initiator = $row->getData('initiator');

        switch ($initiator) {
            case Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION:
                $message = "<span style=\"text-decoration: underline;\">{$value}</span>";
                break;
            case Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN:
                $message = "<span style=\"font-style: italic; color: gray;\">{$value}</span>";
                break;
            case Ess_M2ePro_Helper_Data::INITIATOR_USER:
            default:
                $message = "<span>{$value}</span>";
                break;
        }

        return $message;
    }

    public function callbackColumnChannelOrderId($value, $row, $column, $isExport)
    {
        $mode = $row->getData('component_mode');
        $order = Mage::helper('M2ePro/Component')->getComponentModel($mode, 'Order')->load($row->getData('order_id'));

        if (is_null($order->getId())) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        switch ($mode) {
            case Ess_M2ePro_Helper_Component_Ebay::NICK:
                $channelOrderId = $order->getData('ebay_order_id');
                $url = $this->getUrl('*/adminhtml_ebay_order/view', array('id' => $row->getData('order_id')));
                break;
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $channelOrderId = $order->getData('amazon_order_id');
                $url = $this->getUrl('*/adminhtml_common_amazon_order/view', array('id' => $row->getData('order_id')));
                break;
            case Ess_M2ePro_Helper_Component_Buy::NICK:
                $channelOrderId = $order->getData('buy_order_id');
                $url = $this->getUrl('*/adminhtml_common_buy_order/view', array('id' => $row->getData('order_id')));
                break;
            default:
                $channelOrderId = Mage::helper('M2ePro')->__('N/A');
                $url = '#';
        }

        return '<a href="'.$url.'" target="_blank">'.Mage::helper('M2ePro')->escapeHtml($channelOrderId).'</a>';
    }

    public function callbackColumnMagentoOrderNumber($value, $row, $column, $isExport)
    {
        $magentoOrderId = $row->getData('magento_order_id');
        $magentoOrderNumber = $row->getData('magento_order_number');

        if (!$magentoOrderId) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $url = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $magentoOrderId));

        return '<a href="'.$url.'" target="_blank">'.Mage::helper('M2ePro')->escapeHtml($magentoOrderNumber).'</a>';
    }

    public function callbackFilterChannelOrderId($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $ordersIds = array();

        if (Mage::helper('M2ePro/Component_Ebay')->isActive()) {
            $tempOrdersIds = Mage::getModel('M2ePro/Ebay_Order')
                ->getCollection()
                ->addFieldToFilter('ebay_order_id', array('like' => '%'.$value.'%'))
                ->getColumnValues('order_id');
            $ordersIds = array_merge($ordersIds, $tempOrdersIds);
        }

        if (Mage::helper('M2ePro/Component_Amazon')->isActive()) {
            $tempOrdersIds = Mage::getModel('M2ePro/Amazon_Order')
                ->getCollection()
                ->addFieldToFilter('amazon_order_id', array('like' => '%'.$value.'%'))
                ->getColumnValues('order_id');
            $ordersIds = array_merge($ordersIds, $tempOrdersIds);
        }

        if (Mage::helper('M2ePro/Component_Buy')->isActive()) {
            $tempOrdersIds = Mage::getModel('M2ePro/Buy_Order')
                ->getCollection()
                ->addFieldToFilter('buy_order_id', array('like' => '%'.$value.'%'))
                ->getColumnValues('order_id');
            $ordersIds = array_merge($ordersIds, $tempOrdersIds);
        }

        $ordersIds = array_unique($ordersIds);

        $collection->addFieldToFilter('main_table.order_id', array('in' => $ordersIds));
    }

    //########################################

    public function getRowUrl($row)
    {
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/orderGrid', array(
            '_current' => true,
            'channel' => $this->getRequest()->getParam('channel')
        ));
    }

    //########################################
}