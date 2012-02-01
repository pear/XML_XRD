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
 * Property element in a XRD document.
 *
 * The <XRD> root element as well as <Link> tags may have <Property> children.
 *
 * @category XML
 * @package  XML_XRD
 * @author   Christian Weiske <cweiske@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/XML_XRD
 */
class XML_XRD_Element_Property
{
    /**
     * Value of the property.
     *
     * @var string|null
     */
    public $value;

    /**
     * Type of the propery.
     *
     * @var string
     */
    public $type;

    /**
     * Create a new instance and load data from the XML element
     *
     * @param object $x SimpleXMLElement representing the <Property>
     */
    public function __construct(SimpleXMLElement $x)
    {
        if (isset($x['type'])) {
            $this->type = (string)$x['type'];
        }
        $s = (string)$x;
        if ($s != '') {
            $this->value = $s;
        }
    }
}

?>