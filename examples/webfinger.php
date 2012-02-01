<?php
/**
 * Basic WebFinger implementation to discover a user's OpenID provider
 * from just his email address
 */
$email = 'cweiske@cweiske.de';

$host = substr($email, strpos($email, '@') + 1);

require_once 'XML/XRD.php';
$xrd = new XML_XRD();
try {
    $xrd->loadFile('http://' . $host . '/.well-known/host-meta');
} catch (XML_XRD_Exception $e) {
    die('Loading XRD file failed: '  . $e->getMessage());
}

$link = $xrd->get('lrdd', 'application/xrd+xml');
if ($link === null) {
    die('No LRDD link found');
}
$template = $link->template;
$lrddUri = str_replace('{uri}', urlencode('acct:' . $email), $template);
echo 'URL with infos about ' . $email . ' is ' . $lrddUri . "\n";

$xrdLrdd = new XML_XRD();
try {
    $xrdLrdd->loadFile($lrddUri);
} catch (XML_XRD_Exception $e) {
    die('Loading LRDD XRD file failed: '  . $e->getMessage());
}

$openIdLink = $xrdLrdd->get('http://specs.openid.net/auth/2.0/provider');
if ($openIdLink === null) {
    die("No OpenID provider found for $email\n");
}

echo $email . '\'s OpenID provider is: ' . $openIdLink->href . "\n";
?>