

<div class="-grid-wrapper page-content -gutter-top">
    <h1>Акции и скидки от магазинов</h1>
    <div id="coupons-feed" class="-grid">

        <script type="text/javascript">
            (function (w, d) {
                var proto = d.location.protocol === "https:" ? "https:" : "http:";
                w.feedAsyncInit = function() {
                    w.FeedAPI({
                        feedUrl: proto + '//getcoupons.ru/feed/fe52ae6c104c6c714ddd/',
                        container: 'coupons-feed'
                    }).feed();
                };
                (function(d){
                    var js, id = 'feed-frame', ref = d.getElementsByTagName('script')[0];
                    if (d.getElementById(id)) {return;}
                    js = d.createElement('script'); js.id = id; js.async = true;
                    js.src = proto + '//getcoupons.ru/static/feeds/js/admitad.feeds.js?3342c3e1';
                    ref.parentNode.insertBefore(js, ref);
                }(d));
            }(window, window.document));
        </script>
    </div>
</div>