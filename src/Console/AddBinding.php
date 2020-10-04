<?php

namespace Fanmade\ServiceBinding\Console;

use Illuminate\Console\Command;

class AddBinding extends Command
{
    protected $signature = 'fanmade:add-binding';

    protected $description = 'Add service binding configuration entry';

    public function handle()
    {
        // TODO: add functionality
        $this->info('Success!');
        return 0;
    }
}
