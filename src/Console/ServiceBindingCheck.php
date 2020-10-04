<?php

namespace Fanmade\ServiceBinding\Console;

use Illuminate\Console\Command;

class ServiceBindingCheck extends Command
{
    protected $signature = 'fanmade:check-bindings';

    protected $description = 'Check service binding configuration';

    public function handle()
    {
        // TODO: add functionality
        $this->info('Check successfull');
        return 0;
    }
}
