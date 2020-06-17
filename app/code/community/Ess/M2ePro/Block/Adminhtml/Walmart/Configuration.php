<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Configuration extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartConfiguration');
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Walmart')->getTitle();
            $this->_headerText = Mage::helper('M2ePro')->__('%component_name% / Configuration', $componentName);
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Configuration');
        }

        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        // ---------------------------------------
        $this->setTemplate('M2ePro/walmart/configuration.phtml');
        // ---------------------------------------
    }

    protected function _toHtml()
    {
        $javascript = <<<JAVASCIRPT

<script type="text/javascript">

    Event.observe(window, 'load', function() {
        CommonObj = new Common();
    });

</script>

JAVASCIRPT;

        $activeTab = $this->getData('active_tab') !== null ? $this->getData('active_tab')
            : Ess_M2ePro_Block_Adminhtml_Walmart_Configuration_Tabs::TAB_ID_MARKETPLACE;
        $tabsBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_configuration_tabs', '', array('active_tab' => $activeTab)
        );

        return $javascript .
               parent::_toHtml() .
               $tabsBlock->toHtml() .
               '<div id="tabs_container"></div>';
    }

    //########################################
}
