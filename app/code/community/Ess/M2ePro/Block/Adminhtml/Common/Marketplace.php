<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Marketplace extends Ess_M2ePro_Block_Adminhtml_Common_Component_Tabs_Container
{
    const TAB_ID_RAKUTEN = 'rakuten';

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('marketplace');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_marketplace';
        // ---------------------------------------

        // Form id of marketplace_general_form
        // ---------------------------------------
        $this->tabsContainerId = 'edit_form';
        // ---------------------------------------

        $this->_headerText = '';

        $this->setTemplate(NULL);

        // ---------------------------------------
        $this->addButton('run_update_all', array(
            'label' => Mage::helper('M2ePro')->__('Update All Now'),
            'onclick' => 'MarketplaceHandlerObj.updateAction()',
            'class' => 'save update_all_marketplace'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('run_synch_now', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'MarketplaceHandlerObj.saveAction();',
            'class'     => 'save save_and_update_marketplaces'
        ));
        // ---------------------------------------
    }

    protected function initializeTabs()
    {
        $this->initializeAmazon();
        $this->initializeRakuten();
    }

    protected function initializeRakuten()
    {
        if (Mage::helper('M2ePro/Component')->isRakutenActive()) {
            $this->initializeTab(self::TAB_ID_RAKUTEN);
        }
    }

    //########################################

    public function setEnabledTab($id)
    {
        if ($id == self::TAB_ID_BUY) {
            $id = self::TAB_ID_RAKUTEN;
        }
        parent::setEnabledTab($id);
    }

    protected function getActiveTab()
    {
        $activeTab = $this->getRequest()->getParam('tab');
        if (is_null($activeTab)) {
            Mage::helper('M2ePro/View_Common_Component')->isAmazonDefault()  && $activeTab = self::TAB_ID_AMAZON;
            Mage::helper('M2ePro/View_Common_Component')->isRakutenDefault() && $activeTab = self::TAB_ID_RAKUTEN;
        }

        return $activeTab;
    }

    //########################################

    protected function getAmazonTabBlock()
    {
        if (!$this->getChild('amazon_tab')) {
            $this->setChild(
                'amazon_tab',
                $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_marketplace_form')
            );
        }
        return $this->getChild('amazon_tab');
    }

    protected function getBuyTabBlock()
    {
        return null;
    }

    protected function getRakutenTabBlock()
    {
        if (!$this->getChild('rakuten_tab')) {
            $this->setChild(
                'rakuten_tab',
                $this->getLayout()->createBlock('M2ePro/adminhtml_common_rakuten_marketplace_form','')
            );
        }
        return $this->getChild('rakuten_tab');
    }

    protected function getRakutenTabHtml()
    {
        return $this->getRakutenTabBlock()->toHtml();
    }

    //########################################

    protected function getTabLabelById($id)
    {
        if ($id == self::TAB_ID_RAKUTEN) {
            return Mage::helper('M2ePro')->__('Rakuten (Beta)');
        }

        return parent::getTabLabelById($id);
    }

    protected function _toHtml()
    {
        return '<div id="marketplaces_progress_bar"></div>' .
               '<div id="marketplaces_content_container">' .
               parent::_toHtml() .
               '</div>';
    }

    protected function _componentsToHtml()
    {
        $tabsCount = count($this->tabs);

        if ($tabsCount <= 0) {
            return '';
        }

        $formBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_marketplace_general_form');
        count($this->tabs) == 1 && $formBlock->setChildBlockId($this->getSingleBlock()->getContainerId());

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

        return <<<HTML
<div class="content-header skip-header">
    <table cellspacing="0">
        <tr>
            <td{$hideChannels}>{$tabsContainer->toHtml()}</td>
            <td class="form-buttons">{$this->getButtonsHtml()}</td>
        </tr>
    </table>
</div>
{$formBlock->toHtml()}
HTML;

    }

    protected function getTabsContainerDestinationHtml()
    {
        return '';
    }

    //########################################

    protected function getTabsContainerBlock()
    {
        if (is_null($this->tabsContainerBlock)) {
            $this->tabsContainerBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_marketplace_tabs');
        }

        return $this->tabsContainerBlock;
    }

    //########################################
}