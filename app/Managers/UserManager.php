<?php

namespace App\Managers;

class UserManager
{
    public static function getGroups($groupsTmp, $limitDate, $previousDate) {
        $groups = [];

        foreach ($groupsTmp as $group) {
            $groups[$group->id] = ["last" =>0, "now" =>0, "total"=>0,
                'latest'=>'', 'name'=> $group->name, 'id'=> $group->id];
            foreach ($group->tasks() as $task) {
                if ($task->created_at >= $limitDate) {
                    $groups[$group->id]['now'] = $groups[$group->id]['now'] + $task->price;
                }
                if ($task->created_at >= $previousDate && $task->created_at < $limitDate) {
                    $groups[$group->id]['last'] = $groups[$group->id]['last'] + $task->price;
                }
                $groups[$group->id]['total'] = $groups[$group->id]['total'] + $task->price;
            }
        }

        return $groups;
    }

    public static function getTransactions($tasks, $limitDate, $previousDate) {
        $transactions = [];
        foreach ($tasks as $task){
            if (!isset($transactions[$task->category_id])){
                $transactions[$task->category_id] = ["last" =>0, "now" =>0, "total"=>0,
                    'category' =>$task->category, 'latest'=>$task->created_at];
            }

            if ($task->created_at >= $limitDate) {
                $transactions[$task->category_id]['now'] = $transactions[$task->category_id]['now'] + $task->price;
            }
            if ($task->created_at >= $previousDate && $task->created_at < $limitDate) {
                $transactions[$task->category_id]['last'] = $transactions[$task->category_id]['last'] + $task->price;
            }
            $transactions[$task->category_id]['total'] = $transactions[$task->category_id]['total'] + $task->price;
        }
        return $transactions;
    }
}