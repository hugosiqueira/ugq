$(function () {
    $('.mobile_menu').click(function () {
        $('.main_nav').slideToggle();
    });

    //PLAY TAKE
    $('.testimony_start').click(function () {
        var Testimony = $(this).attr('id');
        var Headding = $(this).find('h1').html();
        $('.testimony_content h1').html(Headding);
        $('.testimony_content .embed-container').html('<iframe width="640" height="360" src="https://www.youtube.com/embed/' + Testimony + '?rel=0&amp;showinfo=0&autoplay=1&origin=https://wspp.upinside.com.br" frameborder="0" allowfullscreen></iframe>');
        $('.testimony').fadeIn(200);
    });

    $('.testimony_close').click(function () {
        $('.testimony').fadeOut(200, function () {
            $('.testimony_content .embed-container').html('');
        });
    });
    //END PLAY TAKE
});

//FACEBOOK APP
(function (d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id))
        return;
    js = d.createElement(s);
    js.id = id;
    js.src = "//connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v2.6&appId=626590460695980";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

//FACEBOOK ADS
!function (f, b, e, v, n, t, s) {
    if (f.fbq)
        return;
    n = f.fbq = function () {
        n.callMethod ?
                n.callMethod.apply(n, arguments) : n.queue.push(arguments)
    };
    if (!f._fbq)
        f._fbq = n;
    n.push = n;
    n.loaded = !0;
    n.version = '2.0';
    n.queue = [];
    t = b.createElement(e);
    t.async = !0;
    t.src = v;
    s = b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t, s)
}(window,
        document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

fbq('init', '');
fbq('track', "PageView");

//GOOGLE ANALYTICS
(function (i, s, o, g, r, a, m) {
    i['GoogleAnalyticsObject'] = r;
    i[r] = i[r] || function () {
        (i[r].q = i[r].q || []).push(arguments)
    }, i[r].l = 1 * new Date();
    a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
    a.async = 1;
    a.src = g;
    m.parentNode.insertBefore(a, m)
})(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

ga('create', '', 'auto');
ga('send', 'pageview');

//ACTIVE CAMPAIGN
var trackcmp_email = '';
var trackcmp = document.createElement("script");
trackcmp.async = true;
trackcmp.type = 'text/javascript';
trackcmp.src = '//trackcmp.net/visit?actid=999677658&e=' + encodeURIComponent(trackcmp_email) + '&r=' + encodeURIComponent(document.referrer) + '&u=' + encodeURIComponent(window.location.href);
var trackcmp_s = document.getElementsByTagName("script");
if (trackcmp_s.length) {
    trackcmp_s[0].parentNode.appendChild(trackcmp);
} else {
    var trackcmp_h = document.getElementsByTagName("head");
    trackcmp_h.length && trackcmp_h[0].appendChild(trackcmp);
}