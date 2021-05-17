<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Mapping_View extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
//########################################

    public function __construct(array $args = array())
    {
        parent::__construct($args);

        $this->setTemplate('M2ePro/listing/mapping/view.phtml');
    }

    protected function _beforeToHtml()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Listing_Mapping_Grid $block */
        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_mapping_grid',
            '',
            array(
                'grid_url' => $this->getData('grid_url'),
                'mapping_handler_js' => $this->getData('mapping_handler_js'),
                'mapping_action' => $this->getData('mapping_action')
            )
        );

        $this->setChild('listing_mapping_grid', $block);

        parent::_beforeToHtml();
    }

    //########################################
}