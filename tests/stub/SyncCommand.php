<?php

namespace Tests\Stub;

/**
 * Inherit from the command to stub the Good Companies API request and logging
 */
class SyncCommand extends \App\Console\Commands\SyncCompaniesFromGoodCompanies
{
    public $fakeCompanies = [];

    protected function log($details)
    {
        // Do nothing
    }

    protected function getCompanies()
    {
        // Decoding an encoded version of the companies array turns the companies into objects
        return json_decode(json_encode($this->fakeCompanies));
    }
}
