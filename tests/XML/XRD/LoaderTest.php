<?php
require_once 'XML/XRD/Loader.php';
require_once 'XML/XRD.php';

use PHPUnit\Framework\TestCase;

/**
 * @covers XML_XRD_Loader
 */
class XML_XRD_LoaderTest extends TestCase
{
    protected $cleanupList = array();

    public function setUp(): void
    {
        $this->xrd = new XML_XRD();
        $this->loader = new XML_XRD_Loader($this->xrd);
    }

    public function tearDown(): void
    {
        foreach ($this->cleanupList as $k => $file) {
            chmod($file, '0700');
            unlink($file);
            unset($this->cleanupList[$k]);
        }
    }

    public function testLoadFileTypeNull()
    {
        $this->loader->loadFile(
            __DIR__ . '/../../xrd/properties.xrd'
        );
        $this->assertEquals('http://example.com/gpburdell', $this->xrd->subject);
    }

    public function testLoadFileTypeWrong()
    {
        $this->expectException(XML_XRD_Loader_Exception::class);
        $this->expectExceptionMessage('No loader for XRD type "foobarbaz"');
        @$this->loader->loadFile(
            __DIR__ . '/../../xrd/properties.xrd',
            'foobarbaz'
        );
    }

    public function testLoadFileTypeXml()
    {
        $this->loader->loadFile(
            __DIR__ . '/../../xrd/properties.xrd',
            'xml'
        );
        $this->assertEquals('http://example.com/gpburdell', $this->xrd->subject);
    }

    public function testLoadStringTypeNull()
    {
        $this->loader->loadString(
            '{"subject":"gpburdell@example.org"}'
        );
        $this->assertEquals('gpburdell@example.org', $this->xrd->subject);
    }

    public function testLoadStringTypeWrong()
    {
        $this->expectException(XML_XRD_Loader_Exception::class);
        $this->expectExceptionMessage('No loader for XRD type "foobarbaz"');
        @$this->loader->loadString(
            '{"subject":"gpburdell@example.org"}',
            'foobarbaz'
        );
    }

    public function testLoadStringJson()
    {
        $this->loader->loadString(
            '{"subject":"gpburdell@example.org"}',
            'json'
        );
        $this->assertEquals('gpburdell@example.org', $this->xrd->subject);
    }

    public function testDetectTypeFromFileDoesNotExist()
    {
        $this->expectException(XML_XRD_Loader_Exception::class);
        $this->expectExceptionMessage('Error loading XRD file: File does not exist');
        $this->loader->detectTypeFromFile(__DIR__ . '/../doesnotexist');
    }

    public function testDetectTypeFromFileCannotOpen()
    {
        $this->expectException(XML_XRD_Loader_Exception::class);
        $this->expectExceptionMessage('Cannot open file to determine type');
        $file = tempnam(sys_get_temp_dir(), 'xml_xrd-unittests');
        $this->cleanupList[] = $file;
        chmod($file, '0000');
        @$this->loader->detectTypeFromFile($file);
    }

    public function testDetectTypeFromStringUnknownFormat()
    {
        $this->expectException(XML_XRD_Loader_Exception::class);
        $this->expectExceptionMessage('Detecting file type failed');
        $this->loader->detectTypeFromString('asdf');
    }
}
?>
