<?php
if (!$Read):
    $Read = new Read;
endif;

$Read->ExeRead(DB_CATEGORIES, "WHERE category_name = :nm", "nm={$URL[1]}");
if (!$Read->getResult()):
    require REQUIRE_PATH . '/404.php';
    return;
else:
    extract($Read->getResult()[0]);
endif;
?>
<div class="top_conversion breadcrumbs">
    <div class="content">
        <p><a href="<?= BASE; ?>" title="<?= SITE_NAME; ?>"><?= SITE_NAME; ?></a> / <?= $category_title; ?></p>
        <div class="clear"></div>
    </div>
</div>

<div class="container main_content wc_blog_content">
    <div class="content">
        <div class="main_blog">
            <?php
            $Page = (!empty($URL[2]) ? $URL[2] : 1);
            $Pager = new Pager(BASE . "/artigos/{$category_name}/", "<", ">", 5);
            $Pager->ExePager($Page, 10);
            $Read->ExeRead(DB_POSTS, "WHERE post_status = 1 AND post_date <= NOW() AND (post_category = :ct OR FIND_IN_SET(:ct, post_category_parent)) ORDER BY post_date DESC LIMIT :limit OFFSET :offset", "limit={$Pager->getLimit()}&offset={$Pager->getOffset()}&ct={$category_id}");
            if (!$Read->getResult()):
                $Pager->ReturnPage();
                echo Erro("Ainda Não existe posts cadastrados. Favor volte mais tarde :)", E_USER_NOTICE);
            else:
                foreach ($Read->getResult() as $Post):
                    extract($Post);
                    $BOX = 1;
                    require REQUIRE_PATH . '/inc/post.php';
                endforeach;
            endif;

            $Pager->ExePaginator(DB_POSTS, "WHERE post_status = 1 AND post_date <= NOW() AND (post_category = :ct OR FIND_IN_SET(:ct, post_category_parent))", "ct={$category_id}");
            echo $Pager->getPaginator();
            ?>
        </div><?php require REQUIRE_PATH . '/inc/sidebar.php'; ?>
        <div class="clear"></div>
    </div>
</div>