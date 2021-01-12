<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Order_UploadByUser_Popup extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /** @var string */
    protected $_component;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('orderUploadByUserGrid');

        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_order_uploadByUser';
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

    public function getGridHtml()
    {
        $this->getChild('grid')->setComponent($this->_component);

        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'UploadByUserObj.closePopup()'
        );
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);

        $helpBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_helpBlock',
            '',
            array(
                'content' => Mage::helper('M2ePro')->__(
                    <<<HTML
M2E Pro provides an automatic order synchronization as basic functionality.
Use manual order import as an alternative only in <a href="%url%" target="_blank">these cases</a>.
HTML
                    ,
                    Mage::helper('M2ePro/Module_Support')->getHowToGuideUrl('1594828')
                ),
                'style'   => 'margin-top: 15px;',
                'title'   => Mage::helper('M2ePro')->__('Order Reimport')
            )
        );

        return '<div id="uploadByUser_messages" style="margin-top: 10px;"></div>' .
               $helpBlock->toHtml() .
               parent::getGridHtml() .
               <<<HTML
<div style="margin-top: 10px; margin-bottom: 20px; text-align: right;">
    {$button->toHtml()}
</div>
HTML;
    }

    //########################################

    public function setComponent($component)
    {
        $this->_component = $component;
    }

    //########################################
}
