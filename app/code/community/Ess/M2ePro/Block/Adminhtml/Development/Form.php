<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Development_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('developmentGeneralForm');
        // ---------------------------------------

        // Set template
        // ---------------------------------------
        $this->setTemplate('M2ePro/development.phtml');
        // ---------------------------------------
    }

    //########################################

    protected function _beforeToHtml()
    {
        $tabsBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_development_tabs');
        $this->setChild('tabs_development', $tabsBlock);

        return parent::_beforeToHtml();
    }

    //########################################
}