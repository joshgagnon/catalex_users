<?php

use App\InvoiceRecipient;
use App\User;
use Illuminate\Database\Migrations\Migration;

class AddOrgAdminsToInvoiceRecipients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $users = User::get();

        foreach ($users as $user) {
            $isOrgAdmin = $user->organisation_id && $user->hasRole('organisation_admin');

            if ($isOrgAdmin) {
                InvoiceRecipient::forceCreate([
                    'organisation_id' => $user->organisation_id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]);
            }
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
