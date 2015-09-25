<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Account_Grid extends Ess_M2ePro_Block_Adminhtml_Account_Grid
{
    // ####################################

    protected function _prepareCollection()
    {
        // Get collection of accounts
        $collection = Mage::getModel('M2ePro/Account')->getCollection();

        $collection->getSelect()
            ->joinLeft(array('aa'=>Mage::getResourceModel('M2ePro/Amazon_Account')->getMainTable()),
                '(`aa`.`account_id` = `main_table`.`id`)',
                array())
            ->joinLeft(array('m'=>Mage::getResourceModel('M2ePro/Marketplace')->getMainTable()),
                '(`m`.`id` = `aa`.`marketplace_id`)',
                array('marketplace_title'=>'title'));

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    // ####################################

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('M2ePro')->__('ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'id',
            'filter_index' => 'main_table.id'
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('M2ePro')->__('Title / Info'),
            'align'     => 'left',
            //'width'     => '200px',
            'type'      => 'text',
            'index'     => 'title',
            'escape'    => true,
            'filter_index' => 'main_table.title',
            'frame_callback' => array($this, 'callbackColumnTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ));

        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            $this->addColumn('component_mode', array(
                'header'         => Mage::helper('M2ePro')->__('Channel'),
                'align'          => 'left',
                'width'          => '120px',
                'type'           => 'options',
                'index'          => 'component_mode',
                'filter_index'   => 'main_table.component_mode',
                'sortable'       => false,
                'options'        => Mage::helper('M2ePro/View_Common_Component')->getActiveComponentsTitles()
            ));
        }

        return parent::_prepareColumns();
    }

    // ####################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        if ($row->isComponentModeAmazon()) {
            $account = Mage::helper('M2ePro')->__('Account');
            $marketplace = Mage::helper('M2ePro')->__('Marketplace');

            $marketplaceTitle = $row->getData('marketplace_title');
            $value = <<<HTML
<div>
    {$value}<br/>
    <span style="font-weight: bold">{$marketplace}</span>: <span style="color: #505050">{$marketplaceTitle}</span><br/>
</div>
HTML;
        }

        return $value;
    }

    // ####################################

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('main_table.title LIKE ? OR m.title LIKE ?', '%'. $value .'%');
    }

    // ####################################
}
