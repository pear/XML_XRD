<?php
require_once 'XML/XRD/Loader.php';
require_once 'XML/XRD.php';

use PHPUnit\Framework\TestCase;

/**
 * @covers XML_XRD_Serializer
 */
class XML_XRD_SerializerTest extends TestCase
{
    protected $cleanupList = array();

    public function setUp(): void
    {
        $this->xrd = new XML_XRD();
        $this->serializer = new XML_XRD_Serializer($this->xrd);
    }

    public function testToJson()
    {
        $this->xrd->subject = 'foo@example.org';
        $json = $this->serializer->to('json');
        $this->assertIsString($json);
        $this->assertStringContainsString('foo@example.org', $json);
    }

    public function testToUnsupported()
    {
        $this->expectException(XML_XRD_Serializer_Exception::class);
        $this->expectExceptionMessage('No serializer for type "batty"');
        $this->xrd->subject = 'foo@example.org';
        @$this->serializer->to('batty');
    }
}
?>
