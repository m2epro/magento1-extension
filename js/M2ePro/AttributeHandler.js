AttributeHandler = Class.create();
AttributeHandler.prototype = {

    //----------------------------------

    attrData: '',

    availableAttributes: [],

    //----------------------------------

    setAvailableAttributes: function(attributes)
    {
        this.availableAttributes = attributes;
    },

    //----------------------------------

    initialize: function(selectId) {},

    //----------------------------------

    appendToText: function(ddId, targetId)
    {
        if ($(ddId).value == '') {
            return;
        }

        var suffix = '#' + $(ddId).value + '#';
        $(targetId).value = $(targetId).value + suffix;
    },

    appendToTextarea: function(value)
    {
        if (value == '') {
            return;
        }

        if (wysiwygtext.isEnabled()) {

            var data = tinyMCE.get('description_template').getContent();
            tinyMCE.get('description_template').setContent(data + value);

            return;
        }

        var element = $('description_template');

        if (document.selection) {

            /* IE */
            element.focus();
            document.selection.createRange().text = value;
            element.focus();

        } else if (element.selectionStart || element.selectionStart == '0') {

            /* Webkit */
            var startPos = element.selectionStart;
            var endPos = element.selectionEnd;
            var scrollTop = element.scrollTop;
            element.value = element.value.substring(0, startPos) + value + element.value.substring(endPos, element.value.length);
            element.focus();
            element.selectionStart = startPos + value.length;
            element.selectionEnd = startPos + value.length;
            element.scrollTop = scrollTop;

        } else {

            element.value += value;
            element.focus();
        }
    },

    //----------------------------------

    checkAttributesSelect: function(id, value)
    {
        if ($(id)) {
            if (typeof M2ePro.formData[id] != 'undefined') {
                $(id).value = M2ePro.formData[id];
            }
            if (value) {
                $(id).value = value;
            }
        }
    },

    renderAttributes: function(name, insertTo, value, width)
    {
        var style = width ? ' style="width: ' + width + 'px;"' : '';
        var txt = '<select name="' + name + '" id="' + name + '"' + style + '>\n';

        txt += this.attrData;
        txt += '</select>';

        $(insertTo).innerHTML = txt;
        this.checkAttributesSelect(name, value);
    },

    renderAttributesWithEmptyHiddenOption: function(name, insertTo, value, width)
    {
        var style = width ? ' style="width: ' + width + 'px;"' : '';
        var txt = '<select name="' + name + '" id="' + name + '" class="M2ePro-required-when-visible"' + style + '>\n';

        txt += '<option style="display: none;"></option>\n';
        txt += this.attrData;
        txt += '</select>';

        $(insertTo).innerHTML = txt;
        this.checkAttributesSelect(name, value);
    },

    renderAttributesWithEmptyOption: function(name, insertTo, value, notRequiried)
    {
        var className = notRequiried ? '' : ' class="M2ePro-required-when-visible"';
        var txt = '<select name="' + name + '" id="' + name + '" ' + className + '>\n';

        txt += '<option class="empty"></option>\n';
        txt += this.attrData;
        txt += '</select>';

        if ($(insertTo + '_note') != null && $$('#' + insertTo + '_note').length != 0) {
            $(insertTo).innerHTML = txt + $(insertTo + '_note').innerHTML;
        } else {
            $(insertTo).innerHTML = txt;
        }

        this.checkAttributesSelect(name, value);
    }

    //----------------------------------
};