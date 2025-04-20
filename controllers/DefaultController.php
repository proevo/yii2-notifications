<?php

namespace webzop\notifications\controllers;

use Yii;
use backend\controllers\Controller;
use yii\db\Query;
use yii\data\Pagination;
use yii\helpers\Url;
use webzop\notifications\helpers\TimeElapsed;
use webzop\notifications\widgets\Notifications;

class DefaultController extends Controller
{

    public $layout = "@app/views/layouts/main";

    /**
     * Displays index page.
     *
     * @return string
     */
    public function actionIndex()
    {
        $userId = Yii::$app->getUser()->getId();
        $query = (new Query())
            ->from('notifications')
            ->andWhere(['user_id'=>$userId]);

        $pagination = new Pagination([
            'pageSize' => 20,
            'totalCount' => $query->count(),
        ]);

        $list = $query
            ->orderBy('id DESC')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $notifs = $this->prepareNotifications($list);

        return $this->render('index', [
            'notifications' => $notifs,
            'pagination' => $pagination,
        ]);
    }

    public function actionList()
    {
        $userId = Yii::$app->getUser()->getId();
        $list = (new Query())
            ->from('notifications')
            ->andWhere(['user_id'=> $userId])
            ->orderBy('id DESC')
            ->limit(5)
            ->all();
        $notifs = $this->prepareNotifications($list);
        $this->ajaxResponse(['list' => $notifs]);
    }

    public function actionCount()
    {
        $count = Notifications::getCountUnseen();
        $this->ajaxResponse(['count' => $count]);
    }

    public function actionRead($notificationId)
    {
        Yii::$app->getDb()->createCommand()->update('notifications', ['read' => 1], ['id' => $notificationId])->execute();

        if (Yii::$app->getRequest()->getIsAjax()) {
            return $this->ajaxResponse(1);
        }

        return Yii::$app->getResponse()->redirect(['/notifications/default/index']);
    }

    public function actionReadAll()
    {
        // LENDO SOMENTE AS NOTIFICACOES DO USUARIO
        Yii::$app->getDb()->createCommand()->update('notifications', ['read' => 1], ['user_id' => Yii::$app->user->id])->execute();
        
        if (Yii::$app->getRequest()->getIsAjax()) {
            return $this->ajaxResponse(1);
        }

        Yii::$app->getSession()->setFlash('success', ['message' => Yii::t('modules/notifications', 'Todas as notificações foram marcadas como lidas')]);

        return Yii::$app->getResponse()->redirect(['/notifications/default/index']);
    }

    public function actionDelete($notificationId)
    {
        // DELETANDO SOMENTE AS NOTIFICACOES DO USUARIO
        Yii::$app->getDb()->createCommand()->delete('notifications', ['id' => $notificationId, 'user_id' => Yii::$app->user->id])->execute();

        if (Yii::$app->getRequest()->getIsAjax()) {
            return $this->ajaxResponse(1);
        }

        return Yii::$app->getResponse()->redirect(['/notifications/default/index']);
    }

    public function actionDeleteAll()
    {
        // DELETANDO SOMENTE AS NOTIFICACOES DO USUARIO
        Yii::$app->getDb()->createCommand()->delete('notifications', ['user_id' => Yii::$app->user->id])->execute();

        if (Yii::$app->getRequest()->getIsAjax()) {
            return $this->ajaxResponse(1);
        }

        Yii::$app->getSession()->setFlash('success', ['message' => Yii::t('modules/notifications', 'All notifications have been deleted.')]);

        return Yii::$app->getResponse()->redirect(['/notifications/default/index']);
    }

    private function prepareNotifications($list)
    {
        $notifs = [];
        $seen = [];
        foreach ($list as $notif) {
            if (!$notif['seen']) {
                $seen[] = $notif['id'];
            }
            $route = @unserialize($notif['route']);
            $notif['url'] = !empty($route) ? Url::to($route) : '';
            $notif['urlRedirect'] = Url::toRoute(["/notifications/default/redirect-read", "id" => $notif["id"]]);
            $notif['timeago'] = TimeElapsed::timeElapsed($notif['created_at']);
            $notifs[] = $notif;
        }

        if (!empty($seen)) {
            Yii::$app->getDb()->createCommand()->update('notifications', ['seen' => 1], ['id' => $seen])->execute();
        }

        return $notifs;
    }

    public function ajaxResponse($data = [])
    {
        if (is_string($data)) {
            $data = ['html' => $data];
        }

        $session = \Yii::$app->getSession();
        $flashes = $session->getAllFlashes(true);
        foreach ($flashes as $type => $message) {
            $data['notifications'][] = [
                'type' => $type,
                'message' => $message,
            ];
        }
        return $this->asJson($data);
    }


    public function actionRedirectRead($id)
    {
        if ($id != null) {
            $notification = Yii::$app->db->createCommand('SELECT * FROM notifications WHERE id = '. $id)->queryOne();
            Yii::$app->getDb()->createCommand()->update('notifications', ['read' => 1], ['id' => $id])->execute();
            return $this->redirect($notification["route"]);
        }
    }
}
