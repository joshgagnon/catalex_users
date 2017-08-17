<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Organisation;

class RemoveOrgRelationshipFromServiceReqistrations extends Migration
{
    public function up()
    {
        $orgs = Organisation::get();

        foreach ($orgs as $org) {
            $orgServices = $org->services()->get();

            $orgUsers = $org->members()->get();

            foreach ($orgUsers as $orgUser) {
                $orgUser->services()->sync($orgServices);
            }

            $org->services()->sync([]);
        }

        Schema::table('service_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'organisation_id',
                'price_in_cents',
                'access_level',
            ]);
        });
    }

    public function down()
    {
        // Let's just forget what we did above, we ain't ever getting that back
    }
}
