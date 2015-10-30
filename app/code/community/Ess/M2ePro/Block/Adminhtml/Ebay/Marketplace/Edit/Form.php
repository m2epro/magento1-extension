<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Marketplace_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('edit_form');
        $this->setContainerId('magento_block_ebay_marketplaces');
        $this->setTemplate('M2ePro/ebay/marketplace/form.phtml');
        // ---------------------------------------
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        /** @var Ess_M2ePro_Model_Marketplace $tempMarketplaces */
        $tempMarketplaces = Mage::helper('M2ePro/Component_Ebay')->getCollection('Marketplace')
            ->setOrder('group_title', 'ASC')
            ->setOrder('sorder','ASC')
            ->setOrder('title','ASC')
            ->getItems();

        $storedStatuses = array();
        $groups = array();
        $idGroup = 1;

        $groupOrder = array(
            'america' => 'America',
            'europe' => 'Europe',
            'asia_pacific' => 'Asia / Pacific',
            'other' => 'Other'
        );

        foreach ($groupOrder as $key => $groupOrderTitle) {

            $groups[$key] = array(
                'id'           => $idGroup++,
                'title'        => $groupOrderTitle,
                'marketplaces' => array()
            );

            foreach ($tempMarketplaces as $tempMarketplace) {
                if ($groupOrderTitle != $tempMarketplace->getGroupTitle()) {
                    continue;
                }

                $isLocked = (bool)Mage::getModel('M2ePro/Listing')->getCollection()
                    ->addFieldToFilter('marketplace_id', $tempMarketplace->getId())
                    ->getSize();

                $storedStatuses[] = array(
                    'marketplace_id' => $tempMarketplace->getMarketplaceId(),
                    'status'         => $tempMarketplace->getStatus()
                );

                /* @var $tempMarketplace Ess_M2ePro_Model_Marketplace */
                $marketplace = array(
                    'instance' => $tempMarketplace,
                    'params'   => array('locked' => $isLocked)
                );

                $groups[$key]['marketplaces'][] = $marketplace;
            }
        }

        $this->groups = $groups;
        $this->storedStatuses = $storedStatuses;
        // ---------------------------------------

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('M2ePro')->__('Update Now'),
                'onclick' => 'MarketplaceHandlerObj.runSingleSynchronization(this)',
                'class' => 'run_single_button'
            ));

        $this->setChild('run_single_button', $buttonBlock);

        return parent::_beforeToHtml();
    }

    //########################################
}