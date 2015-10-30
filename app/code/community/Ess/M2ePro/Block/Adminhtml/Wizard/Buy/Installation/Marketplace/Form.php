<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Buy_Installation_Marketplace_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('wizardBuyMarketplaceForm');
        $this->setContainerId('wizard_marketplaces_container');
        $this->setTemplate('M2ePro/wizard/buy/installation/marketplace/form.phtml');
        // ---------------------------------------
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------

        /** @var Ess_M2ePro_Model_Marketplace[] $marketplaces */
        $marketplaces = Mage::helper('M2ePro/Component_Buy')->getCollection('Marketplace')
            ->setOrder('group_title', 'ASC')
            ->setOrder('sorder','ASC')
            ->setOrder('title','ASC')
            ->getItems();

        $storedStatuses = array();

        foreach ($marketplaces as $marketplace) {
            $storedStatuses[] = array(
                'marketplace_id' => $marketplace->getId(),
                'status' => $marketplace->getStatus(),
                'marketplace_title' => $marketplace->getTitle(),
                'component_title' => Mage::helper('M2ePro/Component_Buy')->getTitle()
            );
        }

        $this->storedStatuses = $storedStatuses;

        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}