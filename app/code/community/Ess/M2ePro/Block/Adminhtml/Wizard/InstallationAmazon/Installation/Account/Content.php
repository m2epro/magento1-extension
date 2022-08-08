<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationAmazon_Installation_Account_Content
    extends Mage_Adminhtml_Block_Widget_Form
{
    public function _construct()
    {
        parent::_construct();
        $this->setId('wizardInstallationAmazonAccountContent');
    }

    // ----------------------------------------

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('wizard.help.block')->setContent(
            Mage::helper('M2ePro')->__(
                <<<HTML
On this step, you should link your Amazon Account with your M2E Pro.<br/><br/>
Please, select the Marketplace you are going to sell on and click on Continue button.
HTML
            )
        );

        parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $form = new Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form(
            array(
                'id'      => 'edit_form',
                'action'  => '',
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $fieldset = $form->addFieldset(
            'wizard_amazon_marketplaces',
            array()
        );

        /** @var Ess_M2ePro_Model_Resource_Marketplace_Collection $marketplacesCollection */
        $marketplacesCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Marketplace')
            ->setOrder('group_title', 'ASC')
            ->setOrder('sorder', 'ASC')
            ->setOrder('title', 'ASC');

        $marketplaces = array(
            array(
                'value' => '',
                'label' => ''
            )
        );

        foreach ($marketplacesCollection->getItems() as $marketplace) {
            $marketplaces[$marketplace['id']] = $marketplace['title'];
        }

        $fieldset->addField(
            'marketplace_id',
            'select',
            array(
                'label'    => Mage::helper('M2ePro')->__('What the Marketplace do You Want to Onboard?'),
                'class'    => 'marketplace-mode-choose',
                'name'     => 'marketplace_id',
                'values'   => $marketplaces,
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
