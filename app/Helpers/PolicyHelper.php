<?php

namespace App\Helpers;

use Illuminate\Support\Str;

/**
 * One way of naming a permission, used everywhere.
 *
 * A policy action and the model it is about, both snake cased and joined:
 * `viewAny` + `ResearchIdea` becomes `view_any_research_idea`. Policies, the
 * seeder and the admin all go through here, so a name cannot be spelled one
 * way in one place and another way in the next.
 */
class PolicyHelper
{
    /** Standard actions every model gets, unless it asks for others. */
    public const ACTIONS = ['viewAny', 'view', 'create', 'update', 'delete'];

    /**
     * @param  string|object  $model  a class name, or an instance of one
     */
    public static function name(string $action, string|object $model): string
    {
        $modelName = class_basename(is_object($model) ? $model::class : $model);

        return Str::snake($action).'_'.Str::snake($modelName);
    }

    /** The same thing, named the way a call site reads best. */
    public static function can(string $action, string|object $model): string
    {
        return static::name($action, $model);
    }

    /**
     * Every permission name for one model.
     *
     * @param  array<int, string>|null  $actions
     * @return array<int, string>
     */
    public static function forModel(string|object $model, ?array $actions = null): array
    {
        return array_map(
            fn (string $action) => static::name($action, $model),
            $actions ?? static::ACTIONS,
        );
    }
}
