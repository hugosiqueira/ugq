<?php
if (!$Read):
    $Read = new Read;
endif;
?>
<div class="top_conversion breadcrumbs">
    <div class="content">
        <p style="font-size: 3em; font-weight: bold; text-shadow: 1px 1px #000; text-transform: none;">Oppsss! :/</p>
        <div class="clear"></div>
    </div>
</div>
<div class="container not_found">
    <div class="content">
        <section>
            <header class="site_header">
                <h1>Desculpe, mas <b>não encontramos o que você procura!</b></h1>
                <p>Conteúdo não encontrado. Você pode fazer uma pesquisa, ou ainda veja abaixo uma lista de nossos conteúdos mais acesssados!</p>
            </header>

            <form class="sidebar_search" name="search" action="" method="post" enctype="multipart/form-data">
                <input type="text" name="s" placeholder="Pesquisar Artigos:" required/><button class="btn btn_blue">IR!</button>
            </form>

            <?php
            $Read->ExeRead(DB_POSTS, "WHERE post_status = 1 AND post_date <= NOW() ORDER BY post_views DESC LIMIT 4");
            if ($Read->getResult()):
                foreach ($Read->getResult() as $Post):
                    extract($Post);
                    $BOX = 4;
                    require REQUIRE_PATH . '/inc/post.php';
                endforeach;
            endif;
            ?>
        </section>
        <div class="clear"></div>
    </div>
</div>