<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    const CHANNEL_ID_ALL        = 'all';
    const CHANNEL_ID_AMAZON     = 'amazon';
    const CHANNEL_ID_BUY        = 'buy';

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
        $this->setId('commonLogTabs');
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
            'label' => Mage::helper('M2ePro')->__('Listings'),
            'title' => Mage::helper('M2ePro')->__('Listings')
        );

        if ($this->getData('active_tab') == self::TAB_ID_LISTING) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_log_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_log')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_common_log/listing', array('_current' => true));
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
                                   ->createBlock('M2ePro/adminhtml_common_listing_other_log_help')->toHtml();
            $tab['content'] .= $this->getLayout()
                                   ->createBlock('M2ePro/adminhtml_common_listing_other_log')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_common_log/listingOther', array('_current' => true));
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
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_common_order_log_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_common_order_log')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_common_log/order', array('_current' => true));
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
                ->createBlock('M2ePro/adminhtml_common_synchronization_log_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_common_synchronization_log')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_common_log/synchronization', array('_current' => true));
        }

        return $tab;
    }

    //########################################

    protected function isListingOtherTabShouldBeShown()
    {
        $chanel = $this->getRequest()->getParam('channel');

        if (is_null($chanel)) {
            return true;
        }

        $helper = Mage::helper('M2ePro/View_Common');

        if ($chanel == self::CHANNEL_ID_AMAZON &&
            $helper->is3rdPartyShouldBeShown(Ess_M2ePro_Helper_Component_Amazon::NICK)) {
            return true;
        }

        if ($chanel == self::CHANNEL_ID_BUY &&
            $helper->is3rdPartyShouldBeShown(Ess_M2ePro_Helper_Component_Buy::NICK)) {
            return true;
        }

        if ($chanel == self::CHANNEL_ID_ALL &&
            ($helper->is3rdPartyShouldBeShown(Ess_M2ePro_Helper_Component_Amazon::NICK) ||
             $helper->is3rdPartyShouldBeShown(Ess_M2ePro_Helper_Component_Buy::NICK))) {
            return true;
        }

        return false;
    }

    //########################################

    protected function _toHtml()
    {
        $translations = json_encode(array(
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