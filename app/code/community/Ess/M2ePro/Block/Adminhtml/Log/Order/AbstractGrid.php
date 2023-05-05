<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Log_Order_AbstractGrid extends Ess_M2ePro_Block_Adminhtml_Log_AbstractGrid
{
    //#######################################

    public function _construct()
    {
        parent::_construct();

        $this->setId(ucfirst($this->getComponentMode()) . 'OrderLogGrid');

        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        $this->_entityIdFieldName = self::ORDER_ID_FIELD;
        $this->_logModelName = 'Order_Log';
    }

    protected function _prepareCollection()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Order_Log_Collection */
        $collection = Mage::getModel('M2ePro/Order_Log')->getCollection();

        $isNeedCombine = $this->isNeedCombineMessages();

        if ($isNeedCombine) {
            $collection->getSelect()->columns(
                array('create_date' => new \Zend_Db_Expr('MAX(main_table.create_date)'))
            );
            $collection->getSelect()->group(array('main_table.order_id', 'main_table.description'));
        }

        $collection->getSelect()->joinLeft(
            array('mo' => Mage::getResourceModel('M2ePro/Order')->getMainTable()),
            '(mo.id = `main_table`.order_id)',
            array('magento_order_id')
        );

        if ($accountId = $this->getRequest()->getParam($this->getComponentMode() . 'Account')) {
            $collection->addFieldToFilter('main_table.account_id', $accountId);
        } else {
            $collection->getSelect()->joinLeft(
                array('account_table' => Mage::getResourceModel('M2ePro/Account')->getMainTable()),
                'account_table.id = main_table.account_id',
                array('real_account_id' => 'account_table.id')
            );

            $collection->addFieldToFilter('account_table.id', array('notnull' => true));
        }

        if ($marketplaceId = $this->getRequest()->getParam($this->getComponentMode() . 'Marketplace')) {
            $collection->addFieldToFilter('main_table.marketplace_id', $marketplaceId);
        } else {
            $collection->getSelect()->joinLeft(
                array('marketplace_table' => Mage::getResourceModel('M2ePro/Marketplace')->getMainTable()),
                'marketplace_table.id = main_table.marketplace_id',
                array('marketplace_status' => 'marketplace_table.status')
            );

            $collection->addFieldToFilter('marketplace_table.status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
        }

        $collection->getSelect()->joinLeft(
            array('so' => Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('sales/order')),
            '(so.entity_id = `mo`.magento_order_id)',
            array('magento_order_number' => 'increment_id')
        );

        if ($orderId = $this->getRequest()->getParam('order_id')) {
            $collection->addFieldToFilter('main_table.order_id', (int)$orderId);
        }

        $collection->addFieldToFilter('main_table.component_mode', $this->getComponentMode());

        $filter = $this->getParam($this->getVarNameFilter(), null);
        if (is_string($filter)) {
            $this->addOrderCreateDateToFilter($collection, $filter);
            $this->applyVatChangedFilters($collection, $filter);
        }

        $this->setCollection($collection);
        $result = parent::_prepareCollection();

        if ($isNeedCombine) {
            $this->prepareMessageCount($collection);
        }

        return $result;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'create_date',
            array(
                'header'       => Mage::helper('M2ePro')->__('Creation Date'),
                'align'        => 'left',
                'width'        => '165px',
                'type'         => 'datetime',
                'format'       => Mage::app()->getLocale()->getDateTimeFormat(
                    Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM
                ),
                'index'        => 'create_date',
                'filter_index' => 'main_table.create_date'
            )
        );

        $this->addColumn(
            'channel_order_id',
            array(
                'header'                    => Mage::helper('M2ePro')->__($this->getComponentTitle() . ' Order #'),
                'align'                     => 'left',
                'width'                     => '180px',
                'sortable'                  => false,
                'index'                     => 'channel_order_id',
                'frame_callback'            => array($this, 'callbackColumnChannelOrderId'),
                'filter_condition_callback' => array($this, 'callbackFilterChannelOrderId')
            )
        );

        $this->addColumn(
            'magento_order_number',
            array(
                'header'         => Mage::helper('M2ePro')->__('Magento Order #'),
                'align'          => 'left',
                'width'          => '150px',
                'index'          => 'so.increment_id',
                'sortable'       => false,
                'frame_callback' => array($this, 'callbackColumnMagentoOrderNumber'),
                'filter_condition_callback' => array($this, 'callbackFilterMagentoOrderNumber')
            )
        );

        $this->addColumn(
            'description',
            array(
                'header'         => Mage::helper('M2ePro')->__('Message'),
                'align'          => 'left',
                'width'          => '*',
                'index'          => 'description',
                'frame_callback' => array($this, 'callbackColumnDescription')
            )
        );

        $this->addColumn(
            'initiator',
            array(
                'header'         => Mage::helper('M2ePro')->__('Run Mode'),
                'align'          => 'left',
                'width'          => '65px',
                'index'          => 'initiator',
                'sortable'       => false,
                'type'           => 'options',
                'options'        => $this->_getLogInitiatorList(),
                'frame_callback' => array($this, 'callbackColumnInitiator')
            )
        );

        $this->addColumn(
            'type',
            array(
                'header'         => Mage::helper('M2ePro')->__('Type'),
                'align'          => 'left',
                'width'          => '65px',
                'index'          => 'type',
                'type'           => 'options',
                'sortable'       => false,
                'options'        => $this->_getLogTypeList(),
                'frame_callback' => array($this, 'callbackColumnType')
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnChannelOrderId($value, $row, $column, $isExport)
    {
        $mode = $row->getData('component_mode');
        $order = Mage::helper('M2ePro/Component')->getComponentModel($mode, 'Order')->load($row->getData('order_id'));
        if ($order->getId() === null) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        switch ($mode) {
            case Ess_M2ePro_Helper_Component_Ebay::NICK:
                $channelOrderId = $order->getData('ebay_order_id');
                $url = $this->getUrl('*/adminhtml_ebay_order/view', array('id' => $row->getOrderId()));
                break;
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $channelOrderId = $order->getData('amazon_order_id');
                $url = $this->getUrl('*/adminhtml_amazon_order/view', array('id' => $row->getOrderId()));
                break;
            case Ess_M2ePro_Helper_Component_Walmart::NICK:
                $channelOrderId = $order->getData('walmart_order_id');
                $url = $this->getUrl('*/adminhtml_walmart_order/view', array('id' => $row->getOrderId()));
                break;
            default:
                $channelOrderId = Mage::helper('M2ePro')->__('N/A');
                $url = '#';
        }

        return '<a href="' . $url . '" target="_blank">' . Mage::helper('M2ePro')->escapeHtml($channelOrderId) . '</a>';
    }

    public function callbackColumnMagentoOrderNumber($value, $row, $column, $isExport)
    {
        $magentoOrderId = $row->getData('magento_order_id');
        $magentoOrderNumber = $row->getData('magento_order_number');

        if (!$magentoOrderId) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $url = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $magentoOrderId));

        return '<a href="' . $url . '" target="_blank">' . Mage::helper('M2ePro')->escapeHtml(
                $magentoOrderNumber
            ) . '</a>';
    }

    public function callbackFilterChannelOrderId($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $ordersIds = array();

        if (Mage::helper('M2ePro/Component_Ebay')->isEnabled()) {
            $tempOrdersIds = Mage::getModel('M2ePro/Ebay_Order')
                ->getCollection()
                ->addFieldToFilter('ebay_order_id', array('like' => '%' . $value . '%'))
                ->getColumnValues('order_id');
            $ordersIds = array_merge($ordersIds, $tempOrdersIds);
        }

        if (Mage::helper('M2ePro/Component_Amazon')->isEnabled()) {
            $tempOrdersIds = Mage::getModel('M2ePro/Amazon_Order')
                ->getCollection()
                ->addFieldToFilter('amazon_order_id', array('like' => '%' . $value . '%'))
                ->getColumnValues('order_id');
            $ordersIds = array_merge($ordersIds, $tempOrdersIds);
        }

        if (Mage::helper('M2ePro/Component_Walmart')->isEnabled()) {
            $tempOrdersIds = Mage::getModel('M2ePro/Walmart_Order')
                ->getCollection()
                ->addFieldToFilter('walmart_order_id', array('like' => '%' . $value . '%'))
                ->getColumnValues('order_id');
            $ordersIds = array_merge($ordersIds, $tempOrdersIds);
        }

        $ordersIds = array_unique($ordersIds);

        $collection->addFieldToFilter('main_table.order_id', array('in' => $ordersIds));
    }

    public function callbackFilterMagentoOrderNumber($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();

        if (empty($cond)) {
            return;
        }

        if ($column->getFilter()->getValue() == 'N/A') {
            $collection->addFieldToFilter('mo.magento_order_id', array('null' => true));

            return;
        }

        $collection->addFieldToFilter('mo.magento_order_id', $cond);
    }

    //########################################

    protected function addOrderCreateDateToFilter($collection, $filter)
    {
        $filterData = $this->helper('adminhtml')->prepareFilterString($filter);
        if (array_key_exists('order_create_date', $filterData)) {
            $column = $this->getColumn('create_date');

            if (isset($column) && (!empty($filterData['order_create_date'])) && $column->getFilter()) {
                $filterData['order_create_date']['locale'] = Mage::app()->getLocale()->getLocaleCode();
                $column->getFilter()->setValue($filterData['order_create_date']);
                $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
                $cond = $column->getFilter()->getCondition();
                if ($field && isset($cond)) {
                    $date = new Zend_Date();
                    $date->set($filterData['order_create_date']['from_origin'], Zend_Date::ISO_8601);
                    $cond['from'] = $date;
                    $collection->addFieldToFilter($field, $cond);
                }
            }
        }
    }

    //########################################

    public function getRowUrl($row)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/orderGrid', array('_current' => true));
    }

    //########################################

    protected function getComponentTitle()
    {
        return Mage::helper('M2ePro/Component_' . ucfirst($this->getComponentMode()))->getTitle();
    }

    private function applyVatChangedFilters(Ess_M2ePro_Model_Resource_Order_Log_Collection $collection, $filter)
    {
        $filterData = $this->helper('adminhtml')->prepareFilterString($filter);
        if (empty($filterData['orders_with_modified_vat'])) {
            return;
        }

        $collection->addFieldToFilter(
            'main_table.additional_data',
            array('like' => '%' . \Ess_M2ePro_Model_Order::ADDITIONAL_DATA_KEY_VAT_REVERSE_CHARGE . '%')
        );
    }
}
