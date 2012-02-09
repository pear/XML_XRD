<?php
/**
 * Generates a .well-known/host-meta file that's described
 * in "RFC 6415: Web Host Metadata".
 *
 * @author Christian Weiske <cweiske@php.net>
 * @link   http://tools.ietf.org/html/rfc6415
 */
require_once 'XML/XRD.php';
$x = new XML_XRD();

$x->subject = 'example.org';
$x->aliases[] = 'example.com';
$x->links[] = new XML_XRD_Element_Link(
    'lrdd', 'http://example.org/gen-lrdd.php?a={uri}',
    'application/xrd+xml', true
);

echo $x->toXML();
?>