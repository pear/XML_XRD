<?php
/**
 * Convert a XRD file to JRD or vice versa.
 */
if ($argc < 2) {
    echo "Usage: $argv[0] path/to/file.(xrd|jrd)\n";
    exit(1);
}
$file = $argv[1];

require_once 'XML/XRD.php';
require_once 'XML/XRD/Loader.php';
$xrd = new XML_XRD();
try {
    $xl = new XML_XRD_Loader($xrd);
    $type = $xl->detectTypeFromFile($file);
    $xrd->loadFile($file, $type);
    
    $targetType = $type == 'xml' ? 'json' : 'xml';
    echo $xrd->to($targetType);
} catch (XML_XRD_Exception $e) {
    echo 'Converting (X|J)RD file failed: '  . $e->getMessage() . "\n";
    exit(1);
}
