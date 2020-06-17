window.WizardMigrationToInnodbMarketplaceSynchProgress = Class.create(SynchProgress, {

    // ---------------------------------------

    end: function ($super)
    {
        $super();

        WizardObj.setStatus(M2ePro.php.constant('Ess_M2ePro_Helper_Module_Wizard::STATUS_COMPLETED'));
        WizardObj.complete();
    }

    // ---------------------------------------

});
