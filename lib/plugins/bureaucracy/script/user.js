/**
 * Provides a list of matching user names while user inputs into a userpicker
 *
 * @author Adrian Lang <lang@cosmocode.de>
 * @author Gerrit Uitslag <klapinklapin@gmail.com>
 */
jQuery(function () {
    /**
     * Ajax request for user suggestions
     *
     * @param {Object} request object, with single 'term' property
     * @param {Function} response callback, argument: the data array to suggest to the user.
     * @param {Function} getterm callback, argument: the request Object, returns: search term
     */
    function ajaxsource(request, response, getterm) {
        jQuery.getJSON(
            DOKU_BASE + 'lib/exe/ajax.php', {
                call: 'bureaucracy_user_field',
                search: getterm(request)
            }, function (data) {
                response(jQuery.map(data, function (name, user) {
                    return {
                        label: name + ' (' + user + ')',
                        value: user
                    }
                }))
            }
        );
    }

    function split(val) {
        return val.split(/,\s*/);
    }

    function extractLast(term) {
        return split(term).pop();
    }


    /**
     * pick one user
     */
    jQuery(".userpicker").autocomplete({
        source: function (request, response) {
            ajaxsource(request, response, function (req) {
                return req.term
            })
        }
    });

    /**
     * pick one or more users
     */
    jQuery(".userspicker")
        // don't navigate away from the field on tab when selecting an item
        .bind("keydown", function (event) {
            if (event.keyCode === jQuery.ui.keyCode.TAB &&
                jQuery(this).data("ui-autocomplete").menu.active) {
                event.preventDefault();
            }
        })
        .autocomplete({
            minLength: 0,
            source: function (request, response) {
                ajaxsource(request, response, function (req) {
                    return extractLast(req.term)
                })
            },
            search: function () {
                // custom minLength
                var term = extractLast(this.value);
                return term.length >= 2;
            },
            focus: function () {
                // prevent value inserted on focus
                return false;
            },
            select: function (event, ui) {
                var terms = split(this.value);
                // remove the current input
                terms.pop();
                // add the selected item
                terms.push(ui.item.value);
                // add placeholder to get the comma-and-space at the end
                terms.push("");
                this.value = terms.join(", ");
                return false;
            }
        });
});
