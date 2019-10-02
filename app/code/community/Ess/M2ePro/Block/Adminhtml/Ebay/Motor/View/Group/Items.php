<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_View_Group_Items
    extends Mage_Adminhtml_Block_Template
{
    protected $_group;
    protected $_groupId;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayMotorViewGroupItemsPopup');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/motor/view/group/items.phtml');
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
        return $this->_groupId;
    }

    /**
     * @param mixed $groupId
     */
    public function setGroupId($groupId)
    {
        $this->_groupId = $groupId;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Motor_Group
     */
    public function getGroup()
    {
        if ($this->_group === null) {
            $this->_group = Mage::getModel('M2ePro/Ebay_Motor_Group')->load($this->getGroupId());
        }

        return $this->_group;
    }

    //########################################

    public function getItemTitle()
    {
        return $this->getGroup()->isTypeEpid() ?
            Mage::helper('M2ePro')->__('ePID') :
            Mage::helper('M2ePro')->__('kType');
    }

    //########################################
}
