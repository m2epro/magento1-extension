<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Synchronization extends Mage_Adminhtml_Block_Widget_Form_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebaySynchronization');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_synchronization';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = '';
        //------------------------------

        $this->removeButton('save');
        $this->removeButton('reset');
        $this->removeButton('back');

        $this->_addButton('run_all_enabled_now', array(
            'label'     => Mage::helper('M2ePro')->__('Synchronize'),
            'onclick'   => 'SynchronizationHandlerObj.saveSettings(\'runAllEnabledNow\');',
            'class'     => 'save'
        ));

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'SynchronizationHandlerObj.saveSettings(\'\')',
            'class'     => 'save'
        ));
    }

    // ########################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    Event.observe(window, 'load', function() {
        SynchProgressBarObj = new ProgressBar('synchronization_progress_bar');
        SynchWrapperObj = new AreaWrapper('synchronization_content_container');
    });

</script>
JAVASCRIPT;

        return $javascriptsMain .
        '<div id="synchronization_progress_bar"></div>' .
        '<div id="synchronization_content_container">' .
        parent::_toHtml() .
        '</div>';
    }

    // ########################################
}