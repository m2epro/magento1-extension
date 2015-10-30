<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Log extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayLog');
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Logs');
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        // ---------------------------------------
        $this->setTemplate('M2ePro/ebay/log.phtml');
        // ---------------------------------------
    }

    protected function _toHtml()
    {
        $translations = json_encode(array(
            'Description' => Mage::helper('M2ePro')->__('Description')
        ));

        $javascript = <<<JAVASCIRPT

<script type="text/javascript">

    M2ePro.translator.add({$translations});

    Event.observe(window, 'load', function() {
        CommonHandlerObj = new CommonHandler();
        LogHandlerObj = new LogHandler();
    });

</script>

JAVASCIRPT;

        $activeTab = !is_null($this->getData('active_tab')) ? $this->getData('active_tab')
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