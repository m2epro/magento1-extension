<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Log extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayLog');

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Ebay')->getTitle();
            $this->_headerText = Mage::helper('M2ePro')->__('%component_name% / Logs & Events', $componentName);
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Logs & Events');
        }

        $this->removeButton('back');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->setTemplate('M2ePro/ebay/log.phtml');
    }

    protected function _toHtml()
    {
        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
            'Description' => Mage::helper('M2ePro')->__('Description')
            )
        );

        $javascript = <<<JAVASCIRPT

<script type="text/javascript">

    M2ePro.translator.add({$translations});

    Event.observe(window, 'load', function() {
        CommonObj = new Common();
        LogObj = new Log();
    });

</script>

JAVASCIRPT;

        $activeTab = $this->getData('active_tab') !== null ? $this->getData('active_tab')
            : Ess_M2ePro_Block_Adminhtml_Ebay_Log_Tabs::TAB_ID_LISTING;
        $tabsBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_log_tabs', '', array('active_tab' => $activeTab)
        );

        return $javascript .
        parent::_toHtml() .
        $tabsBlock->toHtml() .
        '<div id="tabs_container"></div>';
    }

    //########################################
}
