<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToV6_Installation_SellingFormatCurrencies_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    // ########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setId('migrationSellingFormatCurrenciesTabs');
        $this->setDestElementId('tabs_container');
    }

    // ########################################

    protected function _prepareLayout()
    {
        $tabsBlockPath = 'M2ePro/adminhtml_wizard_migrationToV6_installation_sellingFormatCurrencies_tabs_';

        $migrationModel = Mage::getSingleton('M2ePro/Wizard_MigrationToV6');
        $components = Mage::helper('M2ePro/Component')->getComponents();
        $componentTitles = Mage::helper('M2ePro/Component')->getComponentsTitles();

        $isFirst = true;
        foreach ($components as $component) {
            $migrationData = $migrationModel->getCurrencyPreparedData($component);
            if (empty($migrationData)) {
                continue;
            }

            $this->addTab($component, array(
                'label' => Mage::helper('M2ePro')->__($componentTitles[$component]),
                'title' => Mage::helper('M2ePro')->__($componentTitles[$component]),
                'content' => $this->getLayout()->createBlock(
                    $tabsBlockPath . $component, '', array('migration_data' => $migrationData)
                )->toHtml(),
            ));

            if ($isFirst) {
                $this->setActiveTab($component);
                $isFirst = false;
            }
        }

        return parent::_prepareLayout();
    }

    protected function _toHtml()
    {
        return parent::_toHtml() . '<form id="currencies_form"><div id="tabs_container"></div></form>';
    }

    // ########################################
}