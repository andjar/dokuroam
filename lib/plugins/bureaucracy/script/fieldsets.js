/**
 * Handle display of dependent, i. e. optional fieldsets
 *
 * Fieldsets may be defined as dependent on the value of a certain input. In
 * this case they contain a p element with the CSS class “bureaucracy_depends”.
 * This p element holds a span with the class “bureaucracy_depends_fname”
 * and optionally another span with “bureaucracy_depends_fvalue”. They
 * specify the target input (fname) and the target value for which the fieldset
 * is to be shown.
 *
 * This function adds onchange handlers to the relevant inputs for showing and
 * heading the respective fieldsets.
 *
 * @author Adrian Lang <dokuwiki@cosmocode.de>
 **/

jQuery(function () {

    jQuery('form.bureaucracy__plugin').each(function () {

        //show/hide fieldset and trigger depending children
        function updateFieldset(input) {
            jQuery.each(jQuery(input).data('dparray'), function (i, dp) {
                var showOrHide =
                    input.parentNode.parentNode.style.display !== 'none' &&                     // input/checkbox is displayed AND
                    ((input.checked === dp.tval) ||                                             //  ( checkbox is checked
                     (input.type !== 'checkbox' && (dp.tval === true && input.value !== '')) || //  OR no checkbox, but input is set
                     input.value === dp.tval);                                                  //  OR input === dp.tval )

                dp.fset.toggle(showOrHide);

                dp.fset.find('input,select')
                    .each(function () {
                        //toggle required attribute
                        var $inputelem = jQuery(this);
                        if($inputelem.hasClass('required')) {
                            if(showOrHide) {
                                $inputelem.attr('required', 'required');
                            } else {
                                $inputelem.removeAttr('required')
                            }
                        }
                        //update dependencies
                        if ($inputelem.data('dparray')) {
                            $inputelem.change();
                        }
                    });
            });
        }

        //look for p (with info about controller) in depending fieldsets
        jQuery('p.bureaucracy_depends', this)
            .each(function () {
                //get controller info
                var fname = jQuery(this).find('span.bureaucracy_depends_fname').html(),
                    fvalue = jQuery(this).find('span.bureaucracy_depends_fvalue');
                fvalue = (fvalue.length ? fvalue.html() : true);

                //get controller field and add info and change event to the input that controls depending fieldset
                var fieldsetinfo = {
                    fset: jQuery(this).parent(),
                    tval: fvalue
                };

                jQuery("label")
                    .has(":first-child:contains('" + fname + "')").first()
                    .find("select,input:last")  //yesno field contains first a hidden input
                    .each(function () {
                        if (!jQuery(this).data('dparray')) {
                            jQuery(this).data('dparray', [fieldsetinfo]);
                        } else {
                            jQuery(this).data('dparray').push(fieldsetinfo);
                        }
                    })
                    .bind('change keyup', function () {
                        updateFieldset(this);
                    })
                    .change();

            })
            .hide(); //hide p.bureaucracy_depends in fieldset

    });
});
