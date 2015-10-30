<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_SellingFormat_SearchCharity_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayCharityGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = new Varien_Data_Collection();

        foreach (array_slice($this->getData('Charities'), 0, 10) as $index) {
            $temp = array(
                'id' => $index['id'],
                'name' => $index['name'],
            );

            $collection->addItem(new Varien_Object($temp));
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'        => Mage::helper('M2ePro')->__('ID'),
            'width'         => '50px',
            'align'         => 'left',
            'type'          => 'text',
            'index'         => 'id',
            'escape'        => true,
            'sortable'      => false,
            'filter'        => false,
        ));

        $this->addColumn('name', array(
            'header'        => Mage::helper('M2ePro')->__('Name'),
            'align'         => 'left',
            'type'          => 'text',
            'index'         => 'name',
            'escape'        => true,
            'sortable'      => false,
            'filter'        => false,
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('M2ePro')->__('Action'),
            'align'     => 'left',
            'width'     => '50px',
            'type'      => 'text',
            'sortable'  => false,
            'filter'    => false,
            'actions'   => array(
                0 => array(
                    'label' => Mage::helper('M2ePro')->__('Select'),
                    'value' => 'selectNewCharity',
                )
            ),
            'frame_callback' => array($this, 'callbackColumnActions')
        ));

        return parent::_prepareColumns();
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $actions = $column->getActions();

        $id = $row->getData('id');
        $name = $row->getData('name');
        $name = Mage::helper('M2ePro')->escapeJs($name);

        $actions = reset($actions);

        $label = $actions['label'];
        $method = $actions['value'];
        $onclick = "EbayTemplateSellingFormatHandlerObj['{$method}']({$id}, '{$name}')";

        return <<<HTML
<div style="padding: 5px;">
        <a href="javascript:void(0)" onclick="{$onclick}">
        {$label}
        </a>
</div>
HTML;
    }

    public function getRowUrl($item)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/*', array('_current'=>true));
    }

    //########################################
}