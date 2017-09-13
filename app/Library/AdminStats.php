<?php

namespace App\Library;

use DB;

class AdminStats
{
    public static function itemCount(string $itemType)
    {
        $query = '
            WITH paying_users AS (
                SELECT id, billing_detail_id, organisation_id FROM 
                (
                    SELECT u.id, billing_detail_id, organisation_id, (SELECT array_agg(name) FROM role_user AS ur LEFT OUTER JOIN roles AS r ON r.id = ur.role_id WHERE ur.user_id = u.id)  as roles FROM users AS u
                    WHERE free != true
                ) AS q
                WHERE NOT (\'global_admin\' = ANY(roles))
            )
            
            SELECT count,
            CASE 
                WHEN user_period = \'monthly\' THEN \'user_monthly\'
                WHEN user_period = \'annually\' THEN \'user_annually\'
                WHEN org_period = \'monthly\' THEN \'org_monthly\'
                WHEN org_period = \'annually\' THEN \'org_annually\'
            END condition
            FROM (
                SELECT COUNT(*), ubd.period user_period, obd.period org_period FROM billing_items
            
                LEFT OUTER JOIN paying_users pu ON billing_items.user_id = pu.id
                LEFT OUTER JOIN organisations o ON pu.organisation_id = o.id
                LEFT OUTER JOIN billing_details ubd ON pu.billing_detail_id = ubd.id AND pu.organisation_id IS NULL
                LEFT OUTER JOIN billing_details obd ON o.billing_detail_id = obd.id
                
                WHERE
                    billing_items.active = true
                    AND (ubd.period IS NOT NULL OR obd.period IS NOT NULL)
                    AND billing_items.item_type = :itemType
            
                GROUP BY user_period, org_period
            ) qq
        ';

        $result = DB::select($query, ['itemType' => $itemType]);

        return $result;
    }

}