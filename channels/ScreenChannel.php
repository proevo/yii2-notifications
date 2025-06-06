<?php

namespace webzop\notifications\channels;

use Yii;
use webzop\notifications\Channel;
use webzop\notifications\Notification;

class ScreenChannel extends Channel
{
    public function send(Notification $notification)
    {
        $db = Yii::$app->getDb();
        $className = $notification->className();
        $currTime = time();
        $db->createCommand()->insert('notifications', [
            'class' => strtolower(substr($className, strrpos($className, '\\')+1, -12)),
            'key' => $notification->key,
            'message' => sprintf('%s %s', (string)$notification->getTitle(), $notification->data['message']),
            'route' => $notification->data['route'],
            'user_id' => $notification->userId,
            'created_at' => $currTime,
        ])->execute();
    }

}
