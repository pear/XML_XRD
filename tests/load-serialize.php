<?php
/**
 * Test script to load an XRD file and save it again.
 * The result should be equal.
 */
if (is_dir(__DIR__ . '/../src/')) {
    set_include_path(
        __DIR__ . '/../src/' . PATH_SEPARATOR . get_include_path()
    );
}
require_once 'XML/XRD.php';
$x = new XML_XRD();
$file = __DIR__ . '/xrd-1.0-b1.xrd';
$x->loadFile($file);
echo $x->toXML();
?>