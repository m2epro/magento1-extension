<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Widget_FloatingToolbarFixer extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $_template = 'M2ePro/widget/floating_toolbar_fixer.phtml';

    //########################################

    public function displayTabButtonsInToolbar()
    {
        if (!isset($this->_data['display_tab_buttons'])) {
            return true;
        }

        return (bool)$this->_data['display_tab_buttons'];
    }

    //########################################
}