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

namespace XRD\Serializer;

use XRD\XRDException;

/**
 * XML_XRD exception that's thrown when saving an XRD file fails.
 *
 * @category XML
 * @package  XML_XRD
 * @author   Christian Weiske <cweiske@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/XML_XRD
 */
class SerializerException extends \Exception implements XRDException
{
    /**
     * Unsupported XRD file/string type (no serializer)
     */
    const NO_SERIALIZER = 10;
}
?>
