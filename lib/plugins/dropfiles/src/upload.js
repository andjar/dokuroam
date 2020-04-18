jQuery(function () {
    'use strict';

    var $lastKnownCaretPosition = 0; // IE 11 fix
    var $editarea;
    var $filelisting;

    var didInit = false;
    var filesThatExist = [];
    var DW_AJAX_URL = window.DOKU_BASE + 'lib/exe/ajax.php';
    var ERROR_DIALOG_ID = 'dropfiles_error_dialog';
    var UPLOAD_PROGRESS_WIDGET_ID = 'plugin_dropfiles_uploadwidget';

    /**
     * Create a XMLHttpRequest that updates the value of the provided progressbar
     *
     * @param {JQuery<Node>} $progressBar jQuery object of the progress-bar
     * @return {XMLHttpRequest} the XMLHttpRequest expected by jQuery's .ajax()
     */
    function createXHRudateProgressBar($progressBar) {
        var xhr = jQuery.ajaxSettings.xhr();
        xhr.upload.onprogress = function (ev) {
            if (ev.lengthComputable) {
                var percentComplete = ev.loaded / ev.total;
                $progressBar.progressbar('option', { value: percentComplete });
            }
        };
        return xhr;
    }

    /**
     * Remove the first item from the stack of files
     *
     * @return {void}
     */
    function skipFile() {
        jQuery('#' + ERROR_DIALOG_ID).remove();
        filesThatExist.shift();
        if (filesThatExist.length) {
            showErrorDialog();
        }
    }

    /**
     * Upload and overwrite the first item from the stack of files
     *
     * @return {void}
     */
    function overwriteFile() {
        jQuery('#' + ERROR_DIALOG_ID).remove();
        uploadFiles([filesThatExist.shift()], true);
        if (filesThatExist.length) {
            showErrorDialog();
        }
    }

    /**
     * Upload all remaining files to the server and overwrite the existing files there
     *
     * @return {void}
     */
    function overwriteAll() {
        jQuery('#' + ERROR_DIALOG_ID).remove();
        uploadFiles(filesThatExist, true);
        filesThatExist = [];
    }

    /**
     * Offer to rename the first file in the stack of files
     *
     * @return {void}
     */
    function renameFile() {
        var $errorDialog = jQuery('#' + ERROR_DIALOG_ID);
        var $newInput = jQuery('<form></form>');
        $newInput.append(jQuery('<input name="filename">').val(filesThatExist[0].name).css('margin-right', '0.4em'));
        $newInput.append(jQuery('<button name="rename" type="submit">' + window.LANG.plugins.dropfiles.rename + '</button>'));
        $newInput.append(jQuery('<button name="cancel">' + window.LANG.plugins.dropfiles.cancel + '</button>'));
        $newInput.find('button').button();
        $newInput.on('submit', function (event) {
            event.preventDefault();
            $errorDialog.remove();
            var fileToBeUploaded = filesThatExist.shift();
            fileToBeUploaded.newFileName = $newInput.find('input').val();
            uploadFiles([fileToBeUploaded]);
            if (filesThatExist.length) {
                showErrorDialog();
            }
        });
        $newInput.find('button[name="cancel"]').click(function (event) {
            event.preventDefault();
            $errorDialog.remove();
            showErrorDialog();
        });
        $errorDialog.parent().find('.ui-dialog-buttonpane').html($newInput);
    }

    /**
     * Create an error dialog and add files to it
     *
     * @return {void}
     */
    function showErrorDialog() {
        var fileName = filesThatExist[0].newFileName || filesThatExist[0].name;
        var text = window.LANG.plugins.dropfiles['popup:fileExists'].replace('%s', fileName);
        if (fileName !== filesThatExist[0].name) {
            text += ' ' + window.LANG.plugins.dropfiles['popup:originalName'].replace('%s', filesThatExist[0].name);
        }
        var errorTitle = window.LANG.plugins.dropfiles['title:fileExistsError'];
        var $errorDialog = jQuery('<div id="' + ERROR_DIALOG_ID + '" title="' + errorTitle + '"></div>').text(text).appendTo(jQuery('body'));
        var buttons = [
            {
                text: window.LANG.plugins.dropfiles.skip,
                click: skipFile
            },
            {
                text: window.LANG.plugins.dropfiles.rename,
                click: renameFile
            },
            {
                text: window.LANG.plugins.dropfiles.overwrite,
                click: overwriteFile
            }
        ];

        if (filesThatExist.length > 1) {
            buttons.push(
                {
                    text: window.LANG.plugins.dropfiles.overwriteAll,
                    click: overwriteAll
                }
            );
        }
        jQuery($errorDialog).dialog({
            width: 510,
            buttons: buttons
        }).draggable();
        jQuery($errorDialog).dialog('widget').addClass('dropfiles');
    }


    /**
     * Cancel an event.
     *
     * @param {Event} e
     */
    function cancelEvent(e) {
        e.preventDefault();
        e.stopPropagation();
    }


    /**
     * Handle drag enter.
     *
     * @param {Event} e
     */
    function onDragEnter(e) {
        cancelEvent(e);

        if ($editarea[0].selectionStart !== $lastKnownCaretPosition) {
            // IE 11 fix
            $editarea[0].setSelectionRange($lastKnownCaretPosition, $lastKnownCaretPosition);
        }
    }


    /**
     * Handle drop.
     *
     * @param {Event} e
     */
    function onDrop(e) {
        if (!e.originalEvent.dataTransfer || !e.originalEvent.dataTransfer.files.length) {
            return;
        }

        cancelEvent(e);

        var files = e.originalEvent.dataTransfer.files;
        handleDroppedFiles(files, getNamespaceFromTarget(e.target), this);
    }


    /**
     * Enable drag'n'drop for the provided elements.
     *
     * @param {jQuery} $elements The Elements for which to enable drag and drop
     *
     * @return {void}
     */
    function enableDragAndDrop($elements) {
        $elements.off('dragover', cancelEvent).on('dragover', cancelEvent);
        $elements.off('dragenter', onDragEnter).on('dragenter', onDragEnter);
        $elements.off('drop', onDrop).on('drop', onDrop);
    }

    /**
     *
     * @param {FileList} files The filelist from the event
     * @param {string} namespace the namespace in which to upload the files
     * @param {Node} elementOntoWhichItWasDropped Before this the error messeages will be inserted
     *
     * @return {void}
     */
    function handleDroppedFiles(files, namespace, elementOntoWhichItWasDropped) {
        // todo Dateigrößen, Filetypes
        var filelist = jQuery.makeArray(files).map(
            function(file) {file.namespace = namespace; return file;}
        );
        if (!filelist.length) {
            return;
        }

        // check filenames etc.
        jQuery.post(DW_AJAX_URL, {
            call: 'dropfiles_checkfiles',
            sectok: jQuery('input[name="sectok"]').val(),
            ns: namespace,
            filenames: filelist.map(function (file) {
                return file.name;
            })
        }).done(function handleCheckFilesResult(json) {
            var data = JSON.parse(json);
            var filesWithoutErrors = filelist.filter(function (file) {
                return data[file.name] === '';
            });
            filesThatExist = filelist.filter(function (file) {
                return data[file.name] === 'file exists';
            });
            var filesWithOtherErrors = filelist.filter(function (file) {
                return data[file.name] && data[file.name] !== 'file exists';
            });

            // show errors / pending files
            if (filesWithoutErrors.length) {
                uploadFiles(filesWithoutErrors);
            }

            if (filesWithOtherErrors.length) {
                filesWithOtherErrors.map(function (file) {
                    var $errorMessage = jQuery('<div class="error"></div>');
                    $errorMessage.text(file.name + ': ' + data[file.name]);
                    jQuery(elementOntoWhichItWasDropped).before($errorMessage);
                });
            }

            // upload valid files
            if (filesThatExist.length) {
                showErrorDialog();
            }

        });
    }

    /**
     * Insert the syntax to the uploaded file into the page
     *
     * @param {string} fileid the id of the uploaded file as returned by DokuWiki
     *
     * @return {void}
     */
    function insertSyntax(fileid) {
        if (!$editarea.length) {
            return;
        }
        var open = '{{' + fileid;
        var close = '}}';

        var selection = DWgetSelection($editarea[0]);
        var text = selection.getText();
        var opts;

        // don't include trailing space in selection
        if(text.charAt(text.length - 1) == ' '){
            selection.end--;
            text = selection.getText();
        }

        if(text){
            text = '|' + text;  // use text as label
        }
        opts = { nosel: true };
        text = open + text + close;
        pasteText(selection,text,opts);
    }

    /**
     *
     * @param {File[]} filelist List of the files to be uploaded
     * @param {boolean} [overwrite] should the files be overwritten at the server?
     *
     * @return {void}
     */
    function uploadFiles(filelist, overwrite) {
        if (typeof overwrite === 'undefined') {
            // noinspection AssignmentToFunctionParameterJS
            overwrite = 0;
        }
        var $widget = jQuery('#' + UPLOAD_PROGRESS_WIDGET_ID);
        $widget.show().dialog({
            width: 600,
            close: function clearDoneEntries() {
                var $uploadBars = $widget.find('.dropfiles_file_upload_bar');
                var $uploadBarsDone = $uploadBars.filter(function(index, element) {
                    return jQuery(element).find('.ui-progressbar-complete').length
                });
                $uploadBarsDone.remove();
                $widget.find('.error').remove();
            }
        });

        $widget.dialog('widget').addClass('dropfiles');
        filelist.forEach(function (file) {
            var fileName = file.newFileName || file.name;

            var $statusbar = jQuery('<div class="dropfiles_file_upload_bar"></div>');
            $statusbar.append(jQuery('<span class="filename">').text(fileName));
            var $progressBar = jQuery('<div class="progressbar">').progressbar({ max: 1 });
            $statusbar.append($progressBar);
            $widget.append($statusbar);
            if (!$widget.dialog('isOpen')) {
                $widget.dialog('open');
            }

            var form = new FormData();
            form.append('qqfile', file, fileName);
            form.append('call', 'dropfiles_mediaupload');
            form.append('sectok', jQuery('input[name="sectok"]').val());
            form.append('ns', file.namespace);
            form.append('ow', overwrite);

            var settings = {
                'type': 'POST',
                'data': form,
                'cache': false,
                'processData': false,
                'contentType': false,
                'xhr': function () {
                    return createXHRudateProgressBar($progressBar)
                }
            };

            jQuery.ajax(DW_AJAX_URL, settings)
                .done(
                    function (data) {
                        if (data.success) {
                            $progressBar.find('.ui-progressbar-value').css('background-color', 'green');
                            $statusbar.find('.filename').wrap(jQuery('<a>').attr({
                                'href': data.link,
                                'target': '_blank'
                            }));
                            if (window.JSINFO.plugins.dropfiles.insertFileLink) {
                                insertSyntax(data.id);
                            }
                            if ($filelisting.length) {
                                $filelisting.find('.plugin__filelisting_content')
                                    .trigger('namespaceFilesChanged', file.namespace);
                            }
                            return;
                        }
                        if (data.errorType === 'file exists') {
                            $progressBar.find('.ui-progressbar-value').css('background-color', 'red');
                            filesThatExist.push(file);
                            if (filesThatExist.length === 1) {
                                showErrorDialog();
                            }
                            var $fileExistsErrorMessage = jQuery('<div class="error"></div>');
                            $fileExistsErrorMessage.text(fileName + ': ' + data.error);
                            $fileExistsErrorMessage.insertAfter($statusbar);
                            return;
                        }
                        $progressBar.find('.ui-progressbar-value').css('background-color', 'red');
                        var $errorMessage = jQuery('<div class="error"></div>');
                        $errorMessage.text(fileName + ': ' + data.error);
                        $errorMessage.insertAfter($statusbar);
                    }
                )
                .fail(
                    function (jqXHR, textStatus, errorThrown) {
                        console.log('Class: , Function: fail-callback, Line 110 {jqXHR, textStatus, errorThrown}(): '
                            , { jqXHR: jqXHR, textStatus: textStatus, errorThrown: errorThrown });
                    }
                );
        });
    }

    /**
     * If the target is part of the filelisting plugin, return the namespace of the target-row, otherwise the current
     *
     * @param {Node} target The Node onto which the files were dropped
     * @return {string} The namespace referenced by the target or the current namespace
     */
    function getNamespaceFromTarget(target) {
        if (jQuery(target).closest('.plugin__filelisting').length) {
            var $targetRow = jQuery(target).closest('tr');
            return $targetRow.data('namespace') || $targetRow.data('childof') || window.JSINFO.namespace;
        }
        return window.JSINFO.namespace;
    }

    /**
     * Wrapper for initial bootstrapping
     *
     * @return {void}
     */
    function bootstrapFunctionality() {
        enableDragAndDrop($editarea);
        enableDragAndDrop($filelisting);

        if (!didInit) {
            var widgetTitle = window.LANG.plugins.dropfiles['title:fileUpload'];
            var $widget = jQuery('<div title="' + widgetTitle + '" id="' + UPLOAD_PROGRESS_WIDGET_ID + '"></div>').hide();
            jQuery('body').append($widget);
        }
        didInit = true;
    }


    /**
     * Called when the edit area loses focus.
     */
    function onEditBlur() {
        // IE 11 fix
        $lastKnownCaretPosition = $editarea[0].selectionStart;
    }


    /**
     * Initialize (or reinitialize) the plugin.
     */
    function init() {
        $editarea = jQuery('#wiki__text');
        $filelisting = jQuery('.plugin__filelisting');
        $lastKnownCaretPosition = 0; // IE 11 fix

        if ($editarea.length || $filelisting.length) {
            bootstrapFunctionality();

            $editarea.off('blur', onEditBlur).on('blur', onEditBlur);
        }
    }

    init();

    // fastwiki plugin support
    jQuery(window).on('fastwiki:afterSwitch', function(evt, viewMode, isSectionEdit, prevViewMode) {
        if (viewMode == 'edit' || isSectionEdit) {
            init();
        }
    });
});
