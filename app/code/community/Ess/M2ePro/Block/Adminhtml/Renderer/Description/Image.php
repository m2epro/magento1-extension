<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Renderer_Description_Image
    extends Ess_M2ePro_Block_Adminhtml_Renderer_Description_Abstract
{
    protected $_imageId;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('rendererDescriptionImage');
        // ---------------------------------------

        $this->setTemplate('M2ePro/renderer/description/image.phtml');
    }

    //########################################

    public function getImageId()
    {
        if ($this->_imageId === null) {
            $this->_imageId = substr(
                sha1(
                    'image-' . $this->getData('index_number') . Mage::helper('M2ePro')->jsonEncode(
                        $this->getData('src')
                    )
                ), 20
            );
        }

        return $this->_imageId;
    }

    //########################################

    public function isModeDefault()
    {
        return $this->getData('linked_mode') == Ess_M2ePro_Helper_Module_Renderer_Description::IMAGES_MODE_DEFAULT;
    }

    //########################################
}
