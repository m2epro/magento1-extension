<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Item_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_listingId  = null;
    protected $_motorsType = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $identifier = Mage::helper('M2ePro/Component_Ebay_Motors')->getIdentifierKey($this->getMotorsType());
        $this->setId('ebayMotor'.$identifier.'Grid');
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
        $this->_listingId = $marketplaceId;
        return $this;
    }

    public function getListingId()
    {
        return $this->_listingId;
    }

    //----------------------------------------

    public function setMotorsType($motorsType)
    {
        $this->_motorsType = $motorsType;
        return $this;
    }

    public function getMotorsType()
    {
        return $this->_motorsType;
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
        $this->getMassactionBlock()->addItem(
            'select', array(
            'label'   => Mage::helper('M2ePro')->__('Select'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        $this->getMassactionBlock()->addItem(
            'setNote', array(
            'label'   => Mage::helper('M2ePro')->__('Set Note'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        $this->getMassactionBlock()->addItem(
            'resetNote', array(
            'label'   => Mage::helper('M2ePro')->__('Reset Note'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        $this->getMassactionBlock()->addItem(
            'saveAsGroup', array(
            'label'   => Mage::helper('M2ePro')->__('Save As Group'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );
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

        return parent::_prepareLayout();
    }

    //########################################

    public function getSaveFilterButtonHtml()
    {
        return $this->getChildHtml('save_filter');
    }

    //########################################

    public function getMainButtonsHtml()
    {
        return $this->getSaveFilterButtonHtml() .
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

        $urls = Mage::helper('M2ePro')->jsonEncode(
            array(
            'adminhtml_ebay_motor/getItemsCountAlertPopupContent' => $this->getUrl(
                '*/adminhtml_ebay_motor/getItemsCountAlertPopupContent'
            )
            )
        );

        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
            'Attention' => Mage::helper('M2ePro')->__('Attention')
            )
        );

        $constants = Mage::helper('M2ePro')->getClassConstantAsJson('Ess_M2ePro_Helper_Component_Ebay_Motors');

        $additionalHtml .= <<<JS
EbayMotorAddItemGridHandlerObj.afterInitPage();

M2ePro.url.add({$urls});
M2ePro.translator.add({$translations});

M2ePro.php.setConstants(
    {$constants},
    'Ess_M2ePro_Helper_Component_Ebay_Motors'
);
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
             You can %link_new_item_start%add Custom Compatible Vehicles%link_new_item_end% manually
             or through the %link_start%Import Tool%link_end%.',
            '<a target="_blank" href="javascript::void(0)" onclick="EbayMotorsHandlerObj.openAddRecordPopup()">',
            '</a>',
            '<a target="_blank" href="' .
                $this->getUrl('*/adminhtml_ebay_configuration/index') .
                '#magento_block_ebay_configuration_general_motors_epids">',
            '</a>'
        );
    }

    //########################################

    public function getItemTitle()
    {
        if (Mage::helper('M2ePro/Component_Ebay_Motors')->isTypeBasedOnEpids($this->getMotorsType())) {
            return Mage::helper('M2ePro')->__('ePID(s)');
        }

        return Mage::helper('M2ePro')->__('kType(s)');
    }

    //########################################
}
