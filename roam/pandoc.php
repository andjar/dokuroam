<?php

        $data = substr($_POST['fpath'],0,-4) . '/references.txt';
        $current = file_get_contents($data);
        // Append a new person to the file
        $data = substr($data,0,-4) . '.bib';
        // Write the contents back to the file
        $result = file_put_contents($data, $current);

    $run_str = 'pandoc --bibliography="' . $data . '" --filter pandoc-citeproc -f markdown ' . $_POST['fpath'] . ' -o ' . $_SERVER['DOCUMENT_ROOT'] . '/data/tmp/' . $_POST['pageid'] .'.docx';
    exec($run_str);
    header("Location: ". '/data/tmp/' . $_POST['pageid'] .'.docx');

?>