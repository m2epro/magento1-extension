<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Marketplace_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartMarketplaceForm');
        $this->setContainerId('magento_block_walmart_marketplaces');
        $this->setTemplate('M2ePro/walmart/marketplace/edit/form.phtml');
        // ---------------------------------------
    }

    //########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
            )
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        /** @var Ess_M2ePro_Model_Marketplace[] $marketplaces */
        $marketplaces = Mage::helper('M2ePro/Component_Walmart')->getCollection('Marketplace')
                                                        ->setOrder('group_title', 'ASC')
                                                        ->setOrder('sorder', 'ASC')
                                                        ->setOrder('title', 'ASC')
                                                        ->getItems();
        $groups = array();
        $storedStatuses = array();
        $idGroup = 1;

        $groupsOrder = array(
            'america' => 'America'
        );

        foreach ($groupsOrder as $key => $groupOrderTitle) {
            $groups[$key] = array(
                'id'           => $idGroup++,
                'title'        => $groupOrderTitle,
                'marketplaces' => array()
            );

            $disabledMarketplaces = array();
            foreach ($marketplaces as $marketplace) {
                if ($marketplace->getGroupTitle() != $groupOrderTitle) {
                    continue;
                }

                $isSupportedProductType = $marketplace->getChildObject()
                                                      ->isSupportedProductType();
                $storedStatuses[] = array(
                    'marketplace_id' => $marketplace->getId(),
                    'status' => $marketplace->getStatus(),
                    'is_need_sync_after_save' => !$isSupportedProductType,
                );

                $isLocked = (bool)Mage::helper('M2ePro/Component_Walmart')->getCollection('Account')
                    ->addFieldToFilter('marketplace_id', $marketplace->getId())
                    ->getSize();

                $marketplace = array(
                    'instance' => $marketplace,
                    'params'   => array(
                        'locked' => $isLocked,
                        'is_supported_pt' => $isSupportedProductType,
                    )
                );

                if ($marketplace['instance']->getData('developer_key') === null) {
                    $disabledMarketplaces[] = $marketplace;
                } else {
                    $groups[$key]['marketplaces'][] = $marketplace;
                }
            }

            $groups[$key]['marketplaces'] = array_merge($groups[$key]['marketplaces'], $disabledMarketplaces);
        }

        $this->groups = $groups;
        $this->storedStatuses = $storedStatuses;
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Update Now'),
            'onclick' => 'MarketplaceObj.runSingleSynchronization(this)',
            'class'   => 'run_single_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('run_single_button', $buttonBlock);


        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Update Now'),
            'onclick' => 'WalmartMarketplaceWithProductTypeSyncObj.runSingleSynchronization(this)',
            'class'   => 'run_single_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('run_with_pt_single_button', $buttonBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}
