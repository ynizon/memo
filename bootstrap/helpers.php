<?php

use Illuminate\Support\Facades\Auth;

function getReminders() : \Illuminate\Database\Eloquent\Collection
{
    return Auth::user()->getReminders();
}

function formatDate($date)
{
    return \Carbon\Carbon::parse($date)->format('d/m/Y');
}

function formatDateUK($date)
{
    return \Carbon\Carbon::parse($date)->format('Y-m-d');
}

function is_current_route($routeName)
{
    return request()->routeIs($routeName) ? 'active' : '';
}


function in_array_r($needle, $haystack, $strict = false)
{
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}


function getCategoriesArray($parent, $child = null)
{
    $categories = array(
        'dashboard', 'tables', 'wallet', 'RTL',

        'laravel-examples' => array(
            'user-profile',
            'users-management',
        ),
    );

    if ($child)
        return $categories[$parent][$child];
    else
        return $categories[$parent];
}
