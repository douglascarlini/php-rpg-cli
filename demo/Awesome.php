<?php

class Awesome extends Game
{
    function report($id, $actor = null)
    {
        if (is_null($actor)) {
            unset($this->labels["report-{$id}"]);
        } else {
            $bars = [];
            $datas = [];
            $supplies = [];

            foreach ($actor->stats as $key => $stat)
                $bars[] = $this->bar(strtoupper($key), $stat->value, $stat->total);

            foreach ($actor->data as $key => $data)
                $datas[] = "{$data->name}: $data->value";

            foreach ($actor->supplies as $key => $supply)
                $supplies[] = strtoupper($key) . ": " . count($supply);

            $text = "\n# {$actor->name} [{$actor->type}]\n";
            $text .= implode(" / ", $datas) . "\n";
            $text .= "Supplies: " . implode(" / ", $supplies) . "\n";
            $text .= "\n" . implode("", $bars);

            $this->label("report-{$id}", $text);
        }
    }

    function run()
    {
        $this->label('welcome', "### Welcome to {$this->name}! Have fun! ###\n\n");
        $actor = $this->actors[0];
        $this->clear();

        while (true) {

            $this->label('state', "{$actor->name} is {$actor->states[$actor->state]->name}...\n");

            if ($actor->stats['lp']->value < 1) $actor->state = 'dead';

            $this->report($actor->name, $actor);

            if ($actor->state == 'dead') {
                $this->label("end", "\n### {$actor->name} LOSE! ###\n");
                $this->report($actor->name, $actor);
                $this->log("{$actor->name} lose");
                $this->ui();
                break;
            } else {

                $total = count($this->threats);

                if ($total < 1) {
                    $this->label("end", "\n### {$actor->name} WINS! ###\n");
                    $this->report($actor->name, $actor);
                    $this->log("{$actor->name} wins");
                    $this->ui();
                    break;
                }

                if ($actor->state == 'duel')
                    $this->duel();

                if ($actor->state == 'idle')
                    $this->idle();

                if ($actor->state == 'walk')
                    $this->walk();

                if ($actor->state != 'duel' && rand(0, 9) > 8) {
                    $pos = rand(0, $total - 1);
                    $threat = $this->threats[$pos];
                    $this->log("Threat spotted: {$threat->name} level {$threat->data['level']->value}");
                    $this->setState($actor, 'duel');
                    $actor->threats[] = $pos;
                }
            }

            $logs = array_reverse(array_slice($this->logs, -10));
            $logs = "\n" . implode("\n", $logs) . "\n";

            $this->ui();

            echo $logs;

            $this->sleep(rand(250, 500));
        }

        try {
            $logs = implode("\n", $this->logs);
            $f = fopen('game.log', 'w');
            fwrite($f, $logs);
            fclose($f);
        } catch (Exception $ex) {
            die($ex->getMessage());
        }
    }

    function duel()
    {
        $actor = $this->actors[0];
        $threat = $this->threats[$actor->threats[0]];
        if (rand(0, 9) > 7) $actor->weapon = rand(0, count($actor->weapons) - 1);

        $this->report($threat->name, $threat);

        if ($this->ready($actor)) {
            $weapon = $actor->weapons[$actor->weapon];
            $this->log("{$actor->name} attack {$threat->name} with {$weapon->name} level {$weapon->data['level']->value}");
            $actor->stats['mp']->add(-rand(0, $threat->data['level']->value));
            $actor->stats['sp']->add(-rand(0, $threat->data['level']->value));
            $damage = rand(1, $actor->data['level']->value + 10);
            $this->log("{$threat->name} receive {$damage} of damage");
            $threat->stats['lp']->add(-$damage);
            $this->log("{$threat->name} have {$threat->stats['lp']->value} of LP");
        }

        if ($this->ready($threat)) {
            $weapon = $threat->weapons[$threat->weapon];
            $this->log("{$threat->name} attack {$actor->name} with {$weapon->name} level {$weapon->data['level']->value}");
            $threat->stats['mp']->add(-rand(0, $threat->data['level']->value));
            $threat->stats['sp']->add(-rand(0, $threat->data['level']->value));
            $damage = rand(1, $threat->data['level']->value);
            $this->log("{$actor->name} receive {$damage} of damage!");
            $actor->stats['lp']->add(-$damage);
            $this->log("{$actor->name} have {$actor->stats['lp']->value} of LP");
        }

        if ($threat->stats['lp']->value < 1) $this->setState($threat, 'dead');

        $this->recover($actor);
        $this->recover($threat);

        $this->dead($threat);
    }

    function setState($actor, $state)
    {
        if ($actor->state != $state) {
            $actor->state = $state;
            $this->log("{$actor->name} now is {$actor->states[$state]->name}");
        }
    }

    function dead($threat)
    {
        $actor = $this->actors[0];

        if ($threat->state == 'dead') {
            unset($this->threats[0]);
            unset($actor->threats[0]);
            $actor->data['level']->add(1);
            $this->setState($actor, 'idle');
            $this->report($threat->name, null);
            $this->threats = array_values($this->threats);
            $actor->threats = array_values($actor->threats);
            $actor->weapons[$actor->weapon]->data['level']->add(1);
            $this->log("{$threat->name} was defeated by {$actor->name}");
        }
    }

    function idle()
    {
        $actor = $this->actors[0];
        $actor->stats['sp']->add(1);
        if ($actor->stats['sp']->value > 99)
            $this->setState($actor, 'walk');
        else
            $this->setState($actor, 'idle');
    }

    function walk()
    {
        $actor = $this->actors[0];
        if ($actor->stats['sp']->value > 0)
            $actor->stats['sp']->add(-1);
        else
            $this->setState($actor, 'idle');
    }

    function newData()
    {
        return ['level' => new Data('Level', 1), 'speed' => new Data('Speed', 1)];
    }

    function newStats()
    {
        return ['lp' => new Stat('Life'), 'mp' => new Stat('Magic'), 'sp' => new Stat('Stamina')];
    }

    function newStates()
    {
        return ['idle' => new State('Idle'), 'dead' => new Stat('Dead'), 'walk' => new Stat('Walking'), 'duel' => new Stat('Dueling')];
    }

    function newSupplies()
    {
        return [
            'lp' => [new Supply('Apple', +10), new Supply('Apple', +10), new Supply('Potion of Life', +50)],
            'mp' => [new Stat('Magic 1', +10), new Stat('Magic 2', +20), new Stat('Magic 3', +50)],
        ];
    }

    function createThreat($name, $type, $weapon)
    {
        $threat = new Actor($name, $type, $this->newData());
        $threat->weapons[] = new Weapon($weapon, $this->newData());
        $threat->supplies = $this->newSupplies();
        $threat->states = $this->newStates();
        $threat->stats = $this->newStats();
        $threat->state = 'idle';
        $threat->weapon = 0;

        $this->threats[] = $threat;
    }
}
