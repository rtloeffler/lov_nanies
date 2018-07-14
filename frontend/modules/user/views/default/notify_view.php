<?php
use yii\web\View;

/* @var $model \common\models\UserNotify */

$this->registerJs(
    '
    $(document).ready(function () {
        $("html, body").animate({scrollTop: $(".slide").height()+$(".navbar").height()},"slow");
        console.log($("slide").height());
            });
     
     $("#reply").click(function() {
        $(".job-contact-panel").removeClass("hidden").addClass("show")
     })
     $("#sendMessage").click(function() {
       var subject = $("input[name=subject]").val()
       var content = $("textarea[name=content]").val()
       var post_id = '.$model->job_post_id.'
       var pid = '.$model->id.'
       if (subject.trim() == "" || content.trim() == "") {
         return false;
       } else {
         $.post("/find-a-job/contact", {subject:subject,content:content,post_id:post_id,pid:pid}, function(data){
           if (data.status) {
               location.reload()
           } else {
             console.log(data.message)
           }
         }, "json")
       }
     })
     
    ',
    View::POS_READY,
    'my-button-handler'
);
?>
<div class="panel panel-success user-notify">
    <div class="panel-heading"><?= $model->subject ?></div>
    <div class="panel-body">
        <?= \yii\helpers\Html::encode($model->content)?>
        <div class="notify-reply">
            <button type="button" class="btn theme-bg-color btn-xs pull-right" id="reply">Reply</button>
        </div>
    </div>
</div>

<div class="panel panel-default hidden job-contact-panel">
    <div class="panel-heading">
        Send message
    </div>
    <div class="panel-body">
        <div class="form-group">
            <label for="messageSubject">Subject</label>
            <input type="text" class="form-control" name="subject" id="messageSubject" placeholder="subject" maxlength="1000">
        </div>
        <div class="form-group">
            <label for="messageText">Content</label>
            <textarea name="content" class="form-control" id="messageText" rows="10"></textarea>
        </div>
        <button type="button" class="btn theme-bg-color" id="sendMessage">Send</button>
    </div>
</div>