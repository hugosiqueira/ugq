<aside class="main_sidebar">
    <form class="sidebar_search sidebar_widget" name="search" action="" method="post" enctype="multipart/form-data">
        <input type="text" name="s" placeholder="Pesquisar Artigos:" required/><button class="btn btn_green">IR!</button>
    </form><article class="sidebar_social sidebar_widget">
        <header>
            <img alt="Siga <?= SITE_SOCIAL_NAME; ?> no Facebook" title="Siga <?= SITE_SOCIAL_NAME; ?> no Facebook" src="<?= INCLUDE_PATH; ?>/images/bussbio.png"/>
            <h1><span><b><?= SITE_SOCIAL_NAME; ?></b></span>no Facebook</h1>
            <p>Siga no Facebook e acompanhe as novidades em primeira mão!</p>
        </header>

        <div class="fb-like" style="z-index: 9; max-width: 100%; overflow: hidden;" data-href="https://facebook.com/<?= SITE_SOCIAL_FB_PAGE; ?>" data-layout="standard" data-action="like" data-show-faces="true" data-share="false" data-width="240"></div>
    </article><nav class="sidebar_nav sidebar_widget">
        <h1>Ver <b>Categorias</b></h1>
        <?php
        $Read->ExeRead(DB_CATEGORIES, "WHERE category_parent IS NULL AND category_id IN(SELECT post_category FROM " . DB_POSTS . " WHERE post_status = 1 AND post_date <= NOW()) ORDER BY category_title ASC");
        if (!$Read->getResult()):
            echo Erro("Ainda não existem sessões cadastradas!", E_USER_NOTICE);
        else:
            echo "<ul>";
            foreach ($Read->getResult() as $Ses):
                echo "<li><a title='artigos/{$Ses['category_name']}' href='" . BASE . "/artigos/{$Ses['category_name']}'>&raquo; {$Ses['category_title']}</a></li>";
                $Read->ExeRead(DB_CATEGORIES, "WHERE category_parent = :pr AND category_id IN(SELECT post_category_parent FROM " . DB_POSTS . " WHERE post_status = 1 AND post_date <= NOW()) ORDER BY category_title ASC", "pr={$Ses['category_id']}");
                if ($Read->getResult()):
                    foreach ($Read->getResult() as $Cat):
                        echo "<li><a title='artigos/{$Cat['category_name']}' href='" . BASE . "/artigos/{$Cat['category_name']}'>&raquo;&raquo; {$Cat['category_title']}</a></li>";
                    endforeach;
                endif;
            endforeach;
            echo "</ul>";
        endif;
        ?>
    </nav><article class="sidebar_most">
        <h1>Mais <b>Vistos</b></h1>
        <?php
        $Read->ExeRead(DB_POSTS, "WHERE post_status = 1 AND post_date <= NOW() ORDER BY post_views DESC, post_date DESC LIMIT 5");
        if (!$Read->getResult()):
            echo Erro("Ainda Não existe posts cadastrados. Favor volte mais tarde :)", E_USER_NOTICE);
        else:
            foreach ($Read->getResult() as $Post):
                ?><article class="sidebar_most_post">
                    <a title="Ler mais sobre <?= $Post['post_title']; ?>" href="<?= BASE; ?>/artigo/<?= $Post['post_name']; ?>">
                        <img title="<?= $Post['post_title']; ?>" alt="<?= $Post['post_title']; ?>" src="<?= BASE; ?>/tim.php?src=uploads/<?= $Post['post_cover']; ?>&w=<?= IMAGE_W / 2; ?>&h=<?= IMAGE_H / 2; ?>"/>
                    </a>
                    <header>
                        <h1><a title="Ler mais sobre <?= $Post['post_title']; ?>" href="<?= BASE; ?>/artigo/<?= $Post['post_name']; ?>"><?= $Post['post_title']; ?></a></h1>
                    </header>
                </article><?php
            endforeach;
        endif;
        ?>
    </article>
</aside>