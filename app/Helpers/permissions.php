<?php

if (! function_exists('allowed_user_routes')) {
    /**
     * Return the list of routes a non-admin user is allowed to access.
     * Keep this single source-of-truth for both middleware and views.
     *
     * @return array
     */
    function allowed_user_routes(): array
    {
        return [
            'dashboard',
            'sales.index',
            'sales.create',
            'sales.store',
            'sales.show',
            'sales.edit',
            'sales.update',
            'sales.destroy',
            'partners.index',
            'partners.create',
            'partners.store',
            'partners.show',
            'partners.edit',
            'partners.update',
            'partners.destroy',
            'profile.edit',
            'profile.update',
            'profile.destroy',
        ];
    }
}

if (! function_exists('user_can_access_route')) {
    /**
     * Check whether the current user can access a given route name.
     * Admins can access everything; guests cannot access anything.
     *
     * @param  string|null  $routeName
     * @return bool
     */
    function user_can_access_route(?string $routeName): bool
    {

        if (! auth()->check()) {
            return false;
        }



        $user = auth()->user();

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        if (is_null($routeName)) {
            return false;
        }

        return in_array($routeName, allowed_user_routes());
    }
}
