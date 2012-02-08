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

    /**
     * Create new instance
     *
     * @param XML_XRD $xrd XRD instance to convert to XML
     */
    public function __construct(XML_XRD $xrd)
    {
        $this->xrd = $xrd;
    }

    /**
     * Generate XML.
     *
     * @return string Full XML code
     */
    public function __toString()
    {
        $x = new XMLWriter();
        $x->openMemory();
        $x->startDocument('1.0', 'UTF-8');
        $x->setIndent(true);
        $x->startElement('XRD');
        $x->writeAttribute('xmlns', 'http://docs.oasis-open.org/ns/xri/xrd-1.0');
        $x->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        $x->writeElement('Subject', $this->xrd->subject);
        foreach ($this->xrd->aliases as $alias) {
            $x->writeElement('Alias', $alias);
        }
        if ($this->xrd->expires !== null) {
            $x->writeElement(
                'Expires', gmdate('Y-m-d\TH:i:s\Z', $this->xrd->expires)
            );
        }

        foreach ($this->xrd->properties as $property) {
            $this->writeProperty($x, $property);
        }

        foreach ($this->xrd->links as $link) {
            $x->startElement('Link');
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
                $x->startElement('Title');
                if ($lang) {
                    $x->writeAttribute('xml:lang', $lang);
                }
                $x->text($value);
                $x->endElement();
            }
            foreach ($link->properties as $property) {
                $this->writeProperty($x, $property);
            }
            $x->endElement();
        }

        $x->endElement();
        $x->endDocument();
        return $x->flush();
    }

    /**
     * Write a property in the XMLWriter stream output
     *
     * @param XMLWriter                $x        Writer object to write to
     * @param XML_XRD_Element_Property $property Property to write
     *
     * @return void
     */
    protected function writeProperty(
        XMLWriter $x, XML_XRD_Element_Property $property
    ) {
        $x->startElement('Property');
        $x->writeAttribute('type', $property->type);
        if ($property->value === null) {
            $x->writeAttribute('xsi:nil', 'true');
        } else {
            $x->text($property->value);
        }
        $x->endElement();
    }
}

?>