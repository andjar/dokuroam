/**
 * Javascript functionality for the include plugin
 */

/**
 * Highlight the included section when hovering over the appropriate include edit button
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Michael Klier <chi@chimeric.de>
 * @author Michael Hamann <michael@content-space.de>
 */
jQuery(function() {
    jQuery('.btn_incledit')
        .mouseover(function () {
            jQuery(this).closest('.plugin_include_content').addClass('section_highlight');
        })
        .mouseout(function () {
            jQuery('.section_highlight').removeClass('section_highlight');
        });
});

// vim:ts=4:sw=4:et:
