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

namespace XRD\Loader;

use XRD\Document;
use XRD\PropertyAccess;
use XRD\Element\Link;
use XRD\Element\Property;
use XRD\Loader\LoaderException;

/**
 * Loads XRD data from an XML file
 *
 * @category XML
 * @package  XML_XRD
 * @author   Christian Weiske <cweiske@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/XML_XRD
 */
class XML
{
    /**
     * Data storage the XML data get loaded into
     *
     * @var Document
     */
    protected $xrd;

    /**
     * XRD 1.0 namespace
     */
    const NS_XRD = 'http://docs.oasis-open.org/ns/xri/xrd-1.0';

    /**
     * Init object with xrd object
     *
     * @param Document $xrd Data storage the XML data get loaded into
     */
    public function __construct(Document $xrd)
    {
        $this->xrd = $xrd;
    }

    /**
     * Loads the contents of the given file
     *
     * @param string $file Path to an XRD file
     *
     * @return void
     *
     * @throws LoaderException When the XML is invalid or cannot be loaded
     */
    public function loadFile($file)
    {
        $old = libxml_use_internal_errors(true);
        $x = simplexml_load_file($file);
        libxml_use_internal_errors($old);
        if ($x === false) {
            throw new LoaderException(
                'Error loading XML file: ' . libxml_get_last_error()->message,
                LoaderException::LOAD
            );
        }
        return $this->load($x);
    }

    /**
     * Loads the contents of the given string
     *
     * @param string $xml XML string
     *
     * @return void
     *
     * @throws LoaderException When the XML is invalid or cannot be loaded
     */
    public function loadString($xml)
    {
        if ($xml == '') {
            throw new LoaderException(
                'Error loading XML string: string empty',
                LoaderException::LOAD
            );
        }
        $old = libxml_use_internal_errors(true);
        $x = simplexml_load_string($xml);
        libxml_use_internal_errors($old);
        if ($x === false) {
            throw new LoaderException(
                'Error loading XML string: ' . libxml_get_last_error()->message,
                LoaderException::LOAD
            );
        }
        return $this->load($x);
    }

    /**
     * Loads the XML element into the classes' data structures
     *
     * @param object $x XML element containing the whole XRD document
     *
     * @return void
     *
     * @throws LoaderException When the XML is invalid
     */
    public function load(\SimpleXMLElement $x)
    {
        $ns = $x->getDocNamespaces();
        if ($ns[''] !== self::NS_XRD) {
            throw new LoaderException(
                'Wrong document namespace', LoaderException::DOC_NS
            );
        }
        if ($x->getName() != 'XRD') {
            throw new LoaderException(
                'XML root element is not "XRD"', LoaderException::DOC_ROOT
            );
        }

        if (isset($x->Subject)) {
            $this->xrd->subject = (string)$x->Subject;
        }
        foreach ($x->Alias as $xAlias) {
            $this->xrd->aliases[] = (string)$xAlias;
        }

        foreach ($x->Link as $xLink) {
            $this->xrd->links[] = $this->loadLink($xLink);
        }

        $this->loadProperties($this->xrd, $x);

        if (isset($x->Expires)) {
            $this->xrd->expires = strtotime($x->Expires);
        }

        $xmlAttrs = $x->attributes('http://www.w3.org/XML/1998/namespace');
        if (isset($xmlAttrs['id'])) {
            $this->xrd->id = (string)$xmlAttrs['id'];
        }
    }

    /**
     * Loads the Property elements from XML
     *
     * @param object $store Data store where the properties get stored
     * @param object $x     XML element
     *
     * @return boolean True when all went well
     */
    protected function loadProperties(PropertyAccess $store, \SimpleXMLElement $x) {
        foreach ($x->Property as $xProp) {
            $store->properties[] = $this->loadProperty($xProp);
        }
    }

    /**
     * Create a link element object from XML element
     *
     * @param object $x XML link element
     *
     * @return Link Created link object
     */
    protected function loadLink(\SimpleXMLElement $x)
    {
        $link = new Link();
        foreach (array('rel', 'type', 'href', 'template') as $var) {
            if (isset($x[$var])) {
                $link->$var = (string)$x[$var];
            }
        }

        foreach ($x->Title as $xTitle) {
            $xmlAttrs = $xTitle->attributes('http://www.w3.org/XML/1998/namespace');
            $lang = '';
            if (isset($xmlAttrs['lang'])) {
                $lang = (string)$xmlAttrs['lang'];
            }
            if (!isset($link->titles[$lang])) {
                $link->titles[$lang] = (string)$xTitle;
            }
        }
        $this->loadProperties($link, $x);

        return $link;
    }

    /**
     * Create a property element object from XML element
     *
     * @param object $x XML property element
     *
     * @return Property Created link object
     */
    protected function loadProperty(\SimpleXMLElement $x)
    {
        $prop = new Property();
        if (isset($x['type'])) {
            $prop->type = (string)$x['type'];
        }
        $s = (string)$x;
        if ($s != '') {
            $prop->value = $s;
        }

        return $prop;
    }
}
?>
