(function (tinymce) {

    tinymce.PluginManager.add('walkthecounty_shortcode', function (editor) {
        editor.addCommand('WalkTheCounty_Shortcode', function () {

            if (window.scForm) {

                window.scForm.open(editor.id);
            }
        });
    });

})(window.tinymce);
