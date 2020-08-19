<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Account_Grid extends Ess_M2ePro_Block_Adminhtml_Account_Grid
{
    //########################################

    protected function _prepareCollection()
    {
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Account');

        $collection->getSelect()->joinInner(
            array('m' => Mage::getResourceModel('M2ePro/Marketplace')->getMainTable()),
            '(`m`.`id` = `second_table`.`marketplace_id`)',
            array('marketplace_title' => 'title')
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    //########################################

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id', array(
                'header'    => Mage::helper('M2ePro')->__('ID'),
                'align'     => 'right',
                'width'     => '100px',
                'type'      => 'number',
                'index'     => 'id',
                'filter_index' => 'main_table.id'
            )
        );

        $this->addColumn(
            'title', array(
                'header'    => Mage::helper('M2ePro')->__('Title / Info'),
                'align'     => 'left',
                'type'      => 'text',
                'index'     => 'title',
                'escape'    => true,
                'filter_index' => 'main_table.title',
                'frame_callback' => array($this, 'callbackColumnTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Model_Account $row */
        $helper = Mage::helper('M2ePro');

        $consumerId = $row->getData('client_id');
        empty($consumerId) && $consumerId = $row->getData('consumer_id');

        return <<<HTML
<div>
    {$value}<br/>
    <span style="font-weight: bold">{$helper->__('Consumer ID')}</span>:
    <span style="color: #505050">{$consumerId}</span>
    <br/>
    <span style="font-weight: bold">{$helper->__('Marketplace')}</span>:
    <span style="color: #505050">{$row->getData('marketplace_title')}</span>
    <br/>
</div>
HTML;
    }

    //########################################

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            'main_table.title LIKE ? OR m.title LIKE ? OR consumer_id LIKE ? OR client_id LIKE ?',
            '%'. $value .'%'
        );
    }

    //########################################
}
