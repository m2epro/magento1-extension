<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationNewAmazon_Installation_DescriptionTemplates
    extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardInstallationDescriptionTemplates');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/migrationNewAmazon/installation/descriptionTemplates.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        /** @var Ess_M2ePro_Model_Wizard_MigrationNewAmazon $wizardModel */
        $wizardModel = Mage::getSingleton('M2ePro/Wizard_MigrationNewAmazon');
        $descriptionTemplatesData = $wizardModel->getDataForDescriptionTemplatesStep();
        $grid = $this->createMagentoGridFromArray($descriptionTemplatesData);
        $this->setData('description_templates_grid', $grid->toHtml());
        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                    'label'   => Mage::helper('M2ePro')->__('Confirm'),
                    'onclick' => 'WizardHandlerObj.skipStep(\'descriptionTemplates\');',
                    'class' => 'process_template_description_button'
                ) );
        $this->setChild('process_description_templates_button',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################

    protected function createMagentoGridFromArray(array $data) {
        $collection = new Varien_Data_Collection();

        foreach ($data as $key => $value) {
            $tempObj = new Varien_Object();
            $tempObj->addData($value);
            $collection->addItem($tempObj);
        }

        /** @var $grid Mage_Adminhtml_Block_Widget_Grid */
        $grid = Mage::getBlockSingleton('adminhtml/Widget_Grid');
        $grid->setCollection($collection);
        $grid->addColumn('title', array(
            'header'   => Mage::helper('M2ePro')->__('Title'),
            'type'     => 'title',
            'width'    => '350px',
            'index'    => 'title',
            'filter'   => false,
            'sortable' => false
        ));
        $grid->addColumn('marketplace_title', array(
            'header'   => Mage::helper('M2ePro')->__('Marketplaces'),
            'type'     => 'text',
            'width'    => '200px',
            'index'    => 'marketplace_title',
            'filter'   => false,
            'sortable' => false
        ));
        $grid->addColumn('category_path', array(
            'header'   => Mage::helper('M2ePro')->__('Category'),
            'type'     => 'text',
            'index'    => 'category_path',
            'filter'   => false,
            'sortable' => false
        ));

        $grid->setFilterVisibility(false);
        $grid->setPagerVisibility(false);

        return $grid;
    }

    // ########################################
}