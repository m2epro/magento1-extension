<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Marketplace_Switcher extends Ess_M2ePro_Block_Adminhtml_Component_Switcher
{
    protected $paramName = 'marketplace';

    //########################################

    public function getLabel()
    {
        if ($this->getData('component_mode') == Ess_M2ePro_Helper_Component_Ebay::NICK) {
            return Mage::helper('M2ePro')->__('eBay Site');
        }

        return Mage::helper('M2ePro')->__($this->getComponentLabel('%component% Marketplace'));
    }

    public function getItems()
    {
        $collection = Mage::getModel('M2ePro/Marketplace')->getCollection()
            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
            ->setOrder('component_mode', 'ASC')
            ->setOrder('sorder', 'ASC');

        if (!is_null($this->componentMode)) {
            $collection->addFieldToFilter('component_mode', $this->componentMode);
        }

        if ($collection->getSize() < 2) {
            return array();
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

        return $items;
    }

    //########################################

    public function getDefaultOptionName()
    {
        if ($this->getData('component_mode') == Ess_M2ePro_Helper_Component_Ebay::NICK) {
            return Mage::helper('M2ePro')->__('All Sites');
        }

        return Mage::helper('M2ePro')->__('All Marketplaces');
    }

    //########################################
}