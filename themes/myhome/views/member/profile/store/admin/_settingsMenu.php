
        <div id="user_menu">
                <ul>
                        <?php $this->widget('zii.widgets.CMenu', array(
                                'activeCssClass' => 'current',
                                'items'=>array(
                                        array('label'=>'Персональные данные', 'url'=>array('/member/profile/settings')),
                                        array('label'=>'Аккаунты в социальных сетях', 'url'=>array('/member/profile/social')),
                                        array('label'=>'Изменение пароля', 'url'=>array('/member/profile/password')),
                                        array('label'=>'Настройки уведомлений', 'url'=>array('/member/profile/options')),
                                ),
                        ));
                        ?>
                </ul>
        </div>
