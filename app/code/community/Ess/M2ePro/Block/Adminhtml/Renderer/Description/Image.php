<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Renderer_Description_Image
    extends Ess_M2ePro_Block_Adminhtml_Renderer_Description_Abstract
{
    private $imageId;

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
        if (is_null($this->imageId)) {
            $this->imageId = substr(sha1(
                'image-' . $this->getData('index_number') . json_encode($this->getData('src'))
            ), 20);
        }
        return $this->imageId;
    }

    //########################################

    public function isLinkMode()
    {
        return $this->getData('linked_mode') == Ess_M2ePro_Helper_Module_Renderer_Description::IMAGES_MODE_NEW_WINDOW;
    }

    //########################################
}