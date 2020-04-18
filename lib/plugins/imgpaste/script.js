/* DOKUWIKI:include jquery.paste_image_reader.js */

jQuery(function () {
    var _didInit = false;
    function init() {
        if (!jQuery('#wiki__text').length || _didInit) return;
        _didInit = true;
        jQuery('html').pasteImageReader({
            callback: function (x) {
                if (!jQuery('#wiki__text').length) return;

                console.log(x);

                // create dialog
                var offset = jQuery('.plugin_imagepaste').length * 20;
                var $box = jQuery('<div><div class="content">' + LANG.plugins.imgpaste.inprogress + '</div></div>');
                $box.dialog({
                    title: 'Upload',
                    dialogClass: 'plugin_imagepaste',
                    closeOnEscape: false,
                    resizable: false,
                    position: {
                        my: 'center+' + offset + ' center+' + offset
                    },
                    appendTo: '.dokuwiki'
                });

                // upload via AJAX
                jQuery.ajax({
                    url: DOKU_BASE + 'lib/exe/ajax.php',
                    type: 'POST',
                    data: {
                        call: 'plugin_imgpaste',
                        data: x.dataURL,
                        id: JSINFO.id
                    },

                    // insert syntax and close dialog
                    success: function (data) {
                        $box.find('.content').addClass('success').text(data.message);
                        insertAtCarret('wiki__text', '{{:' + data.id + '}}');
                        $box.delay(500).fadeOut(500, function () {
                            $box.dialog('destroy').remove()
                        });
                    },

                    // display error and close dialog
                    error: function (xhr, status, error) {
                        $box.find('.content').addClass('error').text(error);
                        $box.delay(1000).fadeOut(500, function () {
                            $box.dialog('destroy').remove()
                        });
                    }
                });
            }
        });
    }

    init();

    // fastwiki plugin support
    jQuery(window).on('fastwiki:afterSwitch', function(evt, viewMode, isSectionEdit, prevViewMode) {
        if (viewMode == 'edit' || isSectionEdit) {
            init();
        }
    });
});
