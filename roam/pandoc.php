<?php

    $run_str = 'pandoc --filter pandoc-citeproc -f markdown ' . $_POST['fpath'] . ' -o ' . $_SERVER['DOCUMENT_ROOT'] . '/data/tmp/' . $_POST['pageid'] .'.docx';
    exec($run_str);
    header("Location: ". '/data/tmp/' . $_POST['pageid'] .'.docx');

?>