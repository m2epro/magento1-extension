<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Order_Item_Product_Mapping extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/order/item/product/mapping.phtml');
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $this->setChild(
            'product_mapping_grid', $this->getLayout()->createBlock('M2ePro/adminhtml_order_item_product_mapping_grid')
        );
        //------------------------------

        parent::_beforeToHtml();
    }

    // ####################################
}