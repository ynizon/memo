<?php

namespace App\Managers;

use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public static function getExpenses($tasks, $limitDate, $previousDate, $groups) {
        $expenses = [];
        foreach ($tasks as $task){
            $id = 'task'.$task->category_id;
            $category = [
                'label' => __("Last expense"),
                'icon' => $task->category->icon,
                'name' => $task->category->name,
                'archive' => $task->category->archive,
                'color' => $task->category->color,
                'href' => "/tasks?category_id=".$task->category->id
            ];
            if (!isset($expenses[$id])){
                $expenses[$id] = ["last" =>0, "now" =>0, "total"=>0,
                    'category' =>$category, 'latest'=>$task->created_at];
            }

            if ($task->created_at >= $limitDate) {
                $expenses[$id]['now'] = $expenses[$id]['now'] + $task->price;
            }
            if ($task->created_at >= $previousDate && $task->created_at < $limitDate) {
                $expenses[$id]['last'] = $expenses[$id]['last'] + $task->price;
            }
            $expenses[$id]['total'] = $expenses[$id]['total'] + $task->price;
        }

        foreach ($groups as $group)
        {
            $id = 'group'.$group['id'];
            $category = [
                'label' => __('Last expense'),
                'icon' => 'fa-group',
                'name' => $group['name'],
                'archive' => 0,
                'color' => '#352365',
                'href' => "/tasks/group_id=".$group['id']
            ];
            $expenses[$id] = ["last" =>$group['last'], "now" =>$group['now'],
                "total"=> $group['total'], 'category' =>$category, 'latest'=>$group['latest']];
        }

        $category = [
            'label' => __('Last update'),
            'icon' => 'fa-bank',
            'name' => __("Bank"),
            'archive' => 0,
            'color' => "#cf78e6",
            'href' => "/accounts"
        ];

        //Current total
        $total = 0;
        $accounts = DB::table('accounts')
            ->where('active', "=",1)
            ->where("user_id","=", Auth::user()->getAuthIdentifier())
            ->selectRaw("SUM(amount) as sum_amount")
            ->get();
        foreach ($accounts as $account)
        {
            $total = $account->sum_amount;
        }

        //Last total
        $oldMonth = new DateTime();
        $oldMonth->modify('first day of this month');
        $oldMonth->modify('-1 month');
        $last_total = 0;
        $accounts = DB::table('accounts')
            ->join('account_amounts', 'account_amounts.account_id', '=', 'accounts.id')
            ->where('accounts.active', "=",1)
            ->where('account_amounts.created_at', "like", $oldMonth->format('Y-m-d').'%')
            ->where("accounts.user_id","=",Auth::user()->getAuthIdentifier())
            ->selectRaw("SUM(account_amounts.amount) as sum_amount")
            ->get();
        foreach ($accounts as $account)
        {
            $last_total = $account->sum_amount;
        }
        $expenses['bank'] = ["last" =>$last_total, "now" =>$total, "total"=> $total,
                    'category' =>$category, 'latest'=>Auth::user()->linxo_at];
        return $expenses;
    }
}
