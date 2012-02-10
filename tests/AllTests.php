<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'XML_XRD_AllTests::main');
}

require_once 'PHPUnit/Autoload.php';

class XML_XRD_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('XML_XRD tests');
        /** Add testsuites, if there is. */
        $suite->addTestFiles(
            glob(__DIR__ . '/XML/XRD{,/*/}*Test.php', GLOB_BRACE)
        );

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'XML_XRD_AllTests::main') {
    XML_XRD_AllTests::main();
}
?>