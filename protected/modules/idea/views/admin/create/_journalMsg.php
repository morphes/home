<?php 
echo Chtml::openTag('li', array('id'=>'msg_'.$data->id));
        echo CHtml::openTag('span', array('class'=>'title'));
                echo date('d.m.Y H:i', $data->create_time).' <b>'. CHtml::link($data->user->login, Yii::app()->createUrl('/member/profile/user', array('id'=>$data->user_id))) .'</b><br />';
        echo CHtml::closeTag('span');
        echo CHtml::openTag('span', array('class'=>'message'));
                echo InteriorJournal::getMessage($data);
        echo CHtml::closeTag('span');
echo CHtml::closeTag('li');
?>