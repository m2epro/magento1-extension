window.GridFrame = Class.create(Common, {

    // ---------------------------------------

    autoHeightFrameByContent: function(contentContainer, frame)
    {
        var wasHidden = contentContainer.style.display == 'none';

        if (wasHidden) {
            contentContainer.style.position = 'absolute';
            contentContainer.style.visibility = 'hidden';
            contentContainer.style.display = 'block';
        }

        var height = frame.contentWindow.document.body.offsetHeight;

        if (wasHidden) {
            contentContainer.style.position = 'static';
            contentContainer.style.visibility = 'visible';
            contentContainer.style.display = 'none';
        }

        frame.style.height = height + 'px';
    }

    // ---------------------------------------
});