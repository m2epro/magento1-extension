<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Account_Switcher extends Ess_M2ePro_Block_Adminhtml_Component_Switcher
{
    protected $_paramName = 'account';

    //########################################

    public function getLabel()
    {
        return Mage::helper('M2ePro')->__($this->getComponentLabel('%component% Account'));
    }

    protected function loadItems()
    {
        $collection = Mage::getModel('M2ePro/Account')->getCollection()
                                                      ->setOrder('component_mode', 'ASC')
                                                      ->setOrder('title', 'ASC');

        if ($this->getData('component_mode') !== null) {
            $collection->addFieldToFilter('component_mode', $this->getData('component_mode'));
        }

        if (!$collection->getSize()) {
            $this->_items = array();
            return;
        }

        if ($collection->getSize() < 2) {
            $this->_hasDefaultOption = false;
            $this->setIsDisabled(true);
        }

        $items = array();

        foreach ($collection as $account) {
            /** @var $account Ess_M2ePro_Model_Account */

            $componentMode = $account->getComponentMode();

            $items[$componentMode]['label'] = ucfirst($componentMode);
            $items[$componentMode]['value'][] = array(
                'value' => $account->getId(),
                'label' => $account->getTitle()
            );
        }

        $this->_items = $items;
    }

    //########################################

    public function getDefaultOptionName()
    {
        if ($this->getData('component_mode') == Ess_M2ePro_Helper_Component_Ebay::NICK) {
            return Mage::helper('M2ePro')->__('All Users');
        }

        return Mage::helper('M2ePro')->__('All Accounts');
    }

    //########################################
}
