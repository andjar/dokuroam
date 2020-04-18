/**
 * @date 20130405 Leo Eibler <dokuwiki@sprossenwanne.at> \n
 *                replace old sack() method with new jQuery method and use post instead of get - see https://www.dokuwiki.org/devel:jqueryfaq \n
 * @date 20130407 Leo Eibler <dokuwiki@sprossenwanne.at> \n
 *                use jQuery for finding the elements \n
 * @date 20130408 Christian Marg <marg@rz.tu-clausthal.de> \n
 *                change only the clicked todoitem instead of all items with the same text \n
 * @date 20130408 Leo Eibler <dokuwiki@sprossenwanne.at> \n
 *                migrate changes made by Christian Marg to current version of plugin (use jQuery) \n
 * @date 20130410 by Leo Eibler <dokuwiki@sprossenwanne.at> / http://www.eibler.at \n
 *                bugfix: encoding html code (security risk <todo><script>alert('hi')</script></todo>) - bug reported by Andreas \n
 * @date 20130413 Christian Marg <marg@rz.tu-clausthal.de> \n
 *                bugfix: chk.attr('checked') returns checkbox state from html - use chk.is(':checked') - see http://www.unforastero.de/jquery/checkbox-angehakt.php \n
 * @date 20130413 by Leo Eibler <dokuwiki@sprossenwanne.at> / http://www.eibler.at \n
 *                bugfix: config option Strikethrough \n
 */

/**
 * html-layout:
 *
 * +input[checkbox].todocheckbox
 * +span.todotext
 * -del
 * --span.todoinnertext
 * ---anchor with text or text only
 */

var ToDoPlugin = {
    /**
     * lock to prevent simultanous requests
     */
    locked: false,

    /**
     * @brief onclick method for input element
     *
     * @param {jQuery} $chk the jQuery input element
     */
    todo: function ($chk) {
        //skip when locked
        if (ToDoPlugin.locked) {
            return;
        }
        //set lock
        ToDoPlugin.locked = true;


        var $spanTodoinnertext = $chk.nextAll("span.todotext:first").find("span.todoinnertext"),
            param = $chk.data(), // contains: index, pageid, date, strikethrough
            checked = !$chk.is(':checked');

        // if the data-index attribute is set, this is a call from the page where the todos are defined
        if (param.index === undefined) param.index = -1;

        if ($spanTodoinnertext.length) {

            /**
             * Callback function update the todoitem when save request succeed
             *
             * @param {Array} data returned by ajax request
             */
            var whenCompleted = function (data) {
                //update date after edit and show alert when needed
                if (data.date) {
                    jQuery('input.todocheckbox').data('date', data.date);
                }
                if (data.message) {
                    alert(data.message);
                }
                //apply styling, or undo checking checkbox
                if (data.succeed) {
                    $chk.prop('checked', checked);

                    if (checked) {
                        if (param.strikethrough && !$spanTodoinnertext.parent().is("del")) {
                            $spanTodoinnertext.wrap("<del></del>");
                        }
                    } else {
                        if ($spanTodoinnertext.parent().is("del")) {
                            $spanTodoinnertext.unwrap();
                        }
                    }
                }

                //release lock
                ToDoPlugin.locked = false;
            };

            jQuery.post(
                DOKU_BASE + 'lib/exe/ajax.php',
                {
                    call: 'plugin_todo',
                    index: param.index,
                    pageid: param.pageid,
                    checked: checked ? "1" : "0",
                    date: param.date
                },
                whenCompleted,
                'json'
            );
        } else {
            alert("Appropriate javascript element not found.\nReverting checkmark.");
        }

    }


};

jQuery(function(){

    // add handler to checkbox
    jQuery('input.todocheckbox').click(function(e){
        e.preventDefault();
        e.stopPropagation();

        var $this = jQuery(this);
        // undo checking the checkbox
        $this.prop('checked', !$this.is(':checked'));

        ToDoPlugin.todo($this);
    });

    // add click handler to todotext spans when marked with 'clickabletodo'
    jQuery('span.todotext.clickabletodo').click(function(){
        //Find the checkbox node we need
        var $chk = jQuery(this).prevAll('input.todocheckbox:first');

        ToDoPlugin.todo($chk);
    });

});
