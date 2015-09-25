<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_TemplateController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function getCustomViewNick()
    {
        return NULL;
    }

    //#############################################

    public function checkMessagesAction()
    {
        //------------------------------
        $id   = $this->getRequest()->getParam('id');
        $nick = $this->getRequest()->getParam('nick');
        $data = $this->getRequest()->getParam($nick);
        $component = $this->getRequest()->getParam('component_mode');
        //------------------------------

        //------------------------------
        $template = NULL;
        $templateData = $data ? $data : array();
        $templateUsedAttributes = array();
        //------------------------------

        //------------------------------
        switch ($component) {
            case Ess_M2ePro_Helper_Component_Ebay::NICK:
                $manager = Mage::getSingleton('M2ePro/Ebay_Template_Manager');
                $manager->setTemplate($nick);
                $template = $manager->getTemplateModel()->load($id);
                break;
            default:
                if ($nick == Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT) {
                    $template = Mage::helper('M2ePro/Component')
                        ->getComponentModel($component, 'Template_SellingFormat')
                        ->load($id);
                }
                break;
        }
        //------------------------------

        if (!is_null($template) && $template->getId()) {
            $templateData = $template->getData();
            $templateUsedAttributes = $template->getUsedAttributes();
        }

        //------------------------------
        if (is_null($template) && empty($templateData)) {
            $this->getResponse()->setBody(json_encode(array('messages' => '')));
            return;
        }
        //------------------------------

        $this->loadLayout();

        /** @var Ess_M2ePro_Block_Adminhtml_Template_Messages $messagesBlock */
        $messagesBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_template_messages')
            ->getResultBlock($nick, $component);

        $messagesBlock->setData('template_data', $templateData);
        $messagesBlock->setData('used_attributes', $templateUsedAttributes);
        $messagesBlock->setData('marketplace_id', $this->getRequest()->getParam('marketplace_id'));
        $messagesBlock->setData('store_id', $this->getRequest()->getParam('store_id'));

        $this->getResponse()->setBody(json_encode(array('messages' => $messagesBlock->getMessagesHtml())));
    }

    //#############################################
}