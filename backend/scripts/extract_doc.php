<?php
$file = __DIR__ . '/../../docs/PANDUAN MATA KULIAH CAPSTONE PROJECT MOBILE COMPUTING.docx';
$zip = new ZipArchive();
if ($zip->open($file) === TRUE) {
    $content = $zip->getFromName('word/document.xml');
    echo $content ?: 'EMPTY';
    $zip->close();
} else {
    echo 'CANNOT_OPEN';
}
