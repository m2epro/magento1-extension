<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Order_Note_Popup extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    /** @var Ess_M2ePro_Model_Order_Note */
    protected $_noteModel;

    //########################################

    public function __construct(array $args = array())
    {
        parent::__construct($args);

        $this->setTemplate('M2ePro/order/note.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $saveBtn = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'style'   => 'float: right;',
                'label'   => Mage::helper('M2ePro')->__('Save'),
                'onclick' => "OrderNoteHandlerObj.saveNote()"
                )
            );

        $this->setChild('note_save_btn', $saveBtn);

        return parent::_beforeToHtml();
    }

    //########################################

    public function getNoteModel()
    {
        if ($this->_noteModel === null) {
            $this->_noteModel = Mage::getModel('M2ePro/Order_Note');
            if ($noteId = $this->getRequest()->getParam('note_id')) {
                $this->_noteModel->load($noteId);
            }
        }

        return $this->_noteModel;
    }

    //########################################
}