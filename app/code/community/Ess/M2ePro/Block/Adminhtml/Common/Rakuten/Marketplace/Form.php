<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Rakuten_Marketplace_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('rakutenMarketplaceForm');
        $this->setContainerId('magento_block_rakuten_marketplaces');
        $this->setTemplate('M2ePro/common/rakuten/marketplace.phtml');
        // ---------------------------------------
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        /** @var Ess_M2ePro_Model_Marketplace[] $marketplaces */
        $marketplaces = array();
        $marketplaces[Ess_M2ePro_Helper_Component_Buy::NICK] = Mage::helper('M2ePro/Component_Buy')
                                                                        ->getCollection('Marketplace')->getFirstItem();

        $groups = array();
        $storedStatuses = array();
        $previewGroup = '';
        $idGroup = 1;

        foreach ($marketplaces as $component => $marketplace) {

            if ($marketplace->getGroupTitle() != $previewGroup) {
                $previewGroup = $marketplace->getGroupTitle();
                $groups[] = array(
                    'id'           => $idGroup,
                    'title'        => $previewGroup,
                    'marketplaces' => array()
                );
                $idGroup++;
            }

            $storedStatuses[] = array(
                'marketplace_id' => $marketplace->getId(),
                'status' => $marketplace->getStatus()
            );

            $isLocked = (bool)Mage::helper('M2ePro/Component_'.ucfirst($component))
                ->getCollection('Account')
                ->getSize();

            $marketplace = array(
                'instance' => $marketplace,
                'params'   => array('locked' => $isLocked)
            );

            $groups[count($groups)-1]['marketplaces'][] = $marketplace;
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