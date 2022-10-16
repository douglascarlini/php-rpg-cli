<?php

class Actor
{
    public $name;
    public $type;
    public $state;
    public $weapon;
    public $moves = [];
    public $stats = [];
    public $states = [];
    public $weapons = [];
    public $threats = [];

    function __construct($name, $type, $data)
    {
        $this->name = $name;
        $this->type = $type;
        $this->data = $data;
    }
}
