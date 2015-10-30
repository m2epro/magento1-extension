<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Order_View_ExternalTransaction extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var $order Ess_M2ePro_Model_Order */
    protected $order = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayOrderViewExternalTransaction');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        // ---------------------------------------

        $this->order = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
    }

    protected function _prepareCollection()
    {
        $collection = $this->order->getChildObject()->getExternalTransactionsCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('transaction_id', array(
            'header' => Mage::helper('M2ePro')->__('Transaction #'),
            'align' => 'left',
            'width' => '*',
            'index' => 'transaction_id',
            'sortable' => false,
            'frame_callback' => array($this, 'callbackColumnTransactionId')
        ));

        $this->addColumn('fee', array(
            'header' => Mage::helper('M2ePro')->__('Fee'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'fee',
            'type' => 'number',
            'sortable' => false,
            'frame_callback' => array($this, 'callbackColumnFee')
        ));

        $this->addColumn('sum', array(
            'header' => Mage::helper('M2ePro')->__('Amount'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'sum',
            'type' => 'number',
            'sortable' => false,
            'frame_callback' => array($this, 'callbackColumnSum')
        ));

        $this->addColumn('transaction_date', array(
            'header'   => Mage::helper('M2ePro')->__('Date'),
            'align'    => 'left',
            'width'    => '150px',
            'index'    => 'transaction_date',
            'type'     => 'datetime',
            'format'   => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'sortable' => false
        ));

        return parent::_prepareColumns();
    }

    public function callbackColumnTransactionId($value, $row, $column, $isExport)
    {
        if (strtolower($this->order->getChildObject()->getPaymentMethod()) != 'paypal') {
            return $value;
        }

        $url = $this->getUrl('*/*/goToPaypal', array('transaction_id' => $value));

        return '<a href="'.$url.'" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnFee($value, $row, $column, $isExport)
    {
        return Mage::getSingleton('M2ePro/Currency')->formatPrice(
            $this->order->getChildObject()->getCurrency(), $value
        );
    }

    public function callbackColumnSum($value, $row, $column, $isExport)
    {
        return Mage::getSingleton('M2ePro/Currency')->formatPrice(
            $this->order->getChildObject()->getCurrency(), $value
        );
    }

    public function getRowUrl($row)
    {
        return '';
    }

    //########################################
}