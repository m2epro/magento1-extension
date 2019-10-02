<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Bids_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_bidsData;
    protected $_listingProductId;

    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingProductBidsGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        // ---------------------------------------
    }

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
     * @return mixed
     */
    public function getBidsData()
    {
        return $this->_bidsData;
    }

    /**
     * @param mixed $bidsData
     */
    public function setBidsData($bidsData)
    {
        $this->_bidsData = $bidsData;
    }

    //########################################

    protected function _prepareCollection()
    {
        $results = new Varien_Data_Collection();
        foreach ($this->getBidsData() as $index => $item) {
            $temp = array(
                'user_id' => $item['user']['user_id'],
                'email' => $item['user']['email'],
                'price' => $item['price'],
                'time' => $item['time']
            );

            $results->addItem(new Varien_Object($temp));
        }

        $this->setCollection($results);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'user_id', array(
            'header'       => Mage::helper('M2ePro')->__('eBay User ID'),
            'width'        => '180px',
            'align'        => 'center',
            'type'         => 'text',
            'index'        => 'user_id',
            'sortable'     => false
            )
        );

        $this->addColumn(
            'email', array(
            'header'       => Mage::helper('M2ePro')->__('eBay User Email'),
            'width'        => '180px',
            'align'        => 'center',
            'type'         => 'text',
            'index'        => 'email',
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnEmail')
            )
        );

        $this->addColumn(
            'price', array(
            'header'       => Mage::helper('catalog')->__('Price'),
            'width'        => '90px',
            'align'        => 'right',
            'index'        => 'price',
            'sortable'     => false,
            'type'         => 'number',
            'frame_callback' => array($this, 'callbackColumnPrice')
            )
        );

        $this->addColumn(
            'time', array(
            'header'       => Mage::helper('M2ePro')->__('Date'),
            'width'        => '180px',
            'align'        => 'right',
            'type'         => 'datetime',
            'index'        => 'time',
            'sortable'     => false,
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM)
            )
        );
    }

    //########################################

    public function callbackColumnEmail($value, $row, $column, $isExport)
    {
        if ($value == 'Invalid Request') {
            return '<span style="color: gray">' . Mage::helper('M2ePro')->__('Not Available') . '</span>';
        }

        return $value;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        $currency = $this->getListingProduct()->getMarketplace()->getChildObject()->getCurrency();
        $value = Mage::app()->getLocale()->currency($currency)->toCurrency($value);

        return '<div style="margin-right: 5px;">'.$value.'</div>';
    }

    //########################################

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function _toHtml()
    {
        $help = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_bids_help')->toHtml();

        $html = parent::_toHtml();

        $data = array(
            'style' => 'float: right; margin: 7px 0;',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'Windows.getFocusedWindow().close()'
        );
        $closeBtn = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);

        return <<<HTML
<div style="margin: 10px 0;">
{$help}
    <div style="height: 250px; overflow: auto;">
        {$html}
    </div>
    {$closeBtn->toHtml()}
</div>
HTML;

    }

    //########################################
}
