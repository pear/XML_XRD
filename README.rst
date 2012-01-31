*******
XML_XRD
*******

PHP library to parse `Extensible Resource Descriptor (XRD) Version 1.0` files.

The XRD format supercedes the XRDS format defined in XRI 2.0.


========
Examples
========

Load from file
==============
::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('/path/to/my.xrd');


Load from string
================
::

    <?php
    $myxrd = <<<XRD
    <?xml version="1.0"?>
    <XRD>
     ...
    XRD;

    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadString($myxrd);


Verify subject
==============
Check if the XRD file really describes the resource/URL that we requested the
XRD for::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('http://example.org/.well-known/host-meta');
    if (!$xrd->describes('http://example.org/')) {
        die('XRD document is not the correct one for http://example.org/');
    }

The ``<subject>`` and all ``<alias>`` tags are checked.


Get all links
=============
::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('http://example.org/.well-known/host-meta');
    foreach ($xrd as $link) {
        echo $link->rel . ': ' . $link->href . "\n";
    }


Get link by relation
====================
Returns the first link that has the given ``relation``::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('http://example.org/.well-known/host-meta');
    $idpLink = $xrd->get('lrdd');
    echo $idpLink->rel . ': ' . $idpLink->href . "\n";


Get link by relation + optional type
====================================
If no link with the given ``type`` is found, the first link with the correct
``relation`` and an empty ``type`` will be returned::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('http://example.org/.well-known/host-meta');
    $link = $xrd->get('lrdd', 'application/xrd+xml');
    echo $link->rel . ': ' . $link->href . "\n";


Get link by relation + type
===========================
The ``relation`` and the ``type`` both need to match exactly::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('http://example.org/.well-known/host-meta');
    $link = $xrd->get('lrdd', 'application/xrd+xml', false);
    echo $link->rel . ': ' . $link->href . "\n";


Get all links by relation
=========================
::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('http://example.org/.well-known/host-meta');
    foreach ($xrd->getAll('lrdd') as $link) {
        echo $link->rel . ': ' . $link->href . "\n";
    }


Get a single property
=====================
::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('http://example.org/.well-known/host-meta');
    if (isset($xrd['http://spec.example.net/type/person'])) {
        echo $xrd['http://spec.example.net/type/person'] . "\n";
    }


Get all properties
==================
::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('http://example.org/.well-known/host-meta');
    foreach ($xrd->getProperties() as $property) {
        echo $property->type . ': ' . $property->value . "\n",
    }


Get all properties of a type
============================
::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('http://example.org/.well-known/host-meta');
    foreach ($xrd->getProperties('http://spec.example.net/type/person') as $property) {
        echo $property->type . ': ' . $property->value . "\n",
    }


====
TODO
====

+ load from string
+ load from file
+ verify that subject/alias matches
+ get properties
+ get links

  + all links
  + links with certain properties set

- get expiry time
- XML signature verification
- (very optional) XRDS (multiple XRD)?

==========
References
==========

- Standard: http://docs.oasis-open.org/xri/xrd/v1.0/xrd-1.0.html
- http://www.oasis-open.org/committees/tc_home.php?wg_abbrev=xri
- http://code.google.com/p/webfinger/wiki/XrdFiles
