<?php
/**
 * Options for the ToDo Plugin
 */
 
$conf['AllowLinks'] = 0;   // Should the Todo's also Link to Files
$conf['ActionNamespace'] = ''; //What should the default namespace for actions be
$conf['Strikethrough'] = 1; // Should text have strikethrough when checked
$conf['CheckboxText'] = 1; //Should we allow action text to check the checkbox
$conf['Checkbox'] = 1; // Should the Checkbox be rendered in list view
$conf['Header'] = 'id'; // How should the header of list be rendered ID/FIRSTHEADER
$conf['Username'] = 'user'; //How should the name of the assigned user be rendered USER/REALNAME/NONE
$conf['ShowdateTag'] = 1; // Should the Start/Due-Date be rendered in a tag
$conf['ShowdateList'] = 0; // Should the Start/Due-Date be rendered in list view

//Setup VIM: ex: et ts=2 enc=utf-8 :
