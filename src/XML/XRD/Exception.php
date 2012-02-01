<?php

class XML_XRD_Exception extends Exception
{
    /**
     * The document namespace is not the XRD 1.0 namespace
     */
    const DOC_NS = 10;

    /**
     * The document root element is not XRD
     */
    const DOC_ROOT = 11;

    /**
     * Error loading the XML
     */
    const LOAD_XML = 12;
}

?>