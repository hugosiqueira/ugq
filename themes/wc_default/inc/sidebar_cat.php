<aside class="main_sidebar">
    <article class="main_sidebar_widget">
        <h1><span><?= $category_title; ?></span></h1>
        <p class="tagline"><?= $category_content; ?></p>
    </article>

    <form class="search_form" name="search" action="" method="post" enctype="multipart/form-data">
        <input type="text" name="s" placeholder="Pesquisar Artigos:" required/>
        <button class="btn btn_blue">Pesquisar</button>
    </form>

    <article class="main_sidebar_widget main_sidebar_widget_most">
        <h1><span>Mais Vistos Aqui</span></h1>
        <?php
        $Read->ExeRead(DB_POSTS, "WHERE post_status = 1 AND post_date <= NOW() AND (post_category = :ct OR FIND_IN_SET(:ct, post_category_parent)) ORDER BY post_views DESC, post_date DESC LIMIT 5", "ct={$category_id}");
        if (!$Read->getResult()):
            echo Erro("Ainda não existe posts cadastrados. Por favor, volte mais tarde :)", E_USER_NOTICE);
        else:
            foreach ($Read->getResult() as $Post):
                ?>
                <article class="main_sidebar_widget_post">
                    <a title="Ler mais sobre <?= $Post['post_title']; ?>" href="<?= BASE; ?>/artigo/<?= $Post['post_name']; ?>">
                        <?php if (!empty($Post['post_cover'])): ?>
                            <img title="<?= $Post['post_title']; ?>" alt="<?= $Post['post_title']; ?>" src="<?= BASE; ?>/tim.php?src=uploads/<?= $Post['post_cover']; ?>&w=<?= IMAGE_W / 2; ?>&h=<?= IMAGE_H / 2; ?>"/>
                        <?php elseif (!empty($Post['post_video'])): ?>
                            <img title="<?= $Post['post_title']; ?>" alt="<?= $Post['post_title']; ?>" src="https://i1.ytimg.com/vi/<?= $Post['post_video']; ?>/maxresdefault.jpg"/>
                        <?php else: ?>
                            <img title="<?= $Post['post_title']; ?>" alt="<?= $Post['post_title']; ?>" src="<?= BASE; ?>/tim.php?src=uploads/<?= $Post['post_cover']; ?>&w=<?= IMAGE_W / 2; ?>&h=<?= IMAGE_H / 2; ?>"/>
                        <?php endif; ?>
                    </a>
                    <header>
                        <h1><a title="Ler mais sobre <?= $Post['post_title']; ?>" href="<?= BASE; ?>/artigo/<?= $Post['post_name']; ?>"><?= $Post['post_title']; ?></a></h1>
                        <p class="tagline"><?= $Post['post_subtitle']; ?></p>
                    </header>
                </article>
                <?php
            endforeach;
        endif;
        ?>
    </article>

    <article class="main_sidebar_widget">
        <h1><span>Outras Categorias</span></h1>
        <?php
        $Read->ExeRead(DB_CATEGORIES, "WHERE category_parent IS NULL AND category_id IN(SELECT post_category FROM " . DB_POSTS . " WHERE post_status = 1 AND post_date <= NOW()) ORDER BY category_title ASC");
        if (!$Read->getResult()):
            echo Erro("Ainda não existem sessões cadastradas!", E_USER_NOTICE);
        else:
            echo "<ul>";
            foreach ($Read->getResult() as $Ses):
                echo "<li><a title='artigos/{$Ses['category_name']}' href='" . BASE . "/artigos/{$Ses['category_name']}'>&raquo; {$Ses['category_title']}</a></li>";
                $Read->ExeRead(DB_CATEGORIES, "WHERE category_parent = :pr ORDER BY category_title ASC", "pr={$Ses['category_id']}");
                if ($Read->getResult()):
                    foreach ($Read->getResult() as $Cat):
                        echo "<li><a title='artigos/{$Cat['category_name']}' href='" . BASE . "/artigos/{$Cat['category_name']}'>&raquo;&raquo; {$Cat['category_title']}</a></li>";
                    endforeach;
                endif;
            endforeach;
            echo "</ul>";
        endif;
        ?>
    </article>
</aside>