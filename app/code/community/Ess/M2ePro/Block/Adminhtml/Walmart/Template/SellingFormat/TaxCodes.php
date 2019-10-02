<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Template_SellingFormat_TaxCodes
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $_marketplaceId;
    protected $_noSelection;

    //########################################

    public function __construct(array $args = array())
    {
        parent::__construct($args);

        $this->_marketplaceId = (int)$args['marketplaceId'];
        $this->_noSelection   = (bool)$args['noSelection'];

        $this->setTemplate('M2ePro/walmart/template/selling_format/tax_codes.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        //------------------------------
        /** @var Ess_M2ePro_Block_Adminhtml_Walmart_Template_SellingFormat_TaxCodes_Grid $block */
        $block = $this->getLayout()
            ->createBlock(
                'M2ePro/adminhtml_walmart_template_sellingFormat_taxCodes_grid',
                '',
                array('marketplaceId' => $this->_marketplaceId, 'noSelection' => $this->_noSelection)
            );

        $this->setChild('tax_codes_grid', $block);
        //------------------------------

        //------------------------------
        $closeBtn = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'style' => 'float: right;',
                'label'   => Mage::helper('M2ePro')->__('Close'),
                'onclick' => 'Windows.getFocusedWindow().close();'
                )
            );
        $this->setChild('tax_codes_close_btn', $closeBtn);
        //------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}