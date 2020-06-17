<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Synchronization_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('synchronizationLog');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_synchronization_log';

        $this->_headerText = '';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->setTemplate('M2ePro/widget/grid/container/only_content.phtml');
    }

    //########################################

    protected function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_helpBlock',
            '',
            array(
                'content' => Mage::helper('M2ePro')->__(
                    'This Log contains information about Synchronization task performing.<br><br>

                    <strong>Note:</strong> Only errors and warnings are logged.<br><br>
            
                    The detailed information can be found <a href="%url%" target="_blank">here</a>.',
                    Mage::helper("M2ePro/Module_Support")->getDocumentationUrl(null, null, "x/L4taAQ")
                ),
                'title' => Mage::helper('M2ePro')->__('Synchronization')
            )
        );

        return $helpBlock->toHtml() . parent::_toHtml();
    }

    //########################################
}
