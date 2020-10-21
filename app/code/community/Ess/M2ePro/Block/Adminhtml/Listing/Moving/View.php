<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Moving_View
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct(array $args = array())
    {
        parent::__construct($args);

        $this->setTemplate('M2ePro/listing/moving/view.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        //------------------------------
        /** @var Ess_M2ePro_Block_Adminhtml_Listing_Moving_Help $block */
        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_moving_help', '', array(
                'component_mode' => $this->getData('component_mode')
            )
        );

        $this->setChild('listing_moving_help', $block);

        //------------------------------
        /** @var Ess_M2ePro_Block_Adminhtml_Listing_Moving_Grid $block */
        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_moving_grid', '',
            array(
                'grid_url' => $this->getData('grid_url'),
                'moving_handler_js' => $this->getData('moving_handler_js')
            )
        );

        $this->setChild('listing_moving_grid', $block);
        //------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}
