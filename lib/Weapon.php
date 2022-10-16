<?php

class Weapon
{
    public $name;
    public $data;
    public $moves = [];

    function __construct($name, $data)
    {
        $this->name = $name;
        $this->data = $data;
    }
}
