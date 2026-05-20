<?php
$xmlFile = __DIR__ . '/doc_xml_output.xml';
$outFile = __DIR__ . '/doc_text.txt';
if (!file_exists($xmlFile)) {
    echo "MISSING_XML\n";
    exit(1);
}
$raw = file_get_contents($xmlFile);
// convert from UTF-16LE if needed
if (strpos($raw, "<?xml") === false && strpos($raw, "\x00<") !== false) {
    $raw = mb_convert_encoding($raw, 'UTF-8', 'UTF-16LE');
}
// remove XML tags and keep text
$text = preg_replace('/<[^>]+>/', ' ', $raw);
// collapse whitespace
$text = preg_replace('/\s+/u', ' ', $text);
$text = trim($text);
file_put_contents($outFile, $text);
echo "DONE\n";
