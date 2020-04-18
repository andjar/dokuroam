/**
 * Init datepicker for all date fields
 */

jQuery(function(){
    jQuery('.bureaucracy__plugin .datepicker').datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true
    });
});