<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Template_NewProduct_Search_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('buyTemplateNewProductSearchGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    protected function _prepareCollection()
    {
        $data = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $results = new Varien_Data_Collection();
        foreach ($data as $index => $item) {
            $temp = array(
                'id'        => $item['category_id'],
                'title'     => $item['title'],
                'path'      => $item['path'],
                'node_id' => $item['node_id'],
                'native_id' => $item['native_id']
            );

            $results->addItem(new Varien_Object($temp));
        }

        $this->setCollection($results);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('title', array(
            'header'       => Mage::helper('M2ePro')->__('Category'),
            'align'        => 'left',
            'type'         => 'text',
            'index'        => 'title',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnTitle')
        ));

        $this->addColumn('actions', array(
            'header'       => Mage::helper('M2ePro')->__('Action'),
            'align'        => 'center',
            'type'         => 'text',
            'width'        => '80px',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnActions'),
        ));

    }

    //########################################

    public function callbackColumnTitle($title, $row, $column, $isExport)
    {
        $categoryInfo = json_encode($row->getData());
        $categoryInfo = Mage::helper('M2ePro')->escapeHtml($categoryInfo);
        $categoryInfo = Mage::helper('M2ePro')->escapeJs($categoryInfo);

        $path    = $row->getData('path');
        (strlen($path) != strlen($title)) && $path = substr($path,0,strlen($path) - strlen($title)-2);
        $path    = str_replace('->',' > ',$path);

        $title   = Mage::helper('M2ePro')->escapeHtml($title);
        if (strlen($title) > 60) {
            $title = substr($title, 0, 60) . '...';
        }
        $foundIn = Mage::helper('M2ePro')->__('Found In: ');

        $fullPath = $path;
        if (strlen($path) > 135) {
            $path = substr($path, 0, 135) . '...';
        }

        $html = <<<HTML
<div style="margin-left: 3px">
    <a href="javascript:;" onclick="BuyTemplateNewProductHandlerObj.confirmSearchClick($categoryInfo)">$title</a>
    <br/>
    <span style="font-weight: bold;">$foundIn</span>
    &nbsp;
    <span title="$fullPath">$path</span><br/>
</div>
HTML;

        return $html;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $categoryInfo = json_encode($row->getData());
        $categoryInfo = Mage::helper('M2ePro')->escapeHtml($categoryInfo);
        $categoryInfo = Mage::helper('M2ePro')->escapeJs($categoryInfo);

        $select = Mage::helper('M2ePro')->__('Select');
        $html = <<<HTML
<a href="javascript:;" onclick="BuyTemplateNewProductHandlerObj.confirmSearchClick($categoryInfo)">$select</a>
HTML;

        return $html;
    }

    //########################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<HTML
<script type="text/javascript">

    $$('#buyTemplateNewProductSearchGrid div.grid th').each(function(el) {
        el.style.padding = '2px 2px';
    });

    $$('#buyTemplateNewProductSearchGrid div.grid td').each(function(el) {
        el.style.padding = '2px 2px';
    });

    $$('#buyTemplateNewProductSearchGrid div.grid table').each(function(el) {
        el.style.width = '99.9%';
    });

</script>
HTML;

        return parent::_toHtml() . $javascriptsMain;
    }

    //########################################

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################
}