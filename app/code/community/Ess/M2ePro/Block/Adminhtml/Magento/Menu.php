<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Menu extends Mage_Adminhtml_Block_Page_Menu
{
    //########################################

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

            $menuArray = Mage::helper('M2ePro/Component')->prepareMenu($menuArray);

        } catch (Exception $exception) {}

        return $menuArray;
    }

    //########################################
}