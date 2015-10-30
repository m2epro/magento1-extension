<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Order extends Ess_M2ePro_Block_Adminhtml_Common_Component_Tabs_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Set header text
        // ---------------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Orders');
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl('*/adminhtml_common_account/index');
        $this->_addButton('accounts', array(
            'label'     => Mage::helper('M2ePro')->__('Accounts'),
            'onclick'   => 'setLocation(\'' . $url .'\')',
            'class'     => 'button_link'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl('*/adminhtml_common_log/order');
        $this->_addButton('logs', array(
            'label'     => Mage::helper('M2ePro')->__('View Logs'),
            'onclick'   => 'window.open(\'' . $url .'\')',
            'class'     => 'button_link'
        ));
        // ---------------------------------------

        $this->useAjax = true;
        $this->tabsAjaxUrls = array(
            self::TAB_ID_AMAZON => $this->getUrl('*/adminhtml_common_amazon_order/index'),
            self::TAB_ID_BUY    => $this->getUrl('*/adminhtml_common_buy_order/index')
        );
    }

    //########################################

    protected function getHelpBlockJavascript()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return '';
        }

        return <<<HTML
<script type="text/javascript">
    setTimeout(function() {
        OrderHandlerObj.initializeGrids();
    }, 50);
</script>
HTML;
    }

    //########################################

    protected function getAmazonTabBlock()
    {
        if (!$this->getChild('amazon_tab')) {
            $this->setChild('amazon_tab', $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_order_grid'));
        }
        return $this->getChild('amazon_tab');
    }

    public function getAmazonTabHtml()
    {
        return $this->getAmazonTabBlockFilterHtml()
               . parent::getAmazonTabHtml();
    }

    private function getAmazonTabBlockFilterHtml()
    {
        $marketplaceFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_marketplace_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'controller_name' => 'adminhtml_common_order'
        ));
        $marketplaceFilterBlock->setUseConfirm(false);

        $accountFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_account_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'controller_name' => 'adminhtml_common_order'
        ));
        $accountFilterBlock->setUseConfirm(false);

        $orderStateSwitcherBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_order_notCreatedFilter',
            '',
            array(
                'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK,
                'controller' => 'adminhtml_common_order'
            )
        );

        return '<div class="filter_block">'
            . $marketplaceFilterBlock->toHtml()
            . $accountFilterBlock->toHtml()
            . $orderStateSwitcherBlock->toHtml()
            . '</div>';
    }

    //########################################

    protected function getBuyTabBlock()
    {
        if (!$this->getChild('buy_tab')) {
            $this->setChild('buy_tab', $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_order_grid'));
        }
        return $this->getChild('buy_tab');
    }

    public function getBuyTabHtml()
    {
        return $this->getBuyTabBlockFilterHtml()
               . parent::getBuyTabHtml();
    }

    private function getBuyTabBlockFilterHtml()
    {
        $accountFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_account_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Buy::NICK,
            'controller_name' => 'adminhtml_common_order'
        ));
        $accountFilterBlock->setUseConfirm(false);

        $orderStateSwitcherBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_order_notCreatedFilter',
            '',
            array(
                'component_mode' => Ess_M2ePro_Helper_Component_Buy::NICK,
                'controller' => 'adminhtml_common_order'
            )
        );

        return '<div class="filter_block">'
            . $accountFilterBlock->toHtml()
            . $orderStateSwitcherBlock->toHtml()
            . '</div>';
    }

    //########################################

    protected function _componentsToHtml()
    {
        $tempGridIds = array();
        Mage::helper('M2ePro/Component_Amazon')->isActive() && $tempGridIds[] = $this->getAmazonTabBlock()->getId();
        Mage::helper('M2ePro/Component_Buy')->isActive()    && $tempGridIds[] = $this->getBuyTabBlock()->getId();

        $generalBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_order_general');
        $generalBlock->setGridIds($tempGridIds);

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_order_help');
        $javascript = $this->getHelpBlockJavascript();

        $editItemBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_order_item_edit');

        return $generalBlock->toHtml()
               . $helpBlock->toHtml()
               . $javascript
               . $editItemBlock->toHtml()
               . parent::_componentsToHtml();
    }

    //########################################
}