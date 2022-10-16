<?php

class Label
{
    public $text;
    public $limit;

    function __construct($text, $limit = null)
    {
        $this->text = $text;
        $this->limit = $limit;
    }
}
