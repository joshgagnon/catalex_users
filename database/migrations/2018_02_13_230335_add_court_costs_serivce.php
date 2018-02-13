<?php

use App\Service;
use Illuminate\Database\Migrations\Migration;

class AddCourtCostsSerivce extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $serviceExists = Service::where('name', 'Court Costs')->exists();

        if (!$serviceExists) {
            Service::create(['name' => 'Court Costs', 'is_paid_service' => true]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
