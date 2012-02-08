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

require_once 'XML/XRD/PropertyAccess.php';
require_once 'XML/XRD/Element/Link.php';
require_once 'XML/XRD/Exception.php';

/**
 * Main class used to load XRD documents from string or file.
 *
 * After loading the file, access to links is possible with get() and getAll(),
 * as well as foreach-iterating over the XML_XRD object.
 *
 * Property access is possible with getProperties() and array access (foreach)
 * on the XML_XRD object.
 *
 * Verification that the subject/aliases match the requested URL can be done with
 * describes().
 *
 * @category XML
 * @package  XML_XRD
 * @author   Christian Weiske <cweiske@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/XML_XRD
 */
class XML_XRD extends XML_XRD_PropertyAccess implements IteratorAggregate
{
    /**
     * XRD subject
     *
     * @var string
     */
    public $subject;

    /**
     * Array of subject alias strings
     *
     * @var array
     */
    public $aliases = array();

    /**
     * Array of link objects
     *
     * @var array
     */
    public $links = array();

    /**
     * Unix timestamp when the document expires.
     * NULL when no expiry date set.
     *
     * @var integer|null
     */
    public $expires;

    /**
     * xml:id of the XRD document
     *
     * @var string|null
     */
    public $id;

    /**
     * XRD 1.0 namespace
     */
    const NS_XRD = 'http://docs.oasis-open.org/ns/xri/xrd-1.0';



    /**
     * Loads the contents of the given file
     *
     * @param string $file Path to an XRD file
     *
     * @return void
     *
     * @throws XML_XRD_Exception When the XML is invalid or cannot be loaded
     */
    public function loadFile($file)
    {
        $old = libxml_use_internal_errors(true);
        $x = simplexml_load_file($file);
        libxml_use_internal_errors($old);
        if ($x === false) {
            throw new XML_XRD_Exception(
                'Error loading XML file: ' . libxml_get_last_error()->message,
                XML_XRD_Exception::LOAD_XML
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
     * @throws XML_XRD_Exception When the XML is invalid or cannot be loaded
     */
    public function loadString($xml)
    {
        if ($xml == '') {
            throw new XML_XRD_Exception(
                'Error loading XML string: string empty',
                XML_XRD_Exception::LOAD_XML
            );
        }
        $old = libxml_use_internal_errors(true);
        $x = simplexml_load_string($xml);
        libxml_use_internal_errors($old);
        if ($x === false) {
            throw new XML_XRD_Exception(
                'Error loading XML string: ' . libxml_get_last_error()->message,
                XML_XRD_Exception::LOAD_XML
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
     * @throws XML_XRD_Exception When the XML is invalid
     */
    protected function load(SimpleXMLElement $x)
    {
        $ns = $x->getDocNamespaces();
        if ($ns[''] !== self::NS_XRD) {
            throw new XML_XRD_Exception(
                'Wrong document namespace', XML_XRD_Exception::DOC_NS
            );
        }
        if ($x->getName() != 'XRD') {
            throw new XML_XRD_Exception(
                'XML root element is not "XRD"', XML_XRD_Exception::DOC_ROOT
            );
        }

        if (isset($x->Subject)) {
            $this->subject = (string)$x->Subject;
        }
        foreach ($x->Alias as $xAlias) {
            $this->aliases[] = (string)$xAlias;
        }

        foreach ($x->Link as $xLink) {
            $this->links[] = new XML_XRD_Element_Link($xLink);
        }

        $this->loadProperties($x);

        if (isset($x->Expires)) {
            $this->expires = strtotime($x->Expires);
        }

        $xmlAttrs = $x->attributes('http://www.w3.org/XML/1998/namespace');
        if (isset($xmlAttrs['id'])) {
            $this->id = (string)$xmlAttrs['id'];
        }
    }

    /**
     * Checks if the XRD document describes the given URI.
     *
     * This should always be used to make sure the XRD file
     * is the correct one for e.g. the given host, and not a copycat.
     *
     * Checks against the subject and aliases
     *
     * @param string $uri An URI that the document is expected to describe
     *
     * @return boolean True or false
     */
    public function describes($uri)
    {
        if ($this->subject == $uri) {
            return true;
        }
        foreach ($this->aliases as $alias) {
            if ($alias == $uri) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the link with highest priority for the given relation and type.
     *
     * @param string  $rel          Relation name
     * @param string  $type         MIME Type
     * @param boolean $typeFallback When true and no link with the given type
     *                              could be found, the best link without a
     *                              type will be returned
     *
     * @return XML_XRD_Element_Link Link object or NULL if none found
     */
    public function get($rel, $type = null, $typeFallback = true)
    {
        $links = $this->getAll($rel, $type, $typeFallback);
        if (count($links) == 0) {
            return null;
        }

        return $links[0];
    }


    /**
     * Get all links with the given relation and type, highest priority first.
     *
     * @param string  $rel          Relation name
     * @param string  $type         MIME Type
     * @param boolean $typeFallback When true and no link with the given type
     *                              could be found, the best link without a
     *                              type will be returned
     *
     * @return array Array of XML_XRD_Element_Link objects
     */
    public function getAll($rel, $type = null, $typeFallback = true)
    {
        $links = array();
        $exactType = false;
        foreach ($this->links as $link) {
            if ($link->rel == $rel
                && ($type === null || $link->type == $type
                || $typeFallback && $link->type === null)
            ) {
                $links[]    = $link;
                $exactType |= $typeFallback && $type !== null
                    && $link->type == $type;
            }
        }
        if ($exactType) {
            //remove all links without type
            $exactlinks = array();
            foreach ($links as $link) {
                if ($link->type !== null) {
                    $exactlinks[] = $link;
                }
            }
            $links = $exactlinks;
        }
        return $links;
    }

    /**
     * Return the iterator object to loop over the links
     *
     * Part of the IteratorAggregate interface
     *
     * @return Traversable Iterator for the links
     */
    public function getIterator()
    {
        return new ArrayIterator($this->links);
    }
}

?>