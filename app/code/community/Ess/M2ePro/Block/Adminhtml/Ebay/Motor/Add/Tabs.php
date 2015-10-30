<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    protected $motorsType;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayMotorAddTabs');
        //------------------------------

        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setDestElementId('ebay_motor_add_tabs_container');
    }

    //------------------------------

    protected function _beforeToHtml()
    {
        //------------------------------
        $motorsType = $this->getMotorsType();
        $motorsType = Mage::helper('M2ePro/Component_Ebay_Motors')->getIdentifierKey($motorsType);

        $itemsGrid = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_motor_add_item_'.$motorsType.'_grid');
        $itemsGrid->setMotorsType($this->getMotorsType());
        $title = $this->getItemsTabTitle();

        $this->addTab('items', array(
            'label'   => Mage::helper('M2ePro')->__($title),
            'title'   => Mage::helper('M2ePro')->__('Child Products'),
            'content' => $itemsGrid->toHtml()
        ));
        //------------------------------

        //------------------------------
        $filtersGrid = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_motor_add_filter_grid');
        $filtersGrid->setMotorsType($this->getMotorsType());

        $this->addTab('filters', array(
            'label'   => Mage::helper('M2ePro')->__('Filters'),
            'title'   => Mage::helper('M2ePro')->__('Filters'),
            'content' => $filtersGrid->toHtml()
        ));
        //------------------------------

        //------------------------------
        $groupsGrid = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_motor_add_group_grid');
        $groupsGrid->setMotorsType($this->getMotorsType());

        $this->addTab('groups', array(
            'label'   => Mage::helper('M2ePro')->__('Groups'),
            'title'   => Mage::helper('M2ePro')->__('Groups'),
            'content' => $groupsGrid->toHtml()
        ));
        //------------------------------

        $this->setActiveTab('items');

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        $additionalJavascript = <<<HTML
<script type="text/javascript">
    {$this->getJsObjectName()}.moveTabContentInDest();

    EbayMotorsHandlerObj.saveAsGroupPopupHtml = $('save_as_group_popup').innerHTML;
    $('save_as_group_popup').remove();
    EbayMotorsHandlerObj.setNotePopupHtml = $('set_note_popup').innerHTML;
    $('set_note_popup').remove();

</script>
HTML;

        $saveAsGroupPopup = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_add_saveAsGroupPopup');
        $setNotePopup = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_add_setNotePopup');

        return parent::_toHtml() .
            '<div id="ebay_motor_add_tabs_container"></div>' .
            $saveAsGroupPopup->toHtml() .
            $setNotePopup->toHtml() .
            $additionalJavascript;
    }

    //########################################

    public function setMotorsType($motorsType)
    {
        $this->motorsType = $motorsType;
    }

    public function getMotorsType()
    {
        if (is_null($this->motorsType)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Motors type not set.');
        }

        return $this->motorsType;
    }

    //########################################

    public function getItemsTabTitle()
    {
        if ($this->getMotorsType() == Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID) {
            return Mage::helper('M2ePro')->__('ePID(s)');
        }

        return Mage::helper('M2ePro')->__('kType(s)');
    }

    //########################################
}