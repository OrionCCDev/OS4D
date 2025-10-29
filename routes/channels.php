<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Private channel for each user - users can only listen to their own notifications
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Optional: Add department or team channels if needed in the future
// Broadcast::channel('department.{departmentId}', function ($user, $departmentId) {
//     return $user->department_id === (int) $departmentId;
// });
