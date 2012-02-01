*******
XML_XRD
*******

PHP library to parse `Extensible Resource Descriptor (XRD) Version 1.0`__ files.

XRD files are used for ``.well-known/host-meta`` files as standardized in
`RFC 6415: Web Host Metadata`__, as well as in the 
`LRDD (Link-based Resource Descriptor Discovery)`__ files linked from it.

The LRDD XRD files can be used to discover information about users by just their
e-mail address, e.g. the OpenID provider.
This is the foundation of Webfinger__, which lets people use their e-mail address
to do OpenID sign in.

The XRD format supercedes the XRDS format defined in XRI 2.0, which is used in
the `Yadis communications protocol`__.

__ http://docs.oasis-open.org/xri/xrd/v1.0/xrd-1.0.html
__ http://tools.ietf.org/html/rfc6415
__ http://tools.ietf.org/html/draft-hammer-discovery-06
__ http://code.google.com/p/webfinger/wiki/WebFingerProtocol
__ http://yadis.org/

.. contents::

========
Examples
========

Real-world example
==================

Fetching LRDD URI from host-meta
================================
::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    try {
        $xrd->loadFile('http://cweiske.de/.well-known/host-meta');
    } catch (XML_XRD_Exception $e) {
        die('Loading XRD file failed: '  . $e->getMessage());
    }
    $link = $xrd->get('lrdd', 'application/xrd+xml');
    if ($link === null) {
        die('No LRDD link found');
    }
    $template = $link->template;
    $lrddUri = str_replace('{uri}', urlencode('cweiske@cweiske.de'), $template);
    echo 'URL with infos about cweiske@cweiske.de is ' . $lrddUri . "\n";


Loading XRD files
=================

Load from file
--------------
::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    try {
        $xrd->loadFile('/path/to/my.xrd');
    } catch (XML_XRD_Exception $e) {
        die('Loading XRD file failed: '  . $e->getMessage());
    }


Load from string
----------------
::

    <?php
    $myxrd = <<<XRD
    <?xml version="1.0"?>
    <XRD>
     ...
    XRD;

    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    try {
        $xrd->loadString($myxrd);
    } catch (XML_XRD_Exception $e) {
        die('Loading XRD string failed: '  . $e->getMessage());
    }


Verification
============

Verify subject
--------------
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



Link finding
============

Get all links
-------------
::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('http://example.org/.well-known/host-meta');
    foreach ($xrd as $link) {
        echo $link->rel . ': ' . $link->href . "\n";
    }


Get link by relation
--------------------
Returns the first link that has the given ``relation``::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('http://example.org/.well-known/host-meta');
    $idpLink = $xrd->get('lrdd');
    echo $idpLink->rel . ': ' . $idpLink->href . "\n";


Get link by relation + optional type
------------------------------------
If no link with the given ``type`` is found, the first link with the correct
``relation`` and an empty ``type`` will be returned::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('http://example.org/.well-known/host-meta');
    $link = $xrd->get('lrdd', 'application/xrd+xml');
    echo $link->rel . ': ' . $link->href . "\n";


Get link by relation + type
---------------------------
The ``relation`` and the ``type`` both need to match exactly::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('http://example.org/.well-known/host-meta');
    $link = $xrd->get('lrdd', 'application/xrd+xml', false);
    echo $link->rel . ': ' . $link->href . "\n";


Get all links by relation
-------------------------
::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('http://example.org/.well-known/host-meta');
    foreach ($xrd->getAll('lrdd') as $link) {
        echo $link->rel . ': ' . $link->href . "\n";
    }


Properties
==========

Get a single property
---------------------
::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('http://example.org/.well-known/host-meta');
    if (isset($xrd['http://spec.example.net/type/person'])) {
        echo $xrd['http://spec.example.net/type/person'] . "\n";
    }


Get all properties
------------------
::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('http://example.org/.well-known/host-meta');
    foreach ($xrd->getProperties() as $property) {
        echo $property->type . ': ' . $property->value . "\n",
    }


Get all properties of a type
----------------------------
::

    <?php
    require_once 'XML/XRD.php';
    $xrd = new XML_XRD();
    $xrd->loadFile('http://example.org/.well-known/host-meta');
    foreach ($xrd->getProperties('http://spec.example.net/type/person') as $property) {
        echo $property->type . ': ' . $property->value . "\n",
    }


Working with Links
==================

Accessing link attributes
-------------------------
::

    <?php
    $link = $xrd->get('http://specs.openid.net/auth/2.0/provider');

    $title = $link->getTitle('de');
    $url   = $link->href;
    $urlTemplate = $link->template;
    $mimetype    = $link->type;

Additional link properties
--------------------------
Works just like properties in the XRD document::

    <?php
    $link = $xrd->get('http://specs.openid.net/auth/2.0/provider');
    $prop = $link['foo'];


====
TODO
====

- XML signature verification
- (very optional) XRDS (multiple XRD)?

=====
Links
=====

- `XRD 1.0 standard specification`__
- `OASIS XRI committee`__
- `WebFinger protocol draft`__
- `WebFinger: Common Link relations`__
- `RFC 5785: Defining Well-Known Uniform Resource Identifiers`__
- `RFC 6415: Web Host Metadata`__
- `LRDD (Link-based Resource Descriptor Discovery) draft`__

__ http://docs.oasis-open.org/xri/xrd/v1.0/xrd-1.0.html
__ http://www.oasis-open.org/committees/tc_home.php?wg_abbrev=xri
__ http://code.google.com/p/webfinger/wiki/WebFingerProtocol
__ http://code.google.com/p/webfinger/wiki/CommonLinkRelations
__ http://tools.ietf.org/html/rfc5785
__ http://tools.ietf.org/html/rfc6415
__ http://tools.ietf.org/html/draft-hammer-discovery-06
