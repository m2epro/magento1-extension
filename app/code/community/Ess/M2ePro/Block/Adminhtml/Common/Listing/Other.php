<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Other extends Ess_M2ePro_Block_Adminhtml_Common_Component_Tabs_Container
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

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if (!is_null($this->getRequest()->getParam('back'))) {
            // ---------------------------------------
            $url = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_common_listing/index');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$url.'\')',
                'class'     => 'back'
            ));
            // ---------------------------------------
        }

        // ---------------------------------------
        $url = $this->getUrl('*/adminhtml_common_log/listingOther');
        $this->_addButton('view_log', array(
            'label'     => Mage::helper('M2ePro')->__('View Log'),
            'onclick'   => 'viewAllChannelLogs(\''.$url.'\')',
            'class'     => 'button_link'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->tabsContainerId = 'listings_other_tabs_container';
        $this->useAjax = true;
        $this->tabsAjaxUrls = array(
            self::TAB_ID_AMAZON => $this->getUrl('*/adminhtml_common_amazon_listing_other/index'),
            self::TAB_ID_BUY    => $this->getUrl('*/adminhtml_common_buy_listing_other/index')
        );

        $this->isAjax = json_encode($this->getRequest()->isXmlHttpRequest());
        // ---------------------------------------
    }

    //########################################

    protected function getAmazonTabBlock()
    {
        if (!$this->getChild('amazon_tab')) {
            $this->setChild(
                'amazon_tab', $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_other_grid')
            );
        }
        return $this->getChild('amazon_tab');
    }

    //########################################

    protected function getBuyTabBlock()
    {
        if (!$this->getChild('buy_tab')) {
            $this->setChild(
                'buy_tab',
                $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_listing_other_grid')
            );
        }
        return $this->getChild('buy_tab');
    }

    //########################################

    protected function getTabsContainerBlock()
    {
        return parent::getTabsContainerBlock()
            ->setTemplate('M2ePro/common/component/tabs/linktabs.phtml');
    }

    protected function _componentsToHtml()
    {
        $javascriptsMain = <<<HTML
<script type="text/javascript">

    viewAllChannelLogs = function(url) {
        var tabsComponent = {$this->getTabsContainerBlock()->getJsObjectName()},
            activeTabId = tabsComponent.activeTab.id,
            channel = activeTabId.replace(tabsComponent.containerId + '_', '');

        window.open(url + 'channel/' + channel);
    }

</script>
HTML;

        $tabsCount = count($this->tabs);

        if ($tabsCount <= 0) {
            return '';
        }

        $tabsContainer = $this->getTabsContainerBlock();
        $tabsContainer->setDestElementId($this->tabsContainerId);

        foreach ($this->tabs as $tabId) {

            if ($tabId == self::TAB_ID_AMAZON && !Mage::helper('M2ePro/View_Common')
                    ->is3rdPartyShouldBeShown(Ess_M2ePro_Helper_Component_Amazon::NICK)) {
                continue;
            }

            if ($tabId == self::TAB_ID_BUY &&
                !Mage::helper('M2ePro/View_Common')->is3rdPartyShouldBeShown(Ess_M2ePro_Helper_Component_Buy::NICK)) {
                continue;
            }

            $tab = $this->prepareTabById($tabId);
            $tabsContainer->addTab($tabId, $tab);
        }

        $tabsContainer->setActiveTab($this->getActiveTab());

        $hideChannels = '';
        $tabsIds = $tabsContainer->getTabsIds();

        if (count($tabsIds) <= 1) {
            $hideChannels = ' style="visibility: hidden"';
        }

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_other_help');

        return $javascriptsMain .
               $helpBlock->toHtml() . <<<HTML
<div class="content-header skip-header">
    <table cellspacing="0">
        <tr>
            <td{$hideChannels}>{$tabsContainer->toHtml()}</td>
            <td class="form-buttons">{$this->getButtonsHtml()}</td>
        </tr>
    </table>
</div>
<div id="listings_other_tabs_container"></div>
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