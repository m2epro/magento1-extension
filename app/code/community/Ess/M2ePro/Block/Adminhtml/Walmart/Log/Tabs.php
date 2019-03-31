<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Log_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    // ---------------------------------------

    const TAB_ID_LISTING            = 'listing';
    const TAB_ID_LISTING_OTHER      = 'listing_other';
    const TAB_ID_ORDER              = 'order';
    const TAB_ID_SYNCHRONIZATION    = 'synchronization';

    //########################################

    protected $logType;

    /**
     * @param string $logType
     */
    public function setLogType($logType)
    {
        $this->logType = $logType;
    }

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setId('walmartLogTabs');
        $this->setDestElementId('tabs_container');
    }

    //########################################

    protected function _prepareLayout()
    {
        if (!$this->isListingOtherTabShouldBeShown() && $this->getData('active_tab') == self::TAB_ID_LISTING_OTHER) {
            $this->setData('active_tab', self::TAB_ID_LISTING);
        }

        $this->addTab(self::TAB_ID_LISTING, $this->prepareTabListing());

        if ($this->isListingOtherTabShouldBeShown()) {
            $this->addTab(self::TAB_ID_LISTING_OTHER, $this->prepareTabListingOther());
        }

        $this->addTab(self::TAB_ID_ORDER, $this->prepareTabOrder());
        $this->addTab(self::TAB_ID_SYNCHRONIZATION, $this->prepareTabSynchronization());

        $this->setActiveTab($this->getData('active_tab'));

        return parent::_prepareLayout();
    }

    //########################################

    protected function prepareTabListing()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('M2E Pro Listings'),
            'title' => Mage::helper('M2ePro')->__('M2E Pro Listings')
        );

        if ($this->getData('active_tab') == self::TAB_ID_LISTING) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_log_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_log_grid')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_walmart_log/listing', array('_current' => true));
        }

        return $tab;
    }

    protected function prepareTabListingOther()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('3rd Party Listings'),
            'title' => Mage::helper('M2ePro')->__('3rd Party Listings')
        );

        if ($this->getData('active_tab') == self::TAB_ID_LISTING_OTHER) {
            $tab['content'] = $this->getLayout()
                                   ->createBlock('M2ePro/adminhtml_walmart_listing_other_log_help')->toHtml();
            $tab['content'] .= $this->getLayout()
                                   ->createBlock('M2ePro/adminhtml_walmart_listing_other_log_grid')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_walmart_log/listingOther', array('_current' => true));
        }

        return $tab;
    }

    protected function prepareTabOrder()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Orders'),
            'title' => Mage::helper('M2ePro')->__('Orders')
        );

        if ($this->getData('active_tab') == self::TAB_ID_ORDER) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_order_log_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_order_log')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_walmart_log/order', array('_current' => true));
        }

        return $tab;
    }

    protected function prepareTabSynchronization()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Synchronization'),
            'title' => Mage::helper('M2ePro')->__('Synchronization')
        );

        if ($this->getData('active_tab') == self::TAB_ID_SYNCHRONIZATION) {

            $tab['content'] = $this->getLayout()
                ->createBlock('M2ePro/adminhtml_walmart_synchronization_log_help')->toHtml();
            $tab['content'] .= $this->getLayout()
                ->createBlock('M2ePro/adminhtml_walmart_synchronization_log')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_walmart_log/synchronization', array('_current' => true));
        }

        return $tab;
    }

    //########################################

    protected function isListingOtherTabShouldBeShown()
    {
        $helper = Mage::helper('M2ePro/View_Walmart');

        return $helper->is3rdPartyShouldBeShown(Ess_M2ePro_Helper_Component_Walmart::NICK);
    }

    //########################################

    protected function _toHtml()
    {
        $translations = Mage::helper('M2ePro')->jsonEncode(array(
            'Description' => Mage::helper('M2ePro')->__('Description')
        ));

        $javascript = <<<JAVASCIRPT

<script type="text/javascript">

    M2ePro.translator.add({$translations});

    Event.observe(window, 'load', function() {
        LogHandlerObj = new LogHandler();
    });

</script>

JAVASCIRPT;

        return $javascript . parent::_toHtml() . '<div id="tabs_container"></div>';
    }

    //########################################
}