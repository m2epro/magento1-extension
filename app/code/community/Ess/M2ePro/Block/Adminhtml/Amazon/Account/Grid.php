<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Account_Grid extends Ess_M2ePro_Block_Adminhtml_Account_Grid
{
    //########################################

    protected function _prepareCollection()
    {
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');

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
            'id',
            array(
                'header'       => Mage::helper('M2ePro')->__('ID'),
                'align'        => 'right',
                'width'        => '100px',
                'type'         => 'number',
                'index'        => 'id',
                'filter_index' => 'main_table.id'
            )
        );

        $this->addColumn(
            'title',
            array(
                'header'                    => Mage::helper('M2ePro')->__('Title / Info'),
                'align'                     => 'left',
                'type'                      => 'text',
                'index'                     => 'title',
                'escape'                    => true,
                'filter_index'              => 'main_table.title',
                'frame_callback'            => array($this, 'callbackColumnTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Model_Account $row */
        $marketplaceLabel = Mage::helper('M2ePro')->__('Marketplace');
        $marketplaceTitle = $row->getData('marketplace_title');

        $merchantLabel = Mage::helper('M2ePro')->__('Merchant ID');
        $merchantId = $row->getData('merchant_id');

        return <<<HTML
<div>
    {$value}<br/>
    <span style="font-weight: bold">{$merchantLabel}</span>:
    <span style="color: #505050">{$merchantId}</span>
    <br/>
    <span style="font-weight: bold">{$marketplaceLabel}</span>:
    <span style="color: #505050">{$marketplaceTitle}</span>
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
            'main_table.title LIKE ? OR m.title LIKE ? OR second_table.merchant_id LIKE ?',
            '%'. $value .'%'
        );
    }

    //########################################
}
