<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing extends Ess_M2ePro_Block_Adminhtml_Common_Component_Tabs_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Set header text
        // ---------------------------------------
        $this->_headerText = '';
        // ---------------------------------------

        $this->setTemplate(NULL);

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_log/listing'
        );
        $this->_addButton('view_log', array(
            'label'     => Mage::helper('M2ePro')->__('View Log'),
            'onclick'   => 'CommonListingObj.viewLogs(\'' . $url . '\')',
            'class'     => 'button_link'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl('*/adminhtml_common_listing_create/index', array(
            'step' => '1',
            'clear' => 'yes'
        ));
        $this->_addButton('add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Listing'),
            'onclick'   => 'CommonListingObj.createListing(\'' . $url . '\')',
            'class'     => 'add'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->tabsContainerId = 'listings_tabs_container';
        $this->useAjax = true;
        $this->tabsAjaxUrls = array(
            self::TAB_ID_AMAZON => $this->getUrl('*/adminhtml_common_amazon_listing/index'),
            self::TAB_ID_BUY    => $this->getUrl('*/adminhtml_common_buy_listing/index')
        );
        // ---------------------------------------
    }

    //########################################

    protected function getTabsContainerBlock()
    {
        return parent::getTabsContainerBlock()
            ->setTemplate('M2ePro/common/component/tabs/linktabs.phtml')->setId('listing');
    }

    //########################################

    protected function _toHtml()
    {
        $urls = json_encode(array(
            'adminhtml_common_listing/saveTitle' => Mage::helper('adminhtml')
                                                        ->getUrl('M2ePro/adminhtml_common_listing/saveTitle')
        ));

        $translations = json_encode(array(
            'Cancel' => Mage::helper('M2ePro')->__('Cancel'),
            'Save' => Mage::helper('M2ePro')->__('Save'),
            'Edit Listing Title' => Mage::helper('M2ePro')->__('Edit Listing Title'),
        ));

        $uniqueTitleTxt = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('The specified Title is already used for other Listing. Listing Title must be unique.'));

        $constants = Mage::helper('M2ePro')
            ->getClassConstantAsJson('Ess_M2ePro_Helper_Component_'.ucfirst($this->getActiveTab()));

        $ajax = (int)$this->getRequest()->isXmlHttpRequest();

        $javascripts = <<<HTML

<script type="text/javascript">

    var init = function () {
        M2ePro.url.add({$urls});
        M2ePro.translator.add({$translations});

        CommonListingObj = new CommonListing({$this->getTabsContainerBlock()->getJsObjectName()});

        M2ePro.text.title_not_unique_error = '{$uniqueTitleTxt}';

        M2ePro.php.setConstants(
            {$constants},
            'Ess_M2ePro_Helper_Component'
        );

        editListingTitle = function(el)
        {
            EditListingTitleObj.gridId = listingJsTabs.activeTab.id.replace('listing_', '') + 'ListingGrid';
            EditListingTitleObj.openPopup(el);
        }

        EditListingTitleObj = new ListingEditListingTitle();
    };

    {$ajax} ? init() : Event.observe(window, 'load', init);

</script>

HTML;

        return parent::_toHtml() . $javascripts;
    }

    //########################################

    protected function getAmazonTabBlock()
    {
        if (!$this->getChild('amazon_tab')) {
            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing');

            $this->setChild('amazon_tab', $block);
        }

        return $this->getChild('amazon_tab');
    }

    //########################################

    protected function getBuyTabBlock()
    {
        if (!$this->getChild('buy_tab')) {
            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_listing');

            $this->setChild('buy_tab', $block);
        }

        return $this->getChild('buy_tab');
    }

    //########################################

    protected function getTabHtmlById($id)
    {
        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            return parent::getTabHtmlById($id);
        }

        /** @var $singleBlock Mage_Core_Block_Abstract|Mage_Adminhtml_Block_Widget_Grid_Container */
        $singleBlock = $this->getSingleBlock();

        if (is_object($singleBlock) && $singleBlock instanceof Mage_Adminhtml_Block_Widget_Grid_Container) {
            return $singleBlock->getGridHtml();
        }

        return parent::getTabHtmlById($id);
    }

    protected function _componentsToHtml()
    {
        $tabsCount = count($this->tabs);

        if ($tabsCount <= 0) {
            return '';
        }

        $tabsContainer = $this->getTabsContainerBlock();
        $tabsContainer->setDestElementId($this->tabsContainerId);

        foreach ($this->tabs as $tabId) {
            $tab = $this->prepareTabById($tabId);
            $tabsContainer->addTab($tabId, $tab);
        }

        $tabsContainer->setActiveTab($this->getActiveTab());

        $hideChannels = '';
        $tabsIds = $tabsContainer->getTabsIds();

        if (count($tabsIds) <= 1) {
            $hideChannels = ' style="visibility: hidden"';
        }

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_help');

        return $helpBlock->toHtml() . <<<HTML
<div class="content-header skip-header">
    <table cellspacing="0">
        <tr>
            <td{$hideChannels}>{$tabsContainer->toHtml()}</td>
            <td class="form-buttons">{$this->getButtonsHtml()}</td>
        </tr>
    </table>
</div>
<div id="listings_tabs_container"></div>
HTML;

    }

    //########################################

    protected function getActiveTab()
    {
        $activeTab = $this->getRequest()->getParam('channel');
        if (is_null($activeTab)) {
            Mage::helper('M2ePro/View_Common_Component')->isAmazonDefault() && $activeTab = self::TAB_ID_AMAZON;
            Mage::helper('M2ePro/View_Common_Component')->isBuyDefault()    && $activeTab = self::TAB_ID_BUY;
        }

        return $activeTab;
    }

    //########################################
}