<?php

class Game
{
    public $name;
    public $time = 0;

    public $logs = [];
    public $types = [];
    public $stats = [];
    public $teams = [];
    public $labels = [];
    public $actors = [];
    public $states = [];
    public $deaths = [];
    public $weapons = [];
    public $threats = [];
    public $targets = [];
    public $supplies = [];

    function __construct($name)
    {
        $this->name = $name;
    }

    function bar($label, $value, $total, $size = 20)
    {
        $diff = $total - $value;

        $a = floor(($value * $size) / 100);
        $b = floor(($diff * $size) / 100);
        if ($a + $b != $size) $b++;

        $bar = "{$label}: ";
        for ($i = 0; $i < $a; $i++) $bar .= "▓";
        for ($i = 0; $i < $b; $i++) $bar .= "░";
        $bar .= " {$value}/{$total} \n";

        return $bar;
    }

    function ready($actor)
    {
        foreach ($actor->stats as $stat) {
            if ($stat->value < 1) {
                return false;
                break;
            }
        }
        return true;
    }

    function input($message)
    {
        echo $message . "\n";
        $input = fopen('php://stdin', 'r');
        $line = fgets($input);
        return trim($line);
    }

    function clear()
    {
        system('clear');
        return $this;
    }

    function label($id, $text, $limit = null)
    {
        $this->labels[$id] = new Label($text, $limit);
        return $this;
    }

    function log($text)
    {
        $date = date('Y-m-d H:i:s');
        $text = "[$date]: $text";
        $this->logs[] = $text;
    }

    function ui()
    {
        $this->clear();

        foreach ($this->labels as $id => $label) {
            if (is_null($label->limit)) {
                echo "{$label->text}";
            } else {
                if ($label->limit > 0) {
                    echo "{$label->text}";
                    $label->limit -= 1;
                } else {
                    unset($this->labels[$id]);
                }
            }
        }
    }

    function recover($actor)
    {
        foreach ($actor->stats as $key => $stat) {
            if ($stat->value < 20) {
                if (isset($actor->supplies[$key])) {
                    if (count($actor->supplies[$key]) > 0) {
                        $supply = $actor->supplies[$key][0];
                        unset($actor->supplies[$key][0]);
                        $actor->stats[$key]->add($supply->value);
                        $actor->supplies[$key] = array_values($actor->supplies[$key]);
                        $this->log("{$actor->name} used {$supply->name} and recovered {$supply->value} of " . strtoupper($key));
                    }
                }
            }
        }
    }

    function sleep($ms)
    {
        usleep($ms * 1000);
    }

    function run()
    {
    }
}
