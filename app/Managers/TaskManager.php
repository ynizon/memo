<?php

namespace App\Managers;

use App\Models\Category;
use App\Models\Group;
use App\Notifications\ReminderNotif;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class TaskManager
{
    public static function getChart($tasks, $categories): array
    {
        $taskDates = [];
        $taskDatasets = [];
        foreach ($tasks as $task){
            $date = substr($task->created_at,0,7);
            if (!in_array($date, $taskDates) && $task->price > 0) {
                $taskDates[] = $date;
            }
        }

        sort($taskDates);

        $taskCategories = [];
        foreach ($tasks as $task) {
            if ($task->price > 0){
                if (!isset($taskCategories[$task->category_id])){
                    $taskCategories[$task->category_id] = [];
                    foreach ($taskDates as $date){
                        $taskCategories[$task->category_id][$date] = 0;
                    }
                }
                $date = substr($task->created_at,0,7);
                $taskCategories[$task->category_id][$date] = $taskCategories[$task->category_id][$date] + $task->price;
            }
        }

        //Reorder dataset
        $taskCategoriesTmp = $taskCategories;
        $taskCategories = [];
        foreach ($categories as $category) {
            foreach ($taskCategoriesTmp as $taskCategoryId => $taskCategory) {
                if ($taskCategoryId == $category->id) {
                    $taskCategories[$taskCategoryId] = $taskCategory;
                }
            }
        }

        foreach ($taskCategories as $categoryIdTmp => $dates){
            $datas = [];
            foreach ($dates as $date => $total){
                $datas[] = $total;
            }
            $category = Category::find($categoryIdTmp);
            $taskDatasets[] = [
                "label" => __($category->name),
                "tension" => "0.4",
                "borderWidth"=> "0",
                "borderSkipped" => "false",
                "backgroundColor" => $category->color,
                "data" => $datas,
                "maxBarThickness" => "6"
            ];
        }

        $charts = [
            "labels" => $taskDates,
            "datasets" => $taskDatasets
        ];

        return $charts;
    }

    /**
     * Sync the task with the group members and give them notification (when created)
     *
     * @param $user
     * @param $groupsTmp
     * @param $task
     * @param $sendNotif
     * @return void
     */
    public static function syncGroup($user, $groupsTmp, $task, $sendNotif): void
    {
        $groups = [];
        $userGroupIds = $user->getGroupIds();

        if ($groupsTmp != null) {
            foreach ($groupsTmp as $groupId) {
                if (in_array($groupId, $userGroupIds)) {
                    $groups[] = $groupId;
                }
            }
            $task->groupsRelation()->sync($groups);

            if ($sendNotif) {
                foreach ($groups as $groupId) {
                    $group = Group::find($groupId);
                    foreach ($group->users() as $user) {
                        if ($user->id != Auth::user()->id) {
                            try {
                                Notification::send($user, new ReminderNotif($task));
                            } catch (\Exception $exception) {
                                //Do nothing
                            }
                        }
                    }
                }
            }
        }
    }
}