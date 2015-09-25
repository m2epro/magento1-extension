var TranslatorHandler = Class.create(Translate, {

    //----------------------------------

    translate: function($super, text)
    {
        if (!this.data.get(text)) {
            alert('Translation not found : "' + text + '"');
        }

        var newText = $super(text);
        var values = Array.prototype.slice.call(arguments).slice(0,2);
        var placeholders = newText.match(/%\w*%/g);

        if (!placeholders) {
            return newText;
        }

        for (placeholder in placeholders) {
            value = values.shift();

            if (!value) {
                break;
            }
            newText = newText.replace(placeholder, value);
        }

        return newText;
    }

    //----------------------------------
});