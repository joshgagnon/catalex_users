<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCourtCostsToBillingItemTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('billing_items', function (Blueprint $table) {
            DB::statement("ALTER TABLE billing_items DROP CONSTRAINT billing_items_item_type_check;");
            DB::statement("ALTER TABLE billing_items ADD CONSTRAINT billing_items_item_type_check CHECK (item_type::TEXT = ANY (ARRAY['gc_company'::character varying, 'catalex_sign_subscription'::character varying, 'court_costs_subscription'::character varying]::text[]))");
        });
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
