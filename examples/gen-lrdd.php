<?php
/**
 * Generate a LRDD file that contains information about a user.
 *
 * @author Christian Weiske <cweiske@php.net>
 * @link   http://tools.ietf.org/html/draft-hammer-discovery-06
 */
require_once 'XML/XRD.php';
$x = new XML_XRD();
$x->subject = 'user@example.org';

//add link to the user's OpenID
$x->links[] = new XML_XRD_Element_Link(
    'http://specs.openid.net/auth/2.0/provider',
    'http://id.example.org/user'
);
//add link to user's home page
$x->links[] = new XML_XRD_Element_Link(
    'http://xmlns.com/foaf/0.1/homepage',
    'http://example.org/~user/'
);

echo $x->toXML();
?>