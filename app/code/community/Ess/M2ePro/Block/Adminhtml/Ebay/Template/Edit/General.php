<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Block_Adminhtml_Ebay_Template_Edit getParentBlock()
 */
class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Edit_General extends Mage_Adminhtml_Block_Widget
{
    private $enabledMarketplaces = NULL;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayTemplateEditGeneral');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/template/edit/general.phtml');
    }

    public function getTemplateNick()
    {
        return $this->getParentBlock()->getTemplateNick();
    }

    public function getTemplateId()
    {
        $template = $this->getParentBlock()->getTemplateObject();

        return $template ? $template->getId() : NULL;
    }

    public function canDisplayMarketplace()
    {
        $manager = Mage::getSingleton('M2ePro/Ebay_Template_Manager')
            ->setTemplate($this->getTemplateNick());

        return $manager->isMarketplaceDependentTemplate();
    }

    public function getEnabledMarketplaces()
    {
        if (is_null($this->enabledMarketplaces)) {
            $collection = Mage::getModel('M2ePro/Marketplace')->getCollection();
            $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);
            $collection->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
            $collection->setOrder('sorder', 'ASC');

            $this->enabledMarketplaces = $collection;
        }

        return $this->enabledMarketplaces->getItems();
    }

    public function getMarketplaceId()
    {
        $template = $this->getParentBlock()->getTemplateObject();

        if ($template) {
            return $template->getData('marketplace_id');
        }

        return NULL;
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $this->setChild('confirm', $this->getLayout()->createBlock('M2ePro/adminhtml_widget_dialog_confirm'));
        // ---------------------------------------
    }

    //########################################
}