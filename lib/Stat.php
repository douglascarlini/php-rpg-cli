<?php

class Stat
{
    public $name;
    public $value = 100;
    public $total = 100;

    function __construct($name)
    {
        $this->name = $name;
    }

    function add($value)
    {
        $value = $this->value + $value;
        $this->value = $value < 1 ? 0 : $value;
    }
}
