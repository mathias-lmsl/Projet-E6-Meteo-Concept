<?php
$directory = '../qrcodes/';
$files = array_diff(scandir($directory), array('..', '.'));

$qrcodes = [];
foreach ($files as $file) {
    $qrcodes[] = [
        'filename' => $file,
        'path' => $directory . $file
    ];
}

echo json_encode($qrcodes);
?>