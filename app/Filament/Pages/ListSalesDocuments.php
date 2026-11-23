<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Proposals\ProposalResource;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\ListRecords;

abstract class ListSalesDocuments extends ListRecords
{
    protected static ?string $title = 'Proposals & Work Orders';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public function getSubNavigation(): array
    {
        $items = [];

        foreach ([
            'Proposals' => ProposalResource::class,
            'Work Orders' => WorkOrderResource::class,
        ] as $label => $resource) {
            if ($resource::canViewAny()) {
                $items[] = NavigationItem::make($label)
                    ->url($resource::getUrl())
                    ->isActiveWhen(fn () => static::getResource() === $resource);
            }
        }

        return $items;
    }
}
