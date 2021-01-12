<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonOrder');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_order';

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Amazon')->getTitle();
            $this->_headerText = Mage::helper('M2ePro')->__('%component_name% / Orders', $componentName);
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Orders');
        }

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton(
            'upload_by_user', array(
                'label'     => Mage::helper('M2ePro')->__('Order Reimport'),
                'onclick'   => 'UploadByUserObj.openPopup()',
                'class'     => 'button_link'
            )
        );

        $url = $this->getUrl('*/adminhtml_amazon_account/index');
        $this->_addButton(
            'accounts', array(
                'label'     => Mage::helper('M2ePro')->__('Accounts'),
                'onclick'   => 'setLocation(\'' . $url .'\')',
                'class'     => 'button_link'
            )
        );

        $url = $this->getUrl('*/adminhtml_amazon_log/order');
        $this->_addButton(
            'logs', array(
                'label'     => Mage::helper('M2ePro')->__('Logs & Events'),
                'onclick'   => 'window.open(\'' . $url .'\')',
                'class'     => 'button_link'
            )
        );
    }

    //########################################

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getLayout()->getBlock('head')->addJs('M2ePro/Order/UploadByUser.js');

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addControllerActions('adminhtml_order_uploadByUser');

        Mage::helper('M2ePro/View')->getJsTranslatorRenderer()->addTranslations(
            array(
                'Order Reimport',
                'Order importing in progress.',
                'Order importing is canceled.'
            )
        );

        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(<<<JS
UploadByUserObj = new UploadByUser('amazon', 'orderUploadByUserPopupGrid');
JS
        );

        return $this;
    }

    public function getGridHtml()
    {
        $marketplaceFilterBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_marketplace_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'controller_name' => 'adminhtml_amazon_order'
            )
        );
        $marketplaceFilterBlock->setUseConfirm(false);

        $accountFilterBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_account_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'controller_name' => 'adminhtml_amazon_order'
            )
        );
        $accountFilterBlock->setUseConfirm(false);

        $orderStateSwitcherBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_order_notCreatedFilter',
            '',
            array(
                'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK,
                'controller' => 'adminhtml_amazon_order'
            )
        );

        $invoiceCreditmemoFilterBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_order_grid_invoiceCreditmemoFilter',
            '',
            array(
                'controller' => 'adminhtml_amazon_order'
            )
        );

        $tempGridIds = array();
        Mage::helper('M2ePro/Component_Amazon')->isEnabled() && $tempGridIds[] = $this->getChild('grid')->getId();

        $generalBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_order_general');
        $generalBlock->setGridIds($tempGridIds);

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_order_help');
        $javascript = $this->getHelpBlockJavascript();

        $editItemBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_order_item_edit');

        return $generalBlock->toHtml()
            . $helpBlock->toHtml()
            . $javascript
            . $editItemBlock->toHtml()
            . '<div class="filter_block">'
            . $accountFilterBlock->toHtml()
            . $marketplaceFilterBlock->toHtml()
            . $orderStateSwitcherBlock->toHtml()
            . $invoiceCreditmemoFilterBlock->toHtml()
            . '</div>'
            . parent::getGridHtml();
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
        OrderObj.initializeGrids();
    }, 50);
</script>
HTML;
    }

    //########################################
}
