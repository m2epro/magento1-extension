<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Item_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $listingId = null;

    //########################################

    abstract public function getMotorsType();

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $motorsType = Mage::helper('M2ePro/Component_Ebay_Motors')->getIdentifierKey($this->getMotorsType());
        $this->setId('ebayMotor'.$motorsType.'Grid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('make');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        //------------------------------
    }

    //########################################

    public function setListingId($marketplaceId)
    {
        $this->listingId = $marketplaceId;
        return $this;
    }

    public function getListingId()
    {
        return $this->listingId;
    }

    //########################################

    protected function _prepareMassaction()
    {
        $typeIdentifier = Mage::helper('M2ePro/Component_Ebay_Motors')->getIdentifierKey(
            $this->getMotorsType()
        );

        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('main_table.'.$typeIdentifier);
        $this->getMassactionBlock()->setFormFieldName($typeIdentifier);
        //--------------------------------

        // Set mass-action
        //--------------------------------
        $this->getMassactionBlock()->addItem('select', array(
            'label'   => Mage::helper('M2ePro')->__('Select'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('setNote', array(
            'label'   => Mage::helper('M2ePro')->__('Set Note'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('resetNote', array(
            'label'   => Mage::helper('M2ePro')->__('Reset Note'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('saveAsGroup', array(
            'label'   => Mage::helper('M2ePro')->__('Save As Group'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    public function getMassactionBlockName()
    {
        // this is required for correct work of massaction js
        return 'M2ePro/adminhtml_grid_massaction';
    }

    //########################################

    public function callbackColumnIdentifier($value, $row, $column, $isExport)
    {
        $type = $this->getMotorsType();

        $idKey = Mage::helper('M2ePro/Component_Ebay_Motors')->getIdentifierKey($type);

        $removeTitle = Mage::helper('M2ePro')->__('Remove this record.');

        $removeCustomRecordHtml = '';
        if (isset($row['is_custom']) && $row['is_custom']) {
            $removeCustomRecordHtml = <<<HTML
<a href="javascript:void(0);"
   class="remove-custom-created-record-link"
   onclick="EbayMotorsHandlerObj.removeCustomMotorsRecord('{$type}', '{$row[$idKey]}');"
   align="center" title="{$removeTitle}"></a>
HTML;
        }

        $noteWord = Mage::helper('M2ePro')->__('Note');

        return <<<HTML

{$value}{$removeCustomRecordHtml}
<br/>
<br/>
<div id="note_{$row[$idKey]}" style="color: gray; display: none;">
    <span style="text-decoration: underline">{$noteWord}</span>: <br/>
    <span class="note-view"></span>
</div>

HTML;
    }

    public function callbackNullableColumn($value, $row, $column, $isExport)
    {
        return $value ? $value : '--';
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/adminhtml_ebay_motor/addItemGrid', array(
                '_current' => true,
                'listing_id' => $this->getListingId()
            )
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function _prepareLayout()
    {
        //------------------------------
        $data = array(
            'id'      => 'save_filter_btn',
            'label'   => Mage::helper('M2ePro')->__('Save Filter'),
            'class'   => 'success',
            'onclick' => 'EbayMotorAddItemGridHandlerObj.saveFilter()'
        );
        $saveFilterBtn = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('save_filter', $saveFilterBtn);
        //------------------------------

        //------------------------------
        $data = array(
            'id'      => 'add_custom_motors_record_button',
            'label'   => Mage::helper('M2ePro')->__('Add New '. $this->getItemTitle()),
            'class'   => 'success',
            'style'   => 'display: none;',
            'onclick' => 'EbayMotorsHandlerObj.openAddRecordPopup()'
        );
        $addCustomMotorsRecordBtn = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('add_custom_motors_record_button', $addCustomMotorsRecordBtn);
        //------------------------------

        return parent::_prepareLayout();
    }

    //########################################

    public function getSaveFilterButtonHtml()
    {
        return $this->getChildHtml('save_filter');
    }

    public function getAddCustomMotorsRecordButtonHtml()
    {
        return $this->getChildHtml('add_custom_motors_record_button');
    }

    //########################################

    public function getMainButtonsHtml()
    {
        return $this->getAddCustomMotorsRecordButtonHtml() .
            $this->getSaveFilterButtonHtml() .
            parent::getMainButtonsHtml();
    }

    //########################################

    protected function _toHtml()
    {
        $additionalHtml = <<<HTML
<style type="text/css">
    #{$this->getId()} table td, #{$this->getId()} table th {
        padding: 5px;
    }

    a.remove-custom-created-record-link {
        display: inline-block;
        width: 8px;
        height: 9px;
        margin-left: 3px;
        background-position: center;
        background-repeat: no-repeat;
        background-image: url("{$this->getSkinUrl('M2ePro/images/delete.png')}");
    }
</style>
HTML;

        $additionalHtml .= '<script type="text/javascript">';

        if ($this->canDisplayContainer()) {
            $additionalHtml .= <<<JS
EbayMotorAddItemGridHandlerObj = new EbayMotorAddItemGridHandler('{$this->getId()}');

$('save_filter_btn').addClassName('disabled');

JS;
        }

        $additionalHtml .= <<<JS
EbayMotorAddItemGridHandlerObj.afterInitPage();
JS;

        $additionalHtml .= '</script>';

        $filterPopup = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_add_item_filterPopup');

        return '<div style="height: 410px; overflow: auto;">' .
            parent::_toHtml()
            . '</div>' .
            $filterPopup->toHtml() .
            $additionalHtml;
    }

    public function getEmptyText()
    {
        return Mage::helper('M2ePro')->__(
            'No records found.
             You can add Custom Compatible Vehicles manually or through the %link_start%Import Tool%link_end%.',
            '<a target="_blank" href="' .
                $this->getUrl('*/adminhtml_ebay_configuration/index') .
                '#magento_block_ebay_configuration_general_motors_epids">',
            '</a>'
        );
    }

    //########################################

    public function getItemTitle()
    {
        if ($this->getMotorsType() == Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID) {
            return Mage::helper('M2ePro')->__('ePID(s)');
        }

        return Mage::helper('M2ePro')->__('kType(s)');
    }

    //########################################
}