<?php

class Move
{
    public $name;
    public $type;
    public $data;

    function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }
}
