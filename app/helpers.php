<?php

use App\Helpers\PolicyHelper;

if (! function_exists('permission')) {
    /**
     * The permission name for a policy action on a model.
     *
     * permission('viewAny', ResearchIdea::class) === 'view_any_research_idea'
     */
    function permission(string $action, string|object $model): string
    {
        return PolicyHelper::name($action, $model);
    }
}
