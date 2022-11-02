<?php

use PHPUnit\Framework\TestCase;
use XRD\Document;
use XRD\Loader;
use XRD\Loader\LoaderException;

/**
 * @covers Loader
 */
class LoaderTest extends TestCase
{
    protected $cleanupList = array();

    public function setUp(): void
    {
        $this->xrd = new Document();
        $this->loader = new Loader($this->xrd);
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
        $this->expectException(LoaderException::class);
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
        $this->expectException(LoaderException::class);
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
        $this->expectException(LoaderException::class);
        $this->expectExceptionMessage('Error loading XRD file: File does not exist');
        $this->loader->detectTypeFromFile(__DIR__ . '/../doesnotexist');
    }

    public function testDetectTypeFromFileCannotOpen()
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionMessage('Cannot open file to determine type');
        $file = tempnam(sys_get_temp_dir(), 'xml_xrd-unittests');
        $this->cleanupList[] = $file;
        chmod($file, '0000');
        @$this->loader->detectTypeFromFile($file);
    }

    public function testDetectTypeFromStringUnknownFormat()
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionMessage('Detecting file type failed');
        $this->loader->detectTypeFromString('asdf');
    }
}
?>
