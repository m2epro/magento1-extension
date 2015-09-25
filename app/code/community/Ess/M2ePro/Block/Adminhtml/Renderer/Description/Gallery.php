<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Renderer_Description_Gallery
    extends Ess_M2ePro_Block_Adminhtml_Renderer_Description_Abstract
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('rendererDescriptionGallery');
        //------------------------------

        $this->setTemplate('M2ePro/renderer/description/gallery.phtml');
    }

    // ####################################
}