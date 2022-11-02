<?php

use PHPUnit\Framework\TestCase;
use XRD\Document;
use XRD\Serializer;
use XRD\Serializer\SerializerException;

/**
 * @covers Serializer
 */
class SerializerTest extends TestCase
{
    protected $cleanupList = array();

    public function setUp(): void
    {
        $this->xrd = new Document();
        $this->serializer = new Serializer($this->xrd);
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
        $this->expectException(SerializerException::class);
        $this->expectExceptionMessage('No serializer for type "batty"');
        $this->xrd->subject = 'foo@example.org';
        @$this->serializer->to('batty');
    }
}
?>
