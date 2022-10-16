<?php

class Type
{
    public $name;
    public $weak = [];

    function __construct($name, $weak = [])
    {
        $this->name = $name;
        $this->weak = $weak;
    }
}
