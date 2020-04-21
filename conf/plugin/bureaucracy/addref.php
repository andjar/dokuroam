<?php
use dokuwiki\plugin\bureaucracy\interfaces\bureaucracy_handler_interface;
 
class helper_plugin_bureaucracy_handler_addref implements bureaucracy_handler_interface {
 
    /**
     * Log the form fields to DokuWiki's debug log
     */
    public function handleData($fields, $thanks)
    {
        $data = array();
        foreach($fields as $field) {
            if ($field->getFieldType() == 'fieldset') { } else {
                $value = $field->getParam('value');
                $label = $field->getParam('label');
                if($value === null || $label === null) {  } else { $data[$label] = $value; }
            } // if
        } // foreach
        
        
        $file = $data['from'];
        $file = substr($file,0,-4);
        $file = $file . '/references.txt' ;
        // Open the file to get existing content
        $current = file_get_contents($file);
        // Append a new person to the file
        $current .= $data['reference'] . PHP_EOL.PHP_EOL;
        // Write the contents back to the file
        $result = file_put_contents($file, $current);
        //if ($result == 1) { $thanks = "Your profile has been updated.";  } else { $thanks = $file; }
        $thanks = 'Return to page';
        return $thanks;
    }
}