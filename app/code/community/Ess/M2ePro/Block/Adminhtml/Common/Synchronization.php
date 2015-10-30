<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Synchronization
    extends Ess_M2ePro_Block_Adminhtml_Common_Component_Tabs_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('synchronization');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_synchronization';
        // ---------------------------------------

        // Form id of marketplace_general_form
        // ---------------------------------------
        $this->tabsContainerId = 'edit_form';
        // ---------------------------------------

        $this->_headerText = '';

        $this->setTemplate(NULL);

        // ---------------------------------------
        $params = Mage::helper('M2ePro')->escapeHtml(
            json_encode(Mage::helper('M2ePro/View_Common_Component')->getActiveComponents())
        );
        $this->_addButton('run_all_enabled_now', array(
            'label'     => Mage::helper('M2ePro')->__('Synchronize'),
            'onclick'   => 'SynchronizationHandlerObj.saveSettings(\'runAllEnabledNow\', ' . $params . ');',
            'class'     => 'save'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'SynchronizationHandlerObj.saveSettings(\'\', ' . $params . ')',
            'class'     => 'save'
        ));
        // ---------------------------------------
    }

    protected function _toHtml()
    {
        $javascriptsMain = <<<HTML
<script type="text/javascript">

    Event.observe(window, 'load', function() {
        SynchProgressBarObj = new ProgressBar('synchronization_progress_bar');
        SynchWrapperObj = new AreaWrapper('synchronization_content_container');
    });

</script>
HTML;

        return $javascriptsMain .
               '<div id="synchronization_progress_bar"></div>' .
               '<div id="synchronization_content_container">' .
               parent::_toHtml() .
               '</div>';
    }

    //########################################

    protected function getAmazonTabBlock()
    {
        if (!$this->getChild('amazon_tab')) {
            $this->setChild(
                'amazon_tab', $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_synchronization_form')
            );
        }
        return $this->getChild('amazon_tab');
    }

    //########################################

    protected function getBuyTabBlock()
    {
        if (!$this->getChild('buy_tab')) {
            $this->setChild(
                'buy_tab',
                $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_synchronization_form')
            );
        }
        return $this->getChild('buy_tab');
    }

    //########################################

    protected function _componentsToHtml()
    {
        $tabsCount = count($this->tabs);

        if ($tabsCount <= 0) {
            return '';
        }

        $formBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_synchronization_form');
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

    //########################################

    protected function getTabsContainerBlock()
    {
        if (is_null($this->tabsContainerBlock)) {
            $this->tabsContainerBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_synchronization_tabs');
        }

        return $this->tabsContainerBlock;
    }

    //########################################
}