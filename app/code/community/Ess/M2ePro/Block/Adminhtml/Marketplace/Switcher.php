<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Marketplace_Switcher extends Ess_M2ePro_Block_Adminhtml_Component_Switcher
{
    protected $_paramName = 'marketplace';

    //########################################

    public function getLabel()
    {
        return Mage::helper('M2ePro')->__($this->getComponentLabel('%component% Marketplace'));
    }

    protected function loadItems()
    {
        $collection = Mage::getModel('M2ePro/Marketplace')->getCollection()
            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
            ->setOrder('component_mode', 'ASC')
            ->setOrder('sorder', 'ASC');

        if ($this->componentMode !== null) {
            $collection->addFieldToFilter('component_mode', $this->componentMode);
        }

        if (!$collection->getSize()) {
            $this->_items = array();
            return;
        }

        if ($collection->getSize() < 2) {
            $this->_hasDefaultOption = false;
            $this->setIsDisabled(true);
        }

        $componentTitles = Mage::helper('M2ePro/Component')->getComponentsTitles();

        $items = array();

        foreach ($collection as $marketplace) {
            /** @var $marketplace Ess_M2ePro_Model_Marketplace */

            if (!isset($items[$marketplace->getComponentMode()]['label'])) {
                $label = '';
                if (isset($componentTitles[$marketplace->getComponentMode()])) {
                    $label = $componentTitles[$marketplace->getComponentMode()];
                }

                $items[$marketplace->getComponentMode()]['label'] = $label;
            }

            $items[$marketplace->getComponentMode()]['value'][] = array(
                'value' => $marketplace->getId(),
                'label' => $marketplace->getTitle()
            );
        }

        $this->_items = $items;
    }

    //########################################

    public function getDefaultOptionName()
    {
        return Mage::helper('M2ePro')->__('All Marketplaces');
    }

    //########################################
}
