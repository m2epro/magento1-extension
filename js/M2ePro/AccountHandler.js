AccountHandler = Class.create(CommonHandler, {

    // ---------------------------------------

    on_delete_popup: function (accountIds)
    {
        var popup = Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: "Delete Account Action",//M2ePro.translator.translate('Account'),
            top: 70,
            width: 470,
            height: 250,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show
        });

        popup.okCallback = function () {
            setLocation(M2ePro.url.get('*/*/delete', {ids: accountIds}));
        };

        popup.options.destroyOnClose = true;

        var popUpContent = $('modal_dialog_message');
        popUpContent.insert($('on_delete_account_template').innerHTML);

        var container = new Element('div');
        container.style.fontSize = '1.1em';
        container.style.marginTop = '20px';
        container.style.marginBottom = '40px';
        container.innerHTML = M2ePro.translator.translate('on_delete_account_message');

        popUpContent.down('.dialog_confirm_content').appendChild(container);

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '630px';
        }, 50);
    }

    // ---------------------------------------
});