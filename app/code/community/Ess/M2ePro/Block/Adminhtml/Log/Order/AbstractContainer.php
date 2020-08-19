<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Log_Order_AbstractContainer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //#######################################

    abstract protected function getComponentMode();

    //#######################################

    public function __construct()
    {
        parent::__construct();

        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_' . $this->getComponentMode() . '_log_order';
        $this->_headerText = '';

        $this->setId(ucfirst($this->getComponentMode()) . 'LogOrder');

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->setTemplate('M2ePro/log/order.phtml');
    }

    //########################################

    public function getFilterBlock()
    {
        $accountSwitcherBlock = $this->createAccountSwitcherBlock();
        $marketplaceSwitcherBlock = $this->createMarketplaceSwitcherBlock();

        $orderId = $this->getRequest()->getParam('order_id', false);

        if ($orderId) {
            /** @var Ess_M2ePro_Model_Order $order */
            $order = Mage::helper('M2ePro/Component')->getUnknownObject('Order', (int)$orderId);

            return
                '<div class="static-switcher-block">'
                . $this->getStaticFilterHtml(
                    Mage::helper('M2ePro')->__('Account'),
                    $order->getAccount()->getTitle()
                )
                . $this->getStaticFilterHtml(
                    Mage::helper('M2ePro')->__('Marketplace'),
                    $order->getMarketplace()->getTitle()
                )
                . '</div>';
        }

        if ($marketplaceSwitcherBlock->isEmpty() && $accountSwitcherBlock->isEmpty()) {
            return '';
        }

        return $accountSwitcherBlock->toHtml() . $marketplaceSwitcherBlock->toHtml();
    }

    protected function getStaticFilterHtml($label, $value)
    {
        return <<<HTML
<p class="static-switcher">
    <span>{$label}:</span>
    <span>{$value}</span>
</p>
HTML;
    }

    protected function createAccountSwitcherBlock()
    {
        return $this->getLayout()->createBlock(
            'M2ePro/adminhtml_account_switcher', '', array(
                'component_mode' => $this->getComponentMode()
            )
        );
    }

    protected function createMarketplaceSwitcherBlock()
    {
        return $this->getLayout()->createBlock(
            'M2ePro/adminhtml_marketplace_switcher', '', array(
                'component_mode' => $this->getComponentMode()
            )
        );
    }

    //########################################
}
