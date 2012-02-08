<?php
/**
 * Part of XML_XRD
 *
 * PHP version 5
 *
 * @category XML
 * @package  XML_XRD
 * @author   Christian Weiske <cweiske@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL
 * @link     http://pear.php.net/package/XML_XRD
 */

/**
 * Generate XML from a XML_XRD object.
 *
 * @category XML
 * @package  XML_XRD
 * @author   Christian Weiske <cweiske@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/XML_XRD
 */
class XML_XRD_Serializer_XML
{
    protected $xrd;

    public function __construct(XML_XRD $xrd)
    {
        $this->xrd = $xrd;
    }

    public function __toString()
    {
        $x = new XMLWriter();
        $x->openMemory();
        $x->startDocument('1.0', 'UTF-8');
        $x->startElement('XRD');
        $x->writeAttribute('xmlns', 'http://docs.oasis-open.org/ns/xri/xrd-1.0');

        $x->writeElement('Subject', $this->xrd->subject);
        foreach ($this->xrd->aliases as $alias) {
            $x->writeElement('Alias', $alias);
        }
        if ($this->xrd->expires !== null) {
            $x->writeElement('Expires', gmdate('Y-m-d\TH:i:s\Z', $this->xrd->expires));
        }

        foreach ($this->xrd->properties as $property) {
            $this->writeProperty($x, $property);
        }

        foreach ($this->xrd->links as $link) {
            $x->writeElement('Link');
            $x->writeAttribute('rel', $link->rel);
            if ($link->type !== null) {
                $x->writeAttribute('type', $link->type);
            }
            if ($link->href !== null) {
                $x->writeAttribute('href', $link->href);
            }
            //template only when no href
            if ($link->template !== null && $link->href === null) {
                $x->writeAttribute('template', $link->template);
            }

            foreach ($link->titles as $lang => $value) {
                $x->writeElement('Title', $value);
                if ($lang) {
                    $x->writeAttributeNS(
                        'xml', 'lang', 'http://www.w3.org/XML/1998/namespace',
                        'true'
                    );
                }
            }
            foreach ($link->properties as $property) {
                $this->writeProperty($x, $property);
            }
        }

        $x->endElement();
        $x->endDocument();
        return $x->flush();
    }

    protected function writeProperty(
        XMLWriter $x, XML_XRD_Element_Property $property
    ) {
        if ($property->value === null) {
            $x->writeElement('Property');
            $x->writeAttributeNS(
                'xsi', 'nil', 'http://www.w3.org/2001/XMLSchema-instance',
                'true'
            );
        } else {
            $x->writeElement('Property', $property->value);
        }
        $x->writeAttribute('type', $property->type);
    }
}

?>