<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Template_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    const TAB_ID_AMAZON = 'amazon';
    const TAB_ID_BUY    = 'buy';

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/common/component/tabs/linktabs.phtml');
        $this->setId('commonTemplateTabs');
        $this->setDestElementId('template_tabs_container');
    }

    //########################################

    protected function _prepareLayout()
    {
        if (Mage::helper('M2ePro/Component_Amazon')->isActive()) {
            $this->addTab(self::TAB_ID_AMAZON, $this->getAmazonTabBlock());
        }
        if (Mage::helper('M2ePro/Component_Buy')->isActive()) {
            $this->addTab(self::TAB_ID_BUY, $this->getBuyTabBlock());
        }

        $this->setActiveTab($this->getActiveChannelTab());

        return parent::_prepareLayout();
    }

    //########################################

    protected function getAmazonTabBlock()
    {
        $title = Mage::helper('M2ePro/Component_Amazon')->getTitle();

        $tab = array(
            'label' => $title,
            'title' => $title
        );

        if ($this->getActiveChannelTab() == self::TAB_ID_AMAZON) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_template_grid')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_common_template/index', array(
                'channel' => self::TAB_ID_AMAZON
            ));
        }

        return $tab;
    }

    //########################################

    protected function getBuyTabBlock()
    {
        $title = Mage::helper('M2ePro/Component_Buy')->getTitle();

        $tab = array(
            'label' => $title,
            'title' => $title
        );

        if ($this->getActiveChannelTab() == self::TAB_ID_BUY) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_template_grid')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_common_template/index', array(
                'channel' => self::TAB_ID_BUY
            ));
        }

        return $tab;
    }

    //########################################

    protected function getActiveChannelTab()
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