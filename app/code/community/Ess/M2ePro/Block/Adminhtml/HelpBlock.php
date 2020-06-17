<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 ** @method getTitle()
 ** @method getClass()
 ** @method getStyle()
 ** @method getSubTitle()
 ** @method hasNoHide()
 ** @method hasNoCollapse()
 */
class Ess_M2ePro_Block_Adminhtml_HelpBlock extends Mage_Adminhtml_Block_Template
{
    protected $_template = 'M2ePro/help_block.phtml';

    //########################################

    public function getId()
    {
        if (null === $this->getData('id') && $this->getContent()) {
            $this->setData('id', 'm2epro_block_notice_' . abs(crc32($this->getContent())));
        }

        return $this->getData('id');
    }

    public function getContent()
    {
        return $this->getData('content');
    }

    public function setContent($content)
    {
        return $this->setData('content', $content);
    }

    //########################################
}