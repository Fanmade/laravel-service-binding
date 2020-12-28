<?php

namespace Fanmade\ServiceBinding\Console;

use Illuminate\Console\Command;

class AddBinding extends Command
{
    protected $signature = 'fanmade:add-binding';

    protected $description = 'Add service binding configuration entry';

    public function handle()
    {
        $interface = $this->anticipate(
            'Please choose the interface:',
            function ($input) {
                return $this->anticipateInterface($input);
            }
        );
        $this->comment("Chosen interface: '{$interface}'");

        $this->info('Success!');
        return 0;
    }

    protected function anticipateInterface(string $input): array
    {
        $interfaces = get_declared_interfaces();
        if (empty($input)) {
            return $interfaces;
        }
        $input = mb_strtolower($input);
        foreach ($interfaces as $interface) {
            if (0 === strpos($interface, $input)) {
                $results[] = $interface;
            } elseif (0 === strpos(mb_strtolower($interface), $input)) {
                $results[] = $interface;
            } elseif (0 === strpos(mb_strtolower(str_replace('\\', '', $interface)), $input)) {
                $results[] = $interface;
            } elseif (str_match_humps($input, $interface)) {
                $results[] = $interface;
            }
        }

        return empty($results) ? $interfaces : $results;
    }
}
