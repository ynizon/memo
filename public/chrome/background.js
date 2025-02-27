let notificationMap = {};
async function checkNotifications() {
    try {
        let SERVER_URL = await readLocalStorage("SERVER_URL");

        if (SERVER_URL !== '') {
            const response = await fetch(SERVER_URL);
            const notifications = await response.json();

            notifications.forEach(notif => {
                if (notif.view == 0) {
                    let notifId = notif.id.toString();

                    let title = notif.app;
                    if (notif.title !== title) {
                        title = title + ' : ' + notif.title;
                    }

                    chrome.notifications.create({
                        type: "basic",
                        iconUrl: "tel.png",
                        title: title,
                        message: notif.text,
                        buttons: [
                            {title: "OK"},
                        ],
                        requireInteraction: false,
                        silent: true
                    }, function (id) {
                        notificationMap[id] = notif.id;
                        //fetch(`${SERVER_URL}&view=${notif.id}`, {method: "GET"});
                    });
                }
            });
        }
    } catch (error) {
        console.error("Erreur lors de la récupération des notifications :", error);
    }
}

// Écoute les clics sur les boutons de notification
chrome.notifications.onButtonClicked.addListener(async (notificationId, buttonIndex) => {
    if (buttonIndex === 0 && notificationMap[notificationId]) {
        const id = notificationMap[notificationId];
        let SERVER_URL = await readLocalStorage("SERVER_URL");

		await fetch(`${SERVER_URL}&delete=${id}`, { method: "GET" });
        chrome.notifications.clear(notificationId);
    }
});

// Vérifie les notifications toutes les 10 secondes
chrome.alarms.create("checkNotifications", { periodInMinutes: 0.2 });

chrome.alarms.onAlarm.addListener(alarm => {
    if (alarm.name === "checkNotifications") {
        checkNotifications();
    }
});

const readLocalStorage = async (key) => {
    return new Promise((resolve, reject) => {
        chrome.storage.local.get([key], function (result) {
            if (result[key] === undefined) {
                reject();
            } else {
                resolve(result[key]);
            }
        });
    });
};
