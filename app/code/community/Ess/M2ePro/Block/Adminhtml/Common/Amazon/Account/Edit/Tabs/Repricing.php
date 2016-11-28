<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Account_Edit_Tabs_Repricing extends Mage_Adminhtml_Block_Widget
{
    public $isRepricingLinked;
    public $m2eProRepricingProducts;

    /**
     * @var Ess_M2ePro_Model_Amazon_Account_Repricing
     */
    public $repricingObj;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonAccountEditTabsRepricing');
        // ---------------------------------------

        $this->setTemplate('M2ePro/common/amazon/account/tabs/repricing.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->isRepricingLinked = false;

        if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data') &&
            Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()) {

            /** @var $accountObj Ess_M2ePro_Model_Account */
            $accountObj = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

            $this->isRepricingLinked = $accountObj->getChildObject()->isRepricing();

            if ($this->isRepricingLinked) {
                $this->repricingObj = $accountObj->getChildObject()->getRepricing();

                /** @var Ess_M2ePro_Model_Mysql4_Amazon_Listing_Product_Collection $collection */
                $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');

                $collection->getSelect()->join(
                    array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
                    '(`l`.`id` = `main_table`.`listing_id`)',
                    array()
                );

                $collection->getSelect()->where("`second_table`.`is_variation_parent` = 0");
                $collection->getSelect()->where("`second_table`.`is_repricing` = 1");
                $collection->getSelect()->where("`l`.`account_id` = ?", $accountObj->getId());

                $this->m2eProRepricingProducts = $collection->count();
            }
        }

        return parent::_beforeToHtml();
    }

    //########################################
}
