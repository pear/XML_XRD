<?php

namespace Serializer;

use PHPUnit\Framework\TestCase;
use XRD\Document;

/**
 * @covers JSON
 */
class JSONTest extends TestCase
{
    public function testXrdRfc6415A()
    {
        $filePath = __DIR__ . '/../../../';
        $x = new Document();
        $x->loadFile($filePath . 'xrd/rfc6415-A.xrd');
        $this->assertEquals(
            json_decode(file_get_contents($filePath . 'jrd/rfc6415-A.jrd')),
            json_decode($x->to('json'))
        );
    }

    public function testRemoveEmptyLinksArray()
    {
        $x = new Document();
        $x->subject = 'foo';

        $res = new \stdClass();
        $res->subject = 'foo';
        $this->assertEquals(
            $res,
            json_decode($x->to('json'))
        );
    }
}

?>
