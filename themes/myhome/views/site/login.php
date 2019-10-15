<?php
$this->pageTitle = Yii::app()->name . ' - Login';
?>

<div class="-grid-wrapper page-content">
    <div class="-grid auth-form">
        <div class="-col-8 -skip-2 -inset-top -gutter-top-dbl -text-align-center">
            <h3>Вход на MyHome</h3>
            <ul class="-menu-inline social-auth">
                <li><a href="/oauth/vkontakte" class="vk"
                       onclick="CCommon.oauth(&quot;/oauth/vkontakte&quot;, &quot;Vkontakte&quot;); return false;"><span
                                class="pill"><strong>ВКонтакте</strong></span></a></li>
                <li><a href="/oauth/facebook" class="fb"
                       onclick="CCommon.oauth(&quot;/oauth/facebook&quot;, &quot;Facebook&quot;); return false;"><span
                                class="pill"><strong>Facebook</strong></span></a></li>
                <li><a href="/oauth/odnoklassniki" class="ok"
                       onclick="CCommon.oauth(&quot;/oauth/odnoklassniki&quot;, &quot;Odnoklassniki&quot;); return false;"><span
                                class="pill"><strong>Одноклассники</strong></span></a></li>
                <!--<li><a href="#" class="mr"><span></span></a></li>-->
                <!--<li><a href="/oauth/twitter" class="tw" onclick='CCommon.oauth("/oauth/twitter", "Twitter"); return false;'><span></span></a></li>-->
            </ul>
        </div>
        <div class="-col-4 -skip-4 -pass-3 -inset-bottom -text-align-center">
            <form id="authForm" method="post" action="/site/login" name="">
                <?php if ($user->loginErrorCode() === UserIdentity::NOTICE_TMPPASS_REQUIRED || $user->loginErrorCode() === UserIdentity::ERROR_TMPPASS) : ?>
                    <input type="hidden" name="User[login]" value="<?php echo $user->login; ?>">
                    <input type="hidden" name="User[password]" value="<?php echo $user->password; ?>">

                    <p>
                        На ваш email, указанный при регистрации, отправлено письмо с разовым паролем для входа. Введите
                        отправленный вам разовый пароль.
                        Данная мера необходима только при попытке входа в административный аккаунт с неизвестного IP
                        адреса.
                    </p>

                    <p class="p-login-name">
                        <label for="tmpPass">Разовый пароль</label><br>
                        <input type="text" name="User[tmpPass]" class="textInput" tabindex="1"
                               value="<?php echo $user->tmpPass; ?>"/>
                    </p>

                    <p class="p-login-submit">
                        <button type="submit" class="button2" tabindex="2">Войти</button>
                    </p>

                    <div class="spacer"></div>

                    <?php
                    if ($user->hasErrors('tmpPass') && !empty($user->tmpPass)) {
                        echo CHtml::error($user, 'tmpPass', array('class' => 'p-login-error'));
                    }
                    ?>

                <?php else : ?>
                    <div><input type="text" tabindex="1" name="User[login]" placeholder="Электронная почта"
                                class="-huge -text-align-center"></div>
                    <div><input type="password" tabindex="2" name="User[password]" maxlength="32"
                                placeholder="Пароль (от 4-х символов)" class="-huge -text-align-center"></div>
                    <div class="-gutter-bottom -text-align-left">
                        <label class="-checkbox">
                            <?php echo CHtml::activeCheckBox($user, 'rememberMe'); ?>
                            <span>Запомнить</span>
                        </label>
                        <span class="-push-right">
                        <a href="/password/remember" class="-skyblue">Забыли пароль?</a>
                    </span>
                    </div>
                    <button type="submit" tabindex="3" class="-button -button-skyblue -huge -semibold"
                            onclick="_gaq.push(['_trackEvent','Login','Войти']); yaCounter11382007.reachGoal('lgbtn'); return true;">
                        Войти
                    </button>
                <?php endif; ?>
            </form>
        </div>
        <div class="-col-8 -skip-2 -pass-2 -tinygray-box -inset-top-hf -inset-bottom-hf -gutter-top-dbl -text-align-center">
            <h3>Еще не зарегистрированы?
                <a href="/site/registration" class="-skyblue -registration">Присоединяйтесь!</a>
            </h3>
        </div>

    </div>
</div>

