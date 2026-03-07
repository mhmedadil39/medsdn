<?php

namespace App\Console\Commands;

use Webkul\Faker\Commands\Console\Faker;

class MedsdnFake extends Faker
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medsdn:fake';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates fake records for testing';
}
