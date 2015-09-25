SupportHandler = Class.create();
SupportHandler.prototype = Object.extend(new CommonHandler(), {

    //----------------------------------

    initialize: function() {},

    //----------------------------------

    searchUserVoiceData: function()
    {
        var self = SupportHandlerObj;
        var query = $('query').value;

        if (query === '') {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_support/getResultsHtml'), {
            method: 'post',
            parameters: {
                query: query
            },
            asynchronous: true,
            onSuccess: function(transport) {
                $('support_results').style.cssText = '';
                $('support_results_content').innerHTML = transport.responseText;
                $('support_results').simulate('click');
                $('support_other_container').show();
            }
        });
    },

    forceShowContactSupportForm: function()
    {
        $('support_other_container').show();
        $('support_support_form').simulate('click');

        if ($('support_results_content').innerHTML == '') {
            $('support_results').hide();
        }
    },

    keyPressQuery: function(event)
    {
        var self = SupportHandlerObj;

        if (event.keyCode == 13) {
            self.searchUserVoiceData();
        }
    },

    //----------------------------------

    toggleArticle: function(answerId)
    {
        var answerBlock = $('article_answer_' + answerId);

        if (!answerBlock.visible()) {
            $('article_meta_' + answerId).hide();
            Effect.Appear(answerBlock, {duration:0.5});
        } else {
            Effect.Fade(answerBlock, {duration:0.3});
            $('article_meta_' + answerId).show();
        }
    },

    toggleSuggestion: function(suggestionId)
    {
        var suggestionBlock = $('suggestion_text_' + suggestionId);

        if (!suggestionBlock.visible()) {
            $('suggestion_meta_' + suggestionId).hide();
            Effect.Appear(suggestionBlock, {duration:0.5});
        } else {
            Effect.Fade(suggestionBlock, {duration:0.3});
            $('suggestion_meta_' + suggestionId).show();
        }
    },

    toggleMoreButton: function()
    {
        if ($('more_button_container').visible()) {
            $('more_button_container').hide();
        } else {
            $('more_button_container').show();
        }
    },

    //----------------------------------

    moreAttachments: function()
    {
        var self = SupportHandlerObj;
        var emptyField = false;

        $$('#more input').each(function(obj) {
            if (obj.value == '') {
                emptyField = true;
            }
        });

        if (emptyField) {
            return;
        }
        $('more').insert('<input type="file" name="files[]" onchange="SupportHandlerObj.toggleMoreButton()" /><br/>');
        self.toggleMoreButton();
    },

    //----------------------------------

    setTabActive: function(tabId)
    {
        $(tabId).simulate('click');
    },

    //----------------------------------

    goToArticle: function(url)
    {
        var self = SupportHandlerObj;
        var urlParam = base64_encode(url);

        $('support_articles').href += 'url/' + urlParam + '/';
        $('support_articles_content').innerHTML = '';

        self.setTabActive('support_articles');

        if (location.href.indexOf('#') == -1) {
            setLocation(location.href+'#support_articles');
        } else {
            setLocation(location.href);
        }
    }

    //----------------------------------
});