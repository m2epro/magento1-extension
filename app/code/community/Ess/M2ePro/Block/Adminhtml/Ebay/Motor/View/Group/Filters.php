<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_View_Group_Filters
    extends Mage_Adminhtml_Block_Template
{
    private $group;
    private $groupId;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayMotorViewGroupFiltersPopup');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/motor/view/group/filters.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $data = array(
            'class'   => 'close-btn',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'Windows.getFocusedWindow().close()'
        );
        $this->setChild(
            'close_btn',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data)
        );

        return $this;
    }

    //########################################

    /**
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param mixed $groupId
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Motor_Group
     */
    public function getGroup()
    {
        if (is_null($this->group)) {
            $this->group = Mage::getModel('M2ePro/Ebay_Motor_Group')->load($this->getGroupId());
        }

        return $this->group;
    }

    //########################################

    public function getFilters()
    {
        /** @var Ess_M2ePro_Model_Mysql4_Ebay_Motor_Filter_Collection $collection */
        $collection = Mage::getModel('M2ePro/Ebay_Motor_Filter')->getCollection();
        $collection->getSelect()->join(
            array('ftg' => Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_motor_filter_to_group')),
            'ftg.filter_id=main_table.id',
            array()
        );

        $collection->getSelect()->where('group_id = ?', (int)$this->getGroupId());

        return $collection->getItems();
    }

    //########################################
}