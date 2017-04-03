<?php

namespace App\Library;

use App\BillingItem;

class BillingItemSummariser
{
    protected $billable;

    function __construct($billable)
    {
        $this->billable = $billable;
    }

    public function summarise()
    {
        $billingItems = BillingItem::active();

        if ($this->billable->billableType() === 'organisation') {
            $memberIds = $this->billable->members()->get()->pluck('id')->toArray();

            $billingItems->where('organisation_id', $this->billable->id);
            $billingItems->orWhereIn('user_id', $memberIds);
        }
        else {
            $billingItems->where('user_id', $this->billable->id);
        }

        $billingItems = $billingItems->with(['service', 'user', 'organisation'])
            ->orderBy('billing_items.service_id', 'ASC')
            ->get();

        return $billingItems;
    }
}