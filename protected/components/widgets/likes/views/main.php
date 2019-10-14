<?php
Yii::app()->clientScript->registerScriptFile('/js/ga_social_tracking.js');
?>

<div class="like_conteiner fb">
        <?php // *** FACEBOOK *** ?>

        <div id="fb-root"></div>
        <script>
		window.fbAsyncInit = function() {
			_ga.trackFacebook(); //Google Analytics tracking
		};
		(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = "//connect.facebook.net/ru_RU/all.js#xfbml=1";
			fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));
	</script>

<!--        <div class="fb-like" data-send="false" data-layout="button_count" data-width="160" data-show-faces="false" data-font="verdana"></div>-->
	<fb:like data-send="false" data-layout="button_count" data-width="160" data-show-faces="false" data-font="verdana"></fb:like>
</div>

<div class="like_conteiner vk">
        <?php // *** VKONTAKTE *** ?>

        <script type="text/javascript" src="http://userapi.com/js/api/openapi.js?49"></script>

        <script type="text/javascript">
                VK.init({apiId: 2478844, onlyWidgets: true});
        </script>

        <div id="vk_like_<?php echo $vkLikePostfix;?>"></div>
        <script type="text/javascript">
                VK.Widgets.Like("vk_like_<?php echo $vkLikePostfix;?>", {type: "mini", height: 18});
        </script>

	<script>
		_ga.trackVK();
	</script>
</div>

<div class="like_conteiner tw">
        <?php // *** TWITTER *** ?>

        <a href="https://twitter.com/share" class="twitter-share-button" data-via="myhomeru" data-lang="ru">Твитнуть</a>
        <script>
		window.twttr = (function (d,s,id) {
			var t, js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return; js=d.createElement(s); js.id=id;
			js.src="//platform.twitter.com/widgets.js"; fjs.parentNode.insertBefore(js, fjs);
			return window.twttr || (t = { _e: [], ready: function(f){ t._e.push(f) } });
		}(document, "script", "twitter-wjs"));

		// Wait for the asynchronous resources to load
		twttr.ready(function(twttr) {
			_ga.trackTwitter(); //Google Analytics tracking
		});
	</script>
</div>

<div class="like_conteiner ok">
	<?php // *** ODNOKLASSNIKI *** ?>

	<div id="ok_shareWidget_<?php echo $okLikePostfix;?>"></div>
	<script>
		!function (d, id, did, st) {
			var js = d.createElement("script");
			js.src = "http://connect.ok.ru/connect.js";
			js.onload = js.onreadystatechange = function () {
				if (!this.readyState || this.readyState == "loaded" || this.readyState == "complete") {
					if (!this.executed) {
						this.executed = true;
						setTimeout(function () {
							OK.CONNECT.insertShareWidget(id,did,st);
						}, 0);
					}
				}};
			d.documentElement.appendChild(js);
		}(document,"ok_shareWidget_<?php echo $okLikePostfix;?>", "<?php echo Yii::app()->createAbsoluteUrl(Yii::app()->request->url); ?>","{width:100,height:30,st:'oval',sz:20,nt:1}");
	</script>


</div>

<div class="like_conteiner gp">
        <?php // *** GOOGLE+ *** ?>

        <!-- Place this tag where you want the +1 button to render -->
        <g:plusone size="medium"></g:plusone>

        <!-- Place this render call where appropriate -->
        <script type="text/javascript">
                window.___gcfg = {lang: 'ru'};

                (function() {
                        var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                        po.src = 'https://apis.google.com/js/plusone.js';
                        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                })();
        </script>
</div>