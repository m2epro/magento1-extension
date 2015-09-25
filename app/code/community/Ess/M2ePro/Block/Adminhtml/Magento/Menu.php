<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Menu extends Mage_Adminhtml_Block_Page_Menu
{
    //#############################################

    public function getModuleName()
    {
        if (Mage::getStoreConfig('advanced/modules_disable_output/Ess_M2ePro')) {
            return 'Mage_Adminhtml';
        }
        return parent::getModuleName();
    }

    public function getMenuArray()
    {
        $menuArray = parent::getMenuArray();

        try {

            $menuArray = Mage::helper('M2ePro/View_Ebay')->prepareMenu($menuArray);
            $menuArray = Mage::helper('M2ePro/View_Common')->prepareMenu($menuArray);

        } catch (Exception $exception) {}

        return $menuArray;
    }

    //#############################################
}