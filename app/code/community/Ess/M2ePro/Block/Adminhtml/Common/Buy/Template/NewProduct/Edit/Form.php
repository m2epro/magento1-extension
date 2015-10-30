<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Template_NewProduct_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('buyTemplateNewProductEditForm');
        // ---------------------------------------
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $this->nodes = $connRead->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('m2epro_buy_dictionary_category'))
            ->where('parent_category_id = ?', 0)
            ->order('title ASC')
            ->query()
            ->fetchAll();

        // ---------------------------------------
        $data = array(
            'id'      => 'category_confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.confirmCategory();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('category_confirm_button',$buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'category_change_button',
            'label'   => Mage::helper('M2ePro')->__('Change Category'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.changeCategory();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('category_change_button',$buttonBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}