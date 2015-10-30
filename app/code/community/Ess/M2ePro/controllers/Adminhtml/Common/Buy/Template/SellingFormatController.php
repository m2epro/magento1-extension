<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_Buy_Template_SellingFormatController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Policies'))
            ->_title(Mage::helper('M2ePro')->__('Selling Format Policies'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Common/Buy/Template/SellingFormatHandler.js');

        $this->_initPopUp();

        $this->setPageHelpLink(Ess_M2ePro_Helper_Component_Buy::NICK, 'Selling+Format+Policy');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/configuration');
    }

    //########################################

    public function indexAction()
    {
        return $this->_redirect('*/adminhtml_common_template/index', array(
            'channel' => Ess_M2ePro_Helper_Component_Buy::NICK
        ));
    }

    //########################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Buy')->getModel('Template_SellingFormat')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Policy does not exist'));
            return $this->_redirect('*/adminhtml_common_template/index', array(
                'channel' => Ess_M2ePro_Helper_Component_Buy::NICK
            ));
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_template_sellingFormat_edit'))
            ->renderLayout();
    }

    //########################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->indexAction();
        }

        $id = $this->getRequest()->getParam('id');

        // Base prepare
        // ---------------------------------------
        $data = array();

        $keys = array(
            'title',

            'qty_mode',
            'qty_custom_value',
            'qty_custom_attribute',
            'qty_percentage',
            'qty_modification_mode',
            'qty_min_posted_value',
            'qty_max_posted_value',

            'price_mode',
            'price_coefficient',
            'price_custom_attribute',

            'price_variation_mode',

            'price_vat_percent'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $data['title'] = strip_tags($data['title']);
        // ---------------------------------------

        // Add or update model
        // ---------------------------------------
        $model = Mage::helper('M2ePro/Component_Buy')->getModel('Template_SellingFormat')->load($id);

        $oldData = $model->getDataSnapshot();
        $model->addData($data)->save();
        $newData = $model->getDataSnapshot();

        $model->getChildObject()->setSynchStatusNeed($newData,$oldData);

        $id = $model->getId();

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Policy was successfully saved'));
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('*/adminhtml_common_template/index', array(), array(
            'edit' => array('id'=>$id),
            'channel' => Ess_M2ePro_Helper_Component_Buy::NICK
        )));
    }

    //########################################
}
