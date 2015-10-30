<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayConfiguration');
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Configuration');
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        // ---------------------------------------
        $this->setTemplate('M2ePro/ebay/configuration.phtml');
        // ---------------------------------------
    }

    protected function _toHtml()
    {
        $javascript = <<<JAVASCIRPT

<script type="text/javascript">

    Event.observe(window, 'load', function() {
        CommonHandlerObj = new CommonHandler();
    });

</script>

JAVASCIRPT;

        $activeTab = !is_null($this->getData('active_tab')) ? $this->getData('active_tab')
            : Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Tabs::TAB_ID_MARKETPLACE;
        $tabsBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_configuration_tabs', '', array('active_tab' => $activeTab)
        );

        return $javascript .
               parent::_toHtml() .
               $tabsBlock->toHtml() .
               '<div id="tabs_container"></div>';
    }

    //########################################
}