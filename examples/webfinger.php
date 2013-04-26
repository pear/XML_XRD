<?php
/**
 * Basic WebFinger implementation to discover a user's OpenID provider
 * from just his email address
 */
if ($argc < 2) {
    echo "Usage: $argv[0] user@example.com\n";
    exit(1);
}
$email = $argv[1];

$host = substr($email, strpos($email, '@') + 1);

require_once 'XML/XRD.php';
$xrd = new XML_XRD();
try {
    $xrd->loadFile(
        'https://' . $host . '/.well-known/webfinger?resource=acct:' . $email,
        'json'
    );
} catch (XML_XRD_Exception $e) {
    echo 'Loading JRD file failed: '  . $e->getMessage() . "\n";
    exit(1);
}

$openIdLink = $xrd->get('http://specs.openid.net/auth/2.0/provider');
if ($openIdLink === null) {
    echo "No OpenID provider found for $email\n";
    exit(2);
}

echo $email . '\'s OpenID provider is: ' . $openIdLink->href . "\n";
?>