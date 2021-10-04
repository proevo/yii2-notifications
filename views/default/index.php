<?php
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\icons\Icon;
use yii\widgets\LinkPager;

$this->title = Yii::t('modules/notifications', 'Notifications');
$this->params['breadcrumbs'][] = $this->title;

$js = '
/* To initialize BS3 tooltips set this below */
$(function () { 
    $("[data-toggle=\"tooltip\"]").tooltip(); 
});;
';
$this->registerJs($js);

?>

<div class="notification-index">

    <div class="toolBar">
        <?= Html::a(Yii::t('modules/notifications', 'Mark all as read'), Url::toRoute(["/notifications/default/read-all"]), ['class' => 'btn btn-primary', 'data-confirm'=>'<strong>Confirma a leitura de TODAS</strong> as notificações?']); ?>
        <?= Html::a(Icon::show('trash', ['class'=>'fa-1x'], Icon::FA) . 'Excluir todas', Url::toRoute(["/notifications/default/delete-all"]), ['class' => 'btn btn-danger', 'data-confirm'=>'<strong>Confirma a exclusão de TODAS</strong> as notificações?']); ?>
    </div>

    <table class="kv-grid-table table table-bordered table-notification">
        <thead>
            <tr>
                <th colspan="4">Notificações</th>
            </tr>
        </thead>
        <tbody>
        
        <?php 
        if($notifications):
            foreach($notifications as $notif): ?>
    
                <tr class="<?= $notif['read'] ? "read" : null; ?>" data-id="<?= $notif['id']; ?>" data-key="<?= $notif['key']; ?>" data-class="<?= $notif['class']; ?>">
                    <td>
                        <a href="<?= $notif['route'] ?>"><?= $notif['message']; ?></a>
                    </td>
                    <td><small class="semDestaque"><?= $notif['timeago']; ?></small></td>
                    <td class="gvIcon aC">
                        <?= Html::a("&nbsp;", Url::toRoute(['read', 'notificationId'=>$notif['id']]), ["class"=>"mark-read-inline", "data-toggle"=>"tooltip", "title"=>$notif['read'] ? Yii::t('modules/notifications', 'Read') : Yii::t('modules/notifications', 'Mark as read')]); ?>
                    </td>
                    <td class="gvIcon aC">
                        <?= Html::a(Icon::show('trash', ['class'=>'fa-1x'], Icon::FA), Url::toRoute(["delete", "notificationId"=>$notif['id']]), ['data-confirm'=>'<strong>Confirma a exclusão</strong> desta notificação?']); ?>
                    </td>
                </tr>

            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3"><?= Yii::t('modules/notifications', 'There are no notifications to show') ?></td></tr>
        <?php endif; ?>
                
        </tbody>
    </table>

    <?= LinkPager::widget(['pagination' => $pagination]); ?>

</div>
