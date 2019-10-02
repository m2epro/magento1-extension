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
        $collection = Mage::getModel('M2ePro/Account')->getCollection();

        $collection->getSelect()
            ->joinLeft(
                array('aa'=>Mage::getResourceModel('M2ePro/Walmart_Account')->getMainTable()),
                '(`aa`.`account_id` = `main_table`.`id`)',
                array('consumer_id')
            )
            ->joinLeft(
                array('m'=>Mage::getResourceModel('M2ePro/Marketplace')->getMainTable()),
                '(`m`.`id` = `aa`.`marketplace_id`)',
                array('marketplace_title'=>'title')
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
        if ($row->isComponentModeWalmart()) {
            $marketplaceLabel = Mage::helper('M2ePro')->__('Marketplace');
            $marketplaceTitle = $row->getData('marketplace_title');

            $merchantLabel = Mage::helper('M2ePro')->__('Consumer ID');
            $consumerId = $row->getData('consumer_id');

            $value = <<<HTML
            <div>
                {$value}<br/>
                <span style="font-weight: bold">{$merchantLabel}</span>:
                <span style="color: #505050">{$consumerId}</span>
                <br/>
                <span style="font-weight: bold">{$marketplaceLabel}</span>:
                <span style="color: #505050">{$marketplaceTitle}</span>
                <br/>
            </div>
HTML;
        } else {
            $sellerId = $row->getData('seller_id');

            if (empty($sellerId)) {
                return $value;
            }

            $sellerLabel = Mage::helper('M2ePro')->__('Seller ID');

            $value = <<<HTML
            <div>
                {$value}<br/>
                <span style="font-weight: bold">{$sellerLabel}</span>:
                <span style="color: #505050">{$sellerId}</span>
                <br/>
            </div>
HTML;
        }

        return $value;
    }

    //########################################

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            'main_table.title LIKE ?
            OR m.title LIKE ? 
            OR aa.consumer_id LIKE ?',
            '%'. $value .'%'
        );
    }

    //########################################
}
