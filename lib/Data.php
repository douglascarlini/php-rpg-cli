<?php

class Data
{
    public $name;
    public $value;

    function __construct($name, $value = 0)
    {
        $this->value = $value;
        $this->name = $name;
    }

    function add($value)
    {
        $value = $this->value + $value;
        $this->value = $value < 1 ? 0 : $value;
    }
}
