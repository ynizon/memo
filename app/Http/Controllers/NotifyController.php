<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class NotifyController extends Controller
{

	public function index(Request $request)
	{
		date_default_timezone_set('Europe/Paris');
		header("Access-Control-Allow-Origin: *");
		header("Content-Type: application/json");
		header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type");

		// Fichier pour stocker les notifications
		$user = User::where("email","=",$request->input("email"))->where("token","=",$request->input("token"))->first();
		if (!$user){
			die("User or token incorrect");
		}
        $dir = storage_path("app");
        if (!is_dir($dir))
        {
            mkdir($dir);
        }
        $dir = storage_path("app/notifications");
        if (!is_dir($dir))
        {
            mkdir($dir);
        }
		$dir = storage_path("app/notifications/".$user->id);
        if (!is_dir($dir))
        {
            mkdir($dir);
        }
        $notificationsFile = $dir."/notifications.json";

		if (file_exists($notificationsFile)) {
			$notifications = json_decode(file_get_contents($notificationsFile), true);
		} else {
			$notifications = [];
		}

		if ($request->input('delete') != null && file_exists($notificationsFile)) {
			$deleteId = $request->input('delete');

			$notifications = array_filter($notifications, function($notif) use ($deleteId) {
				return $notif['id'] != $deleteId;
			});

			file_put_contents($notificationsFile, json_encode(array_values($notifications), JSON_PRETTY_PRINT));
		}


		if ($request->input('view') != null && file_exists($notificationsFile)) {
			$notifications2 = [];
			$viewId = $request->input('view');
			foreach ($notifications as $notification){
				if ($notification['id'] == $viewId){
					$notification['view'] = 1;
				}
				$notifications2[] = $notification;
			}
			$notifications = $notifications2;

			file_put_contents($notificationsFile, json_encode(array_values($notifications), JSON_PRETTY_PRINT));
		}

		// Charger les notifications existantes
		if (file_exists($notificationsFile)) {
			$notifications = json_decode(file_get_contents($notificationsFile), true);
		} else {
			$notifications = [];
		}

		if ($request->input('title') == null) {
			// Retourner toutes les notifications

			usort($notifications, function ($a, $b) {
				return strcmp($b['time'], $a['time']); // Descendant (B avant A)
			});

			echo json_encode($notifications);
			exit;
		} else {
			// Ajouter la notification
			$notifications[] = [
				"view"=> "0",
				"id" => uniqid(),
				"app" => $request->input('app'),
				"title" => $request->input('title'),
				"text" => $request->input('text'),
				"icon" => $request->input('icon'),
				"timestamp" => date("d/m/Y H:i"),
				"time" => time()
			];

			// Sauvegarder les notifications
			file_put_contents($notificationsFile, json_encode(array_values($notifications), JSON_PRETTY_PRINT));

			echo json_encode(["message" => "Notification enregistrée"]);
			exit;
		}
	}
}
