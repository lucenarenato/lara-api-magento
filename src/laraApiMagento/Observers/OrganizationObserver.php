<?php

namespace App\Observers;


use App\Jobs\MagentoUpdateCustomerJob;
use App\Organization;

class OrganizationObserver
{

    use \App\Traits\MagentoObserverTrait;
    use \App\Traits\ObserverTrait;
    /**
     * Handle the organization "created" event.
     *
     * @param  \App\Organization  $organization
     * @return void
     */
    public function created(Organization $organization)
    {
        if ($this->isMagentoSyncActive()) {
            dispatch(new MagentoUpdateCustomerJob($organization->id));
        }
    }

    public function saved(Organization $organization)
    {
        if ($this->isMagentoSyncActive()) {
            dispatch(new MagentoUpdateCustomerJob($organization->id));
        }
    }


    /**
     * Handle the organization "updated" event.
     *
     * @param  \App\Organization  $organization
     * @return void
     */
    public function updated(Organization $organization)
    {
        if ($this->isMagentoSyncActive()) {
            dispatch(new MagentoUpdateCustomerJob($organization->id));
        }

        if ($this->fieldsChanged($organization, ['temporary_email'])) {
            if ((isset($organization['temporary_email'])) || (($organization['temporary_email']) != null)) {
                $organization->SendMailConfirmNewEmailOrganization($organization);
            }
        }
    }

    /**
     * Handle the organization "deleted" event.
     *
     * @param  \App\Organization  $organization
     * @return void
     */
    public function deleted(Organization $organization)
    {
        //
    }

    /**
     * Handle the organization "restored" event.
     *
     * @param  \App\Organization  $organization
     * @return void
     */
    public function restored(Organization $organization)
    {
        //
    }

    /**
     * Handle the organization "force deleted" event.
     *
     * @param  \App\Organization  $organization
     * @return void
     */
    public function forceDeleted(Organization $organization)
    {
        //
    }
}
