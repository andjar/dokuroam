/**
 * For the searchtags syntax: make the checkboxes behave like radio buttons
 * so the user can't both include and exclude a tag
 */
jQuery(function() {
    jQuery('form.plugin__tag_search table input').change(function() {
        if (jQuery(this).attr('checked')) { // was this input checked?
            if (jQuery(this).parent().hasClass('minus')) {
                // find the other input in the same tr and uncheck it
                jQuery(this).closest('tr').find('.plus input').attr('checked', false);
            } else {
                jQuery(this).closest('tr').find('.minus input').attr('checked', false);
            }
        }
    })
});
