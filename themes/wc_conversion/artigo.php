<?php
if (!$Read):
    $Read = new Read;
endif;

$Read->ExeRead(DB_POSTS, "WHERE post_name = :nm", "nm={$URL[1]}");
if (!$Read->getResult()):
    require REQUIRE_PATH . '/404.php';
    return;
else:
    extract($Read->getResult()[0]);
    $Update = new Update;
    $UpdateView = ['post_views' => $post_views + 1, 'post_lastview' => date('Y-m-d H:i:s')];
    $Update->ExeUpdate(DB_POSTS, $UpdateView, "WHERE post_id = :id", "id={$post_id}");

    $Read->FullRead("SELECT category_title, category_name FROM " . DB_CATEGORIES . " WHERE category_id = :id", "id={$post_category}");
    $PostCategory = $Read->getResult()[0];

    $Read->FullRead("SELECT user_name, user_lastname FROM " . DB_USERS . " WHERE user_id = :user", "user={$post_author}");
    $AuthorName = "{$Read->getResult()[0]['user_name']} {$Read->getResult()[0]['user_lastname']}";
endif;
?>
<div class="top_conversion breadcrumbs">
    <div class="content">
        <p><a href="<?= BASE; ?>" title="<?= SITE_NAME; ?>"><?= SITE_NAME; ?></a> / <a href="<?= BASE; ?>/artigos/<?= $PostCategory['category_name']; ?>" title="Ver mais: <?= $PostCategory['category_title']; ?> em <?= SITE_NAME; ?>!"><?= $PostCategory['category_title']; ?></a> / <?= $post_title; ?></p>
        <div class="clear"></div>
    </div>
</div>

<div class="container post_single">
    <div class="content">
        <div class="left_content">
            <div class="post_content">
                <header>
                    <h1 class="title"><?= $post_title; ?></h1>
                    <p class="tagline"><?= $post_subtitle; ?></p>
                    <p class="postby">Por <b><?= $AuthorName; ?></b> dia <time datetime="<?= date('Y-m-d', strtotime($post_date)); ?>" pubdate="pubdate"><?= date('d/m/Y H\hi', strtotime($post_date)); ?></time> em <b><?= $PostCategory['category_title']; ?></b></p>
                </header>
                <?php
                if ($post_video):
                    echo "<div class='embed-container'>";
                    echo "<iframe id='mediaview' width='640' height='360' src='https://www.youtube.com/embed/{$post_video}?rel=0&amp;showinfo=0&autoplay=0&origin=" . BASE . "' frameborder='0' allowfullscreen></iframe>";
                    echo "</div>";
                else:
                    echo "<img class='cover' title='{$post_title}' alt='{$post_title}' src='" . BASE . "/uploads/{$post_cover}'/>";
                endif;
                ?>

                <?php
                $WC_TITLE_LINK = $post_title;
                $WC_SHARE_HASH = "BoraEmpreender";
                $WC_SHARE_LINK = BASE . "/artigo/{$post_name}";
                require './_cdn/widgets/share/share.wc.php';
                ?>
                <div class="htmlchars">
                    <?= $post_content; ?>
                </div>
                <?php require './_cdn/widgets/share/share.wc.php'; ?>

                <article class="post_comments">
                    <h1>Deixe seu comentário aqui:</h1>
                    <div class="fb-comments" data-href="<?= BASE; ?>/artigo/<?= $post_name; ?>" data-width="100%" data-numposts="10" data-order-by="reverse_time"></div>
                </article>

                <div class="clear"></div>
            </div>
        </div><?php require REQUIRE_PATH . '/inc/sidebar.php'; ?>
        <div class="clear"></div>
    </div>
</div>

<?php
$Read->ExeRead(DB_POSTS, "WHERE post_status = 1 AND post_date <= NOW() AND post_category_parent = :ct AND post_id != :id ORDER BY post_date DESC LIMIT 4", "ct={$post_category_parent}&id={$post_id}");
if ($Read->getResult()):
    echo '<section class="single_post_more">';
    echo "<div class='content'>";
    echo '<header class="site_header">';
    echo '<h1>Veja Também!</h1>';
    echo '<p>Os artigos relacionados podem te interessar:</p>';
    echo '</header>';

    foreach ($Read->getResult() as $More):
        extract($More);
        require REQUIRE_PATH . '/inc/post.php';
        ?><?php
    endforeach;
    echo '<div class="clear"></div></div>';
    echo '</section>';
endif;
?>