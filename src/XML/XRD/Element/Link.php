<?php

class XML_XRD_Element_Link
{
    /**
     * Link relation
     *
     * @var string
     */
    public $rel;

    /**
     * Link type (MIME type)
     *
     * @var string
     */
    public $type;

    /**
     * Link URL
     *
     * @var string
     */
    public $href;

    /**
     * Link URL template
     *
     * @var string
     */
    public $template;



    public function __construct(SimpleXMLElement $x)
    {
        foreach (array('rel', 'type', 'href', 'template') as $var) {
            if (isset($x[$var])) {
                $this->$var = (string)$x[$var];
            }
        }
    }
}

?>