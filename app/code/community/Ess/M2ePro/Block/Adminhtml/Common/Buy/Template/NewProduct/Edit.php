<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Template_NewProduct_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('buyTemplateNewProductEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_buy_template_newProduct';
        $this->_mode = 'edit';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $templateId = $this->getRequest()->getParam('id');

        $this->_headerText = $templateId
            ? Mage::helper('M2ePro')->__('Edit New SKU Policy For %rakuten%',
                                          Mage::helper('M2ePro/Component_Buy')->getTitle())
            : Mage::helper('M2ePro')->__('Add New SKU Policy For %rakuten%',
                                          Mage::helper('M2ePro/Component_Buy')->getTitle());
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $back = $this->getRequest()->getParam('back');
        $buyListingProductIds = Mage::helper('M2ePro/Data_Session')->getValue('buy_listing_product_ids');
        $listingProductId = $this->getRequest()->getParam('listing_product_id');

        if ($back && ($buyListingProductIds || $listingProductId)) {
            // ---------------------------------------
            $url = Mage::helper('M2ePro')->getBackUrl(null, $back);
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\'' . $url .'\')',
                'class'     => 'back'
            ));
            // ---------------------------------------
        }

        // ---------------------------------------
        $params = array();
        $listingProductId && $params['listing_product_id'] = $listingProductId;
        $templateId && $params['id'] = $listingProductId;

        $url = $this->getUrl('*/adminhtml_common_buy_template_newProduct/add', $params);
        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'CommonHandlerObj.save_click(\''.$url.'\')',
            'class'     => 'save'
        ));
        // ---------------------------------------

        $saveAndAssign = (int)$this->getRequest()->getParam('save_and_assign', 1);

        if ($saveAndAssign && $buyListingProductIds) {
            // ---------------------------------------
            $url = $this->getUrl(
                '*/adminhtml_common_buy_template_newProduct/add',
                array(
                    'do_map' => true
                )
            );
            $this->_addButton('save_and_map', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Assign'),
                'onclick'   => 'CommonHandlerObj.save_click(\''.$url.'\')',
                'class'     => 'save'
            ));
            // ---------------------------------------
        }
    }

    //########################################
}