<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Log_Listing_AbstractView extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /** @var  Ess_M2ePro_Block_Adminhtml_Log_Listing_View_ModeSwitcher */
    protected $_viewModeSwitcherBlock;

    /** @var  Ess_M2ePro_Block_Adminhtml_Account_Switcher  */
    protected $_accountSwitcherBlock;

    /** @var  Ess_M2ePro_Block_Adminhtml_Marketplace_Switcher  */
    protected $_marketplaceSwitcherBlock;

    //#######################################

    abstract protected function getComponentMode();

    abstract protected function getFiltersHtml();

    //#######################################

    public function __construct()
    {
        parent::__construct();

        $this->setId($this->getComponentMode() . 'ListingLog');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_' . $this->getComponentMode() . '_log_listing_view_' . $this->getViewMode();

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->setTemplate('M2ePro/log/listing.phtml');
    }


    protected function _prepareLayout()
    {
        $this->_viewModeSwitcherBlock    = $this->createViewModeSwitcherBlock();
        $this->_accountSwitcherBlock     = $this->createAccountSwitcherBlock();
        $this->_marketplaceSwitcherBlock = $this->createMarketplaceSwitcherBlock();

        return parent::_prepareLayout();
    }

    public function getViewMode()
    {
        if ($viewMode = $this->getRequest()->getParam('view_mode', false)) {
            Mage::helper('M2ePro/Module_Log')->setViewMode(
                $this->getComponentMode() . '_log_listing_view_mode',
                $viewMode
            );

            return $viewMode;
        }

        return Mage::helper('M2ePro/Module_Log')->getViewMode(
            $this->getComponentMode() . '_log_listing_view_mode'
        );
    }

    protected function createViewModeSwitcherBlock()
    {
        return $this->getLayout()->createBlock('M2ePro/adminhtml_log_listing_view_modeSwitcher');
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

    protected function getStaticFilterHtml($label, $value)
    {
        return <<<HTML
<p class="static-switcher">
    <span>{$label}:</span>
    <span>{$value}</span>
</p>
HTML;
    }

    //#######################################
}
