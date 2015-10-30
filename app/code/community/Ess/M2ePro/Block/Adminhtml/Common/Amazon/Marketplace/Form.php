<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Marketplace_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonMarketplaceForm');
        $this->setContainerId('magento_block_amazon_marketplaces');
        $this->setTemplate('M2ePro/common/amazon/marketplace.phtml');
        // ---------------------------------------
    }

    protected function _prepareForm()
    {
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
        $groups = array();
        $storedStatuses = array();
        $idGroup = 1;

        $groupsOrder = array(
            'america' => 'America',
            'europe' => 'Europe',
            'asia_pacific' => 'Asia / Pacific'
        );

        foreach ($groupsOrder as $key => $groupOrderTitle) {
            $groups[$key] = array(
                'id'           => $idGroup++,
                'title'        => $groupOrderTitle,
                'marketplaces' => array()
            );

            foreach ($marketplaces as $marketplace) {
                if ($marketplace->getGroupTitle() != $groupOrderTitle) {
                    continue;
                }

                $storedStatuses[] = array(
                    'marketplace_id' => $marketplace->getId(),
                    'status' => $marketplace->getStatus()
                );

                $isLocked = (bool)Mage::helper('M2ePro/Component_Amazon')->getCollection('Account')
                    ->addFieldToFilter('marketplace_id', $marketplace->getId())
                    ->getSize();

                $marketplace = array(
                    'instance' => $marketplace,
                    'params'   => array('locked' => $isLocked)
                );

                $groups[$key]['marketplaces'][] = $marketplace;
            }
        }

        $this->groups = $groups;
        $this->storedStatuses = $storedStatuses;
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Update Now'),
            'onclick' => 'MarketplaceHandlerObj.runSingleSynchronization(this)',
            'class'   => 'run_single_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('run_single_button', $buttonBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}