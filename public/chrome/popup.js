document.addEventListener("DOMContentLoaded", async () => {
    document.getElementById('block-settings').style.display = 'none';
    refreshNotifs();
});

document.getElementById('save').addEventListener("click", function (e) {
    chrome.storage.local.set({ SERVER_URL: document.getElementById('SERVER_URL').value });

    chrome.storage.local.get("SERVER_URL", function (result) {
        document.getElementById('SERVER_URL').value = result.SERVER_URL;
    });
    refreshNotifs();
});

document.getElementById('settings').addEventListener("click", function (e) {
    if (document.getElementById('block-settings').style.display === 'none'){
        document.getElementById('block-settings').style.display = '';
    } else {
        document.getElementById('block-settings').style.display = 'none';
    }
});

function removeNotif() {
    document.querySelectorAll('.btn').forEach(element => {
        element.addEventListener('click', function () {
            this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);
        });
    });
}

async function refreshNotifs()
{
    const notificationsDiv = document.getElementById("notifications");
    notificationsDiv.innerHTML = "Chargement...";
    try {
        let SERVER_URL = await readLocalStorage("SERVER_URL");
        document.getElementById('SERVER_URL').value = SERVER_URL;

        if (SERVER_URL !== '')
        {
            const response = await fetch(SERVER_URL);

            const notifications = await response.json();

            if (notifications.length === 0) {
                notificationsDiv.innerHTML = "Aucune notification.";
            } else {
                document.getElementById('count').innerText = notifications.length;
                notificationsDiv.innerHTML = notifications.map(notif => {
                    let created = notif.timestamp;
                    let title = notif.app;
                    if (notif.title !== title) {
                        title = title + ' : ' + notif.title;
                    }
                    let icon = `<img src="tel.png" />`;
                    if (notif.icon){
                        icon =`<img src="data:image/png;base64,`+notif.icon +`" />`;
                    }

                    return `
                        <div class="notif">
                            <p><span class="info icon">` + icon + `
                            </span>
                            <a class='btn' href="`+SERVER_URL+`&delete=${notif.id}">
                            <span class="info text"><strong>${title} </strong> (${created}) :
                            <br/>
                            <br/>${notif.text}</span></a></p><hr/>
                        </div>`}).join("");
            }
            removeNotif();
        }
    } catch (error) {
        notificationsDiv.innerHTML = "Erreur lors du chargement.";
        console.error(error);
    }
}

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
