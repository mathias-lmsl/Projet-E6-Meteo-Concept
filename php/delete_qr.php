<?php
if (isset($_POST['filename'])) {
    $file = '../qrcodes/' . $_POST['filename'];
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "success";
        } else {
            echo "error";
        }
    } else {
        echo "error";
    }
} else {
    echo "error";
}
?>