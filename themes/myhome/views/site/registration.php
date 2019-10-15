<?php $this->pageTitle = 'Регистрация — MyHome.ru' ?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>


<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/auth.css'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/scroll.js'); ?>


<div class="-grid-wrapper page-content">
    <div class="-grid reg-form">
        <div class="-col-4 -skip-4 -pass-4 -inset-bottom -text-align-center">
            <ul class="-menu-inline spec-type" style="margin-bottom: 20px">
                <li data-id="<?php echo User::ROLE_USER; ?>" class="current sele">Заказчик</li>
                <li data-id="<?php echo User::ROLE_SPEC_JUR; ?>" class="sele">Исполнитель</li>
            </ul>
            <form id="authForm" method="post" action="" autocomplete="off" class=" social-auth">

                <?php echo CHtml::hiddenField('User[role]', User::ROLE_USER); ?>
                <div id="hide1">
                    <div>
                        <?php echo CHtml::activeTextField($user, 'firstname', array('class' => '-huge -text-align-center', 'placeholder' => 'Название компании')); ?>
                    </div>

                    <div>
                        <?php echo CHtml::activeTextField($user, 'lastname', array('class' => '-huge -text-align-center', 'placeholder' => 'ИНН')); ?>
                    </div>

                    <div>
                        <?php echo CHtml::hiddenField('User[city_id]', $user->city_id, array('id' => 'r_city_id')); ?>
                        <input type="text" placeholder="Город" class="-huge  -text-align-center city-autocomplete"
                               id="User_city_id"
                               onkeyup="if (this.value == '') $('#city_id').val('');">
                        <script>
                            $('.city-autocomplete').autocomplete({
                                source: '/utility/autocompletecity',
                                minLength: 3,
                                select: function (event, ui) {
                                    $("#r_city_id").val(ui.item.id).keyup();
                                },
                                focus: function (event, ui) {
                                    $("#r_city_id").val(ui.item.id).keyup();
                                },
                                change: function (event, ui) {
                                    if (ui.item == null) {
                                        $(".input-clear").click();
                                    }
                                }
                            });
                        </script>
                    </div>
                    <div>
                        <?php echo CHtml::activeTextField($user, 'phone', array('placeholder' => 'Телефон', 'class' => '-huge  -text-align-center')); ?>
                    </div>
                </div>
                <div>
                    <?php echo CHtml::activeTextField($user, 'email', array('class' => '-huge -text-align-center', 'placeholder' => 'Электронная почта')); ?>
                </div>

                <div>
                    <?php echo CHtml::activePasswordField($user, 'password', array('class' => '-huge -text-align-center', 'placeholder' => 'Пароль (от 4-х символов)')); ?>
                </div>
                <div class="-inset-top-hf -gutter-bottom-hf -gray">Регистрируясь, я соглашаюсь с <a href="/agreement"
                                                                                                    target="_blank"
                                                                                                    class="-skyblue">правилами</a>
                </div>
                <button type="submit" class="-button -button-orange -huge -semibold">Зарегистрироваться</button>
            </form>
            <p class="-gutter-bottom-null -huge -normal -gray"><span>или</span></p>

        </div>

        <div class="-col-10 -skip-1 -pass-1 -inset-top -inset-bottom -gutter-top-dbl -text-align-center">
            <h3>Создайте свой профиль — откройте больше возможностей</h3>
            <ul class="-menu-inline social-auth">
                <li><a href="/oauth/vkontakte" class="vk"
                       onclick='CCommon.oauth("/oauth/vkontakte", "Vkontakte"); return false;'><span
                                class="pill"><strong>ВКонтакте</strong></span></a></li>
                <li><a href="/oauth/facebook" class="fb"
                       onclick='CCommon.oauth("/oauth/facebook", "Facebook"); return false;'><span class="pill"><strong>Facebook</strong></span></a>
                </li>
                <li><a href="/oauth/odnoklassniki" class="ok"
                       onclick='CCommon.oauth("/oauth/odnoklassniki", "Odnoklassniki"); return false;'><span
                                class="pill"><strong>Одноклассники</strong></span></a></li>
                <!--<li><a href="#" class="mr"><span></span></a></li>-->
                <!--<li><a href="/oauth/twitter" class="tw" onclick='CCommon.oauth("/oauth/twitter", "Twitter"); return false;'><span></span></a></li>-->
            </ul>
        </div>

    </div>
</div>

<script>
    $(function () {
        $("#hide1").hide();
        $(".sele").on('click', function () {
            if ($(this).attr('data-id') == 2) {
                $("#hide1").hide();
                $("#User_role").val(2)
            } else {
                $("#User_role").val(4)
                $("#hide1").show();

            }

        });
    });
</script>