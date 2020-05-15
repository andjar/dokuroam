/**
 * Outliner plugin JS library
 *
 * @author Michael Hamann <michael [at] content-space [dot] de>
 */

jQuery(function () {
    var $outliner_dls = jQuery('dl.outliner');

    var setState = function(node, state) {
        if (state != 'open' && state != 'closed') { return; }
        var otherState = (state == 'open') ? 'closed' : 'open';
        jQuery(node).removeClass('outliner-' + otherState).addClass('outliner-' + state);
        var nodeId = getOutlinerId(node);
        if (nodeId) {
            jQuery.cookie(nodeId, state, {expires: 7, path: DOKU_BASE});
        }
    };

    var getOutlinerId = function(node) {
        var match = node.className.match(/outl_\w+/);
        if (match) {
            return match[0];
        } else {
            return null;
        }
    };

    $outliner_dls
        .addClass('outliner-js')
        .find('dt')
            .click(function() {
                if (jQuery(this.parentNode).hasClass('outliner-open')) {
                    setState(this.parentNode, 'closed');
                } else {
                    setState(this.parentNode, 'open');
                }
            })
            .mouseover(function() {
                var thisPos = jQuery(this).position();
                jQuery(this).siblings('dd').css({'left': thisPos.left + 40 + 'px', 'top': thisPos.top + 20 + 'px'});
            });
    $outliner_dls
        .each(function() {
            var id = getOutlinerId(this);
            if (id) {
               setState(this, jQuery.cookie(getOutlinerId(this)));
            }
        })
        .filter(':not(.outliner-open,.outliner-closed)').each(function() {
            setState(this, 'closed');
        });

    // delete old cookie data
    DokuCookie.init();
    var value_deleted = false;
    jQuery.each(DokuCookie.data, function(key) {
        if (key.match(/outl(iner)?_\w+_\d+/)) {
            delete DokuCookie.data[key];
            value_deleted = true;
        }
    });
    if (value_deleted) {
        if (jQuery.isEmptyObject(DokuCookie.data)) {
            jQuery.removeCookie(DokuCookie.name, {path: DOKU_BASE});
        } else { // save the data by setting a value that is already set
            var key = '', value = '';
            jQuery.each(DokuCookie.data, function(k, v) {
                key = k; value = v;
                return false;
            });
            DokuCookie.setValue(key, value);
        }
    }
});
