<?php

use App\BillingDetail;
use App\CardDetail;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCardDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_details', function (Blueprint $table) {
            $table->increments('id');

            $table->text('card_token');
            $table->text('expiry_date');
            $table->text('masked_card_number')->nullable();

            $table->timestamps();
        });

        Schema::table('billing_details', function (Blueprint $table) {
            $table->integer('card_detail_id')->unsigned()->nullable();
            $table->foreign('card_detail_id')->references('id')->on('card_details')->onDelete('cascade');
        });

        $billingDetails = BillingDetail::get();

        foreach ($billingDetails as $billingDetail) {
            $cardDetails = CardDetail::create([
                'card_token' => $billingDetail->dps_billing_token,
                'expiry_date' => $billingDetail->expiry_date,
                'masked_card_number' => $billingDetail->masked_card_number,
            ]);

            $billingDetail->update(['card_detail_id' => $cardDetails->id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('billing_details', function (Blueprint $table) {
            $table->dropColumn(['card_detail_id']);

            $table->text('dps_billing_token')->nullable();
            $table->text('expiry_date')->nullable();
            $table->text('masked_card_number')->nullable();
        });

        Schema::drop('card_details');
    }
}
