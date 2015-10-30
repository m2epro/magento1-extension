<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Product_Rule_Chooser_Category
    extends Mage_Adminhtml_Block_Catalog_Category_Checkboxes_Tree
{
    //########################################

    public function getLoadTreeUrl($expanded=null)
    {
        $params = array(
            '_current' => true,
            'id' => null,
            'store' => $this->getRequest()->getParam('store', 0)
        );

        if ((is_null($expanded) && Mage::getSingleton('admin/session')->getIsTreeWasExpanded())
            || $expanded == true) {

            $params['expand_all'] = true;
        }

        return $this->getUrl('*/*/categoriesJson', $params);
    }

    //########################################
}
