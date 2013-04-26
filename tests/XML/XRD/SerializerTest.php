<?php
require_once 'XML/XRD/Loader.php';
require_once 'XML/XRD.php';

/**
 * @covers XML_XRD_Serializer
 */
class XML_XRD_SerializerTest extends PHPUnit_Framework_TestCase
{
    protected $cleanupList = array();

    public function setUp()
    {
        $this->xrd = new XML_XRD();
        $this->serializer = new XML_XRD_Serializer($this->xrd);
    }

    public function testToJson()
    {
        $this->xrd->subject = 'foo@example.org';
        $json = $this->serializer->to('json');
        $this->assertInternalType('string', $json);
        $this->assertContains('foo@example.org', $json);
    }

    /**
     * @expectedException XML_XRD_Serializer_Exception
     * @expectedExceptionMessage No serializer for type "batty"
     */
    public function testToUnsupported()
    {
        $this->xrd->subject = 'foo@example.org';
        @$this->serializer->to('batty');
    }
}
?>
