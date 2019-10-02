<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_PickupStore_Step_Stores_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->_listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $this->setId('ebayListingProductPickupStoreGrid');

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------

        $this->isAjax = Mage::helper('M2ePro')->jsonEncode($this->getRequest()->isXmlHttpRequest());
    }

    //########################################

    protected function _prepareCollection()
    {
        $pickupStoreCollection = Mage::getModel('M2ePro/Ebay_Account_PickupStore')->getCollection();
        $pickupStoreCollection->addFieldToFilter('account_id', $this->_listing->getAccountId());
        $pickupStoreCollection->addFieldToFilter('marketplace_id', $this->_listing->getMarketplaceId());

        // Set collection to grid
        $this->setCollection($pickupStoreCollection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'name', array(
            'header'    => Mage::helper('M2ePro')->__('Name / Location ID'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'name',
            'frame_callback' => array($this, 'callbackColumnTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $this->addColumn(
            'location_id', array(
            'header'    => Mage::helper('M2ePro')->__('Address'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'location_id',
            'width'     => '350px',
            'sortable'  => false,
            'escape'    => true,
            'frame_callback' => array($this, 'callbackColumnLocationId'),
            'filter_condition_callback' => array($this, 'callbackFilterLocation')
            )
        );

        $this->addColumn(
            'phone', array(
            'header'    => Mage::helper('M2ePro')->__('Details'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'phone',
            'width'     => '250px',
            'sortable'  => false,
            'escape'    => true,
            'frame_callback' => array($this, 'callbackColumnDetails'),
            'filter_condition_callback' => array($this, 'callbackFilterDetails')
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        // Set mass-action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem(
            'fake', array(
            'label' => '&nbsp;&nbsp;&nbsp;&nbsp;',
            'url'   => '#',
            )
        );
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $name = $row->getData('name');
        $locationId = $row->getData('location_id');

        $locationIdLabel = Mage::helper('M2ePro')->__('Location ID');

        return "<div>{$name} <br/>
                    <strong>{$locationIdLabel}</strong>:&nbsp;{$locationId} <br/>
                </div>";
    }

    public function callbackColumnLocationId($value, $row, $column, $isExport)
    {
        $countryCode = $row->getData('country');
        $countries = Mage::helper('M2ePro/Magento')->getCountries();

        $realCountry = $countryCode;
        foreach ($countries as $country) {
            if ($country['country_id'] == $countryCode) {
                $realCountry = $country['name'];
                break;
            }
        }

        $region = $row->getData('region');
        $city = $row->getData('city');
        $addressOne = $row->getData('address_1');
        $addressTwo = $row->getData('address_2');

        $addressHtml = "{$realCountry}, {$region}, {$city}, {$addressOne}";
        if (!empty($addressTwo)) {
            $addressHtml .= '<br/>' . $addressTwo;
        }

        $addressHtml .= ', ' .$row->getData('postal_code');

        return "<div>{$addressHtml}</div>";
    }

    public function callbackColumnDetails($value, $row, $column, $isExport)
    {
        $phone = $row->getData('phone');
        $url = $row->getData('url');

        if (!empty($url)) {
            $urlPath = strpos($url, 'http') === 0 ? $url : 'http://'.$url;
            $url = "<a href='{$urlPath}' target='_blank'>{$url}</a>";
        }

        $phoneLabel = Mage::helper('M2ePro')->__('Phone');
        return "<div><strong>{$phoneLabel}</strong>:&nbsp{$phone} <br/>{$url}</div>";
    }

    // ---------------------------------------

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            "main_table.name LIKE '%{$value}%'
            OR main_table.location_id LIKE '%{$value}%'"
        );
    }

    protected function callbackFilterLocation($collection, $column)
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
            "country LIKE '%{$countryCode}%'
            OR region LIKE '%{$value}%'
            OR city LIKE '%{$value}%'
            OR address_1 LIKE '%{$value}%'
            OR address_2 LIKE '%{$value}%'
            OR postal_code LIKE '%{$value}%'"
        );
    }

    protected function callbackFilterDetails($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            "phone LIKE '%{$value}%'
            OR url LIKE '%{$value}%'"
        );
    }

    //########################################

    protected function _toHtml()
    {
        $massActionFormId = $this->getId().'_massaction-form';
        $style = "<style> #{$massActionFormId} { display: none; } </style>";
        $javaScriptsMain = <<<HTML
        <script type="text/javascript">

            EbayListingPickupStoreStepStoresGridHandlerObj = new EbayListingPickupStoreStepStoresGridHandler();
            EbayListingPickupStoreStepStoresGridHandlerObj.gridId = '{$this->getId()}';

            ProductGridHandlerObj = new ListingProductGridHandler();
            ProductGridHandlerObj.setGridId('{$this->getJsObjectName()}');

            var init = function () {
                {$this->getJsObjectName()}.doFilter = ProductGridHandlerObj.setFilter;
                {$this->getJsObjectName()}.resetFilter = ProductGridHandlerObj.resetFilter;
            };

            {$this->isAjax} ? init()
                            : Event.observe(window, 'load', init);

        </script>
HTML;

        return parent::_toHtml() . $style . $javaScriptsMain;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/*/storesStepGrid', array(
            'id' => $this->_listing->getId()
            )
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################
}
