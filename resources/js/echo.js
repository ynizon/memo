import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });

//Reminder general
// window.Echo.channel('memo')
//     .listen('.reminder', (event) => {
//         alert("Vous avez des rappels à traiter sur Memo");
//     });

//Reminder personal
// window.Echo.private('private-memo-' + window.User.id)
//     .listen('.reminder', (event) => {
//         alert("Vous avez des rappels à traiter sur Memo");
//     });
