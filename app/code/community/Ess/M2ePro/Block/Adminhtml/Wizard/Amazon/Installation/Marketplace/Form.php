<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Amazon_Installation_Marketplace_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('wizardAmazonMarketplaceForm');
        $this->setContainerId('wizard_marketplaces_container');
        $this->setTemplate('M2ePro/wizard/amazon/installation/marketplace/form.phtml');
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
        $marketplaces = Mage::helper('M2ePro/Component_Amazon')->getCollection('Marketplace')
            ->setOrder('group_title', 'ASC')
            ->setOrder('sorder','ASC')
            ->setOrder('title','ASC')
            ->getItems();

        $resultMarketplaces = array();
        $storedStatuses = array();

        $canadaMarketplace = NULL;
        $canadaStoreStatus = NULL;

        foreach ($marketplaces as $marketplace) {
            if (is_null($marketplace->getData('developer_key'))) {
                continue;
            }

            $isLocked = (bool)Mage::helper('M2ePro/Component_Amazon')->getCollection('Account')
                ->addFieldToFilter('marketplace_id', $marketplace->getId())
                ->getSize();

            if ($marketplace->getId() == 24 && $marketplace->getCode() == 'CA') {
                $canadaMarketplace = array(
                    'instance' => $marketplace,
                    'params'   => array('locked' => $isLocked)
                );

                $canadaStoreStatus = array(
                    'marketplace_id' => $marketplace->getId(),
                    'status' => $marketplace->getStatus()
                );

                continue;
            }

            $storedStatuses[] = array(
                'marketplace_id' => $marketplace->getId(),
                'status' => $marketplace->getStatus()
            );

            $resultMarketplaces[] = array(
                'instance' => $marketplace,
                'params'   => array('locked' => $isLocked)
            );
        }

        if (!is_null($canadaMarketplace) && !is_null($canadaStoreStatus)) {
            $storedStatuses[] = $canadaStoreStatus;
            $resultMarketplaces[] = $canadaMarketplace;
        }

        $this->marketplaces = $resultMarketplaces;
        $this->storedStatuses = $storedStatuses;

        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}