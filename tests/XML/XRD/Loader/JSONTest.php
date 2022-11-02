<?php
require_once 'XML/XRD.php';
require_once 'XML/XRD/Loader/JSON.php';

use PHPUnit\Framework\TestCase;

/**
 * @covers XML_XRD_Loader_JSON
 */
class XML_XRD_Loader_JSONTest extends TestCase
{
    public function setUp(): void
    {
        $this->xrd = new XML_XRD();
        $this->jl = new XML_XRD_Loader_JSON($this->xrd);
    }

    public function testLoadFileDoesNotExist()
    {
        $this->expectException(XML_XRD_Loader_Exception::class);
        $this->expectExceptionMessage('Error loading JRD file');
        $this->jl = new XML_XRD_Loader_JSON(new XML_XRD());
        @$this->jl->loadFile(__DIR__ . '/doesnotexist');
    }

    public function testLoadStringEmpty()
    {
        $this->expectException(XML_XRD_Loader_Exception::class);
        $this->expectExceptionMessage('Error loading JRD: string empty');
        $this->jl = new XML_XRD_Loader_JSON(new XML_XRD());
        $this->jl->loadString('');
    }

    public function testLoadStringFailBrokenJson()
    {
        $this->expectException(XML_XRD_Loader_Exception::class);
        $this->expectExceptionMessage('Error loading JRD: JSON_ERROR_SYNTAX');
        $this->jl->loadString("{foo");
    }

    public function testLoadSubject()
    {
        $this->jl->loadFile(
            __DIR__ . '/../../../jrd/acct:bob@example.com.jrd'
        );
        $this->assertEquals('acct:bob@example.com', $this->xrd->subject);
        $this->assertTrue($this->xrd->describes('acct:bob@example.com'));
    }

    public function testLoadExpiresNotSet()
    {
        $this->jl->loadFile(
            __DIR__ . '/../../../jrd/acct:bob@example.com.jrd'
        );
        $this->assertNull($this->xrd->expires);
    }

    public function testLoadExpires()
    {
        $this->jl->loadFile(
            __DIR__ . '/../../../jrd/rfc6415-A.jrd'
        );
        $this->assertEquals(1264843800, $this->xrd->expires);
    }

    public function testLoadAliases()
    {
        $this->jl->loadFile(
            __DIR__ . '/../../../jrd/acct:bob@example.com.jrd'
        );
        $this->assertContains(
            'http://www.example.com/~bob/', $this->xrd->aliases
        );
        $this->assertTrue($this->xrd->describes('http://www.example.com/~bob/'));
    }

    public function testLoadProperties()
    {
        $this->jl->loadFile(
            __DIR__ . '/../../../jrd/acct:bob@example.com.jrd'
        );
        $this->assertTrue(isset($this->xrd['http://example.com/ns/role/']));
        $this->assertEquals('employee', $this->xrd['http://example.com/ns/role/']);
    }

    public function testLoadLinks()
    {
        $this->jl->loadFile(
            __DIR__ . '/../../../jrd/acct:bob@example.com.jrd'
        );

        $link = $this->xrd->get('http://webfinger.example/rel/blog');
        $this->assertNotNull($link);
        $this->assertEquals('text/html', $link->type);
        $this->assertEquals('http://blogs.example.com/bob/', $link->href);
        $this->assertEquals('The Magical World of Bob', $link->getTitle());
        $this->assertEquals('Le Monde Magique de Bob', $link->getTitle('fr'));
    }

    public function testLoadLinkProperties()
    {
        $this->jl->loadFile(
            __DIR__ . '/../../../jrd/mailto:sue@example.com.jrd'
        );

        $link = $this->xrd->get('http://webfinger.example/rel/smtp-server');
        $this->assertNotNull($link);
        $this->assertNull($link->type);
        $this->assertNull($link->href);

        $this->assertTrue(isset($link['http://webfinger.example/email/host']));
        $this->assertEquals(
            'smtp.example.com', $link['http://webfinger.example/email/host']
        );
    }
}
?>
