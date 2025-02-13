<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Account_Edit_Tabs_Order_ExcludedCountries
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/amazon/account/tabs/order/excluded_countries.phtml');
    }

    protected function _beforeToHtml()
    {
        $closeBtn = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                    'style'   => 'float: right;',
                    'label'   => Mage::helper('M2ePro')->__('Confirm'),
                    'onclick' => 'AmazonAccountObj.confirmExcludedCountries();'
                )
            );
        $this->setChild('excluded_countries_close_btn', $closeBtn);

        return parent::_beforeToHtml();
    }

    public function getSelectedCountries()
    {
        return $this->getData('selected_countries');
    }

    public function getCountriesList()
    {
        return array_chunk(
            Mage::helper('M2ePro/Component_Amazon')->getEEACountriesList(),
            7,
            true
        );
    }
}
