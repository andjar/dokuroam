<?php

       $refPath = substr($_POST['fpath'],0,-4) . '/references.txt';
       $ref = file_get_contents($refPath);
        // Append a new person to the file
       $refPath = $_SERVER['DOCUMENT_ROOT'] . '/data/tmp/' . $_POST['pageid'] . '.bib';
        // Write the contents back to the file
       file_put_contents($refPath, $ref);

       $textPath = $_POST['fpath'];
       $text = file_get_contents($textPath);

       $lines = explode(PHP_EOL, $text);
       $insideWRAP = false;
       $leftWRAP = false;
       $newWRAPs = 0;
       foreach($lines as $key => $line){
           if( $insideWRAP == false && (strcmp($line, '<WRAP yaml>') == 0)){
               $insideWRAP = true;
               $lines[$key] = '---';
           }else{
               if($insideWRAP){
                   if(strpos($line, '</WRAP>') !== false){
                       if($newWRAPs > 0){
                           unset($lines[$key]);// = '';
                           $newWRAPs--;
                       }else{
                           $lines[$key] = '---';
                           $insideWRAP = false;
                           $leftWRAP = true;
                       }
                   } elseif (strpos($line, '<WRAP') !== false){
                       unset($lines[$key]);// = '';
                       $newWRAPs++;
                   } else {
                       $lines[$key] = strip_tags($line);
                       if(strpos($lines[$key], 'to: ') !== false){
                           $toFormat = substr($lines[$key], 4);
                       }
                   }
               }
           }
       }

       $text = implode(PHP_EOL, $lines);
       $mdPath = $_SERVER['DOCUMENT_ROOT'] . '/data/tmp/' . $_POST['pageid'] . '.md';
       file_put_contents($mdPath, $text);

       $run_str = 'pandoc --bibliography="'. $refPath .'" --filter pandoc-citeproc "' . $mdPath . '" -o "' . $_SERVER['DOCUMENT_ROOT'] . '/data/tmp/' . $_POST['pageid'] .'.' . $toFormat .'"';
    exec($run_str);
    header("Location: ". '/data/tmp/' . $_POST['pageid'] .'.' . $toFormat);

?>