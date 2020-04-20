<?php

    $path_to_file = $_POST['fpath'];
    $file_contents = file_get_contents($path_to_file);
    $file_contents = str_replace("{{tag>note ","{{tag>archived ",$file_contents);
    file_put_contents($path_to_file,$file_contents);
    header("Location: doku.php?id=" . $_POST['pageid']);

?>