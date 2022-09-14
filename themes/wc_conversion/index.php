<article class="top_conversion">
    <div class="content">
        <header>
            <h1>Aqui um <b>Call To Action</b> <span>Para Capturar E-mails!</span></h1>
            <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. <b>Lorem Ipsum has been the industry's</b> standard dummy text ever since the 1500s, when an unknown!</p>
            <?php include REQUIRE_PATH . '/inc/activeoptin.php'; ?>
        </header><div class="media">
            <img src="<?= INCLUDE_PATH; ?>/images/topconversion.png" title="<?= SITE_NAME; ?>" alt="<?= SITE_NAME; ?>"/>
        </div>

        <div class="clear"></div>
    </div>
</article>

<section class="wc_bio" id="a">
    <div class="content">
        <header class="site_header">
            <h1>Fale um pouco mais <b>sobre sua a oferta!</b></h1>
            <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has!</p>
        </header>

        <img title="<?= SITE_NAME; ?> Sobre" alt="<?= SITE_NAME; ?> Sobre"
             src="<?= INCLUDE_PATH; ?>/images/bussbio.png"/><div class="bio_content">
            <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s</p>
            <p>t is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. Many desktop publishing packages and web page editors</p>
            <p>when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.</p>
            <p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary!</p>
        </div>
        <div class="clear"></div>
    </div>
</section>


<article class="wc_social">
    <div class="content">
        <header class="site_header">
            <h1>Siga o <b><?= SITE_SOCIAL_NAME; ?></b> no Facebook</h1>
            <p>There are many variations of passages of Lorem Ipsum available, but the majority!</p>
        </header>

        <div class="lead_social_face">
            <div class="fb-like" style="z-index: 9; max-width: 100%; overflow: hidden;" data-href="https://facebook.com/<?= SITE_SOCIAL_FB_PAGE; ?>" data-layout="standard" data-action="like" data-show-faces="true" data-share="true" data-width="600"></div>
        </div>

        <div class="clear"></div>
    </div>
</article>


<section class="wc_videos" id="b">
    <div class="content">
        <header class="site_header">
            <h1><b>Últimas novidades</b> <?= SITE_NAME; ?>!</h1>
            <p>There are many variations of passages of Lorem Ipsum available, but the majority!</p>
        </header>

        <div class="wc_videos_top">
            <?php
            $Read->ExeRead(DB_POSTS, "WHERE post_status = 1 AND post_date <= NOW() ORDER BY post_date DESC LIMIT :limit OFFSET :offset", "offset=0&limit=1");
            $Offsetpost = array();
            if ($Read->getResult()):
                foreach ($Read->getResult() as $Bora):
                    extract($Bora);
                    $Offsetpost[] = $post_id;
                    $BOX = 1;
                    require REQUIRE_PATH . '/inc/post.php';
                endforeach;
            else:
                Erro("<div style='text-align: center; display: block; width: 100%;'>Ainda não existem posts aqui! Volte mais tarde :)</div>", E_USER_NOTICE);
            endif;
            ?>
        </div><div class="wc_videos_more">
            <?php
            $Read->setPlaces("offset=1&limit=2");
            if ($Read->getResult()):
                foreach ($Read->getResult() as $Bora):
                    extract($Bora);
                    $Offsetpost[] = $post_id;
                    $BOX = 1;
                    require REQUIRE_PATH . '/inc/post.php';
                endforeach;
            else:
                Erro("<div style='text-align: center; display: block; width: 100%;'>Ainda não existem posts aqui! Volte mais tarde :)</div>", E_USER_NOTICE);
            endif;
            ?>
        </div>

        <script type="text/javascript" src="https://apis.google.com/js/platform.js"></script>
        <div class="wc_conversion_yt">
            <h2><b>Inscreva-se no canal do <?= SITE_SOCIAL_NAME; ?></b> para receber as novidades!</h2>
            <div class="g-ytsubscribe" data-channel="<?= SITE_SOCIAL_YOUTUBE; ?>" data-layout="full" data-count="default"></div>
        </div>


        <div class="clear"></div>
    </div>
</section>

<section class="wc_conversion_content">
    <div class="content">
        <header class="site_header">
            <h1>Aqui um <b>Call To Action</b> <span>Para Capturar E-mails!</span></h1>
            <p>There are many variations of passages of Lorem Ipsum available, but the majority!</p>
        </header>

        <p class="tagline">Lorem Ipsum is simply dummy text of the printing and typesetting industry. <b>Lorem Ipsum has been the industry's</b> standard dummy text ever since the 1500s, when an unknown!</p>
        <?php include REQUIRE_PATH . '/inc/activeoptin.php'; ?>
        <div class="clear"></div>
    </div>
</section>


<section class="wc_more" id="c">
    <div class="content">
        <header class="site_header">
            <h1><b>Quer mais?</b> Então veja também!</h1>
            <p>There are many variations of passages of Lorem Ipsum available, but the majority!</p>
        </header>

        <?php
        $OffsetpostGet = (!empty($Offsetpost) ? implode(', ', $Offsetpost) : 0);
        $Read->ExeRead(DB_POSTS, "WHERE post_id NOT IN({$OffsetpostGet}) AND post_status = 1 AND post_date <= NOW() ORDER BY post_date DESC LIMIT :limit OFFSET :offset", "offset=0&limit=8");
        if ($Read->getResult()):
            foreach ($Read->getResult() as $Bora):
                extract($Bora);
                $BOX = 4;
                require REQUIRE_PATH . '/inc/post.php';
            endforeach;
        else:
            Erro("<div style='text-align: center; display: block; width: 100%;'>Ainda não existem posts aqui! Volte mais tarde :)</div>", E_USER_NOTICE);
        endif;
        ?>

        <div class="clear"></div>
    </div>
</section>

<?php
$InstaId = '';
$InstaToken = '';
$Instagram = new Instagram($InstaId, $InstaToken);
$InstaArray = $Instagram->getRecent();
if (!empty($InstaArray->meta->code) && $InstaArray->meta->code == 200):
    echo "<section class='wc_conversion_insta'>";
    echo "<h1 class='wc_conversion_insta_title'><a target='_blank' href='https://www.instagram.com/" . SITE_SOCIAL_INSTAGRAM . "' title='" . SITE_SOCIAL_NAME . " no Instagram!'><b>" . SITE_SOCIAL_NAME . "</b> no Instagram!</a></h1>";
    echo "<div class='wc_conversion_insta_blur'></div>";
    foreach ($InstaArray->data as $InstaPost):
        $InstaText = (!empty($InstaPost->caption->text) ? $InstaPost->caption->text : 'Imagem de ' . SITE_NAME . ' no Instagram!');
        echo "<article><h1 class='site_title'>{$InstaText}</h1><img alt='{$InstaText}' title='{$InstaText}' width='100%' src='{$InstaPost->images->thumbnail->url}'/></article>";
    endforeach;
    echo "</section>";
else:
    Erro('<div class="content" style="text-align:center"><b>INDEX.php//138</b> Configure o Instagram Aqui!</div>');
endif;
?>

<section class="wc_prove">
    <div class="content">
        <header class="site_header">
            <h1>Aqui coloque <b>alguns vídeos</b></h1>
            <p>There are many variations of passages of Lorem Ipsum available, but the majority!</p>
        </header>

        <div class="testimony">
            <div class="testimony_content">
                <span class="testimony_close">X</span>
                <h1>Assistir Depoimento:</h1>
                <div class="embed-container"></div>

                <div class="content_like">
                    <div class="box_like">
                        <p>Curta no Facebook</p>
                        <div class="fb-like box_like_face" data-href="https://facebook.com/<?= SITE_SOCIAL_FB_PAGE; ?>" data-layout="standard" data-action="like" data-show-faces="false" data-share="false" data-width="170"></div>
                    </div><div class="box_like">
                        <p>Inscreva-se no YouTube</p>
                        <div class="g-ytsubscribe" data-channel="<?= SITE_SOCIAL_YOUTUBE; ?>" data-layout="full" data-count="default"></div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        for ($i = 0; $i < 8; $i++):
            $YouTubeVideo = 'zEdLTpDYInA';
            ?><article id="<?= $YouTubeVideo; ?>" class="lead_take box box4 testimony_start">
                <div class="thumb">
                    <img src="http://i1.ytimg.com/vi/<?= $YouTubeVideo; ?>/maxresdefault.jpg" title="Título do vídeo" alt="Título do vídeo"/>
                    <div class="false_bg take_play"></div>
                </div>
                <h1><b>Título do vídeo</b> There are many variations of passages of Lorem Ipsum available, but the....</h1>
                <span class="take_play">Assistir Vídeo!</span>
            </article><?php
        endfor;
        ?>
        <div class="clear"></div>
    </div>
</section>