<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Component_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('widget/tabshoriz.phtml');
    }

    //########################################

    public function addTabAfterAmazon($tabId, $tab)
    {
        return $this->addTabAfter($tabId, $tab, Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_AMAZON);
    }

    public function addTabAfterBuy($tabId, $tab)
    {
        return $this->addTabAfter($tabId, $tab, Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_BUY);
    }

    //########################################
}