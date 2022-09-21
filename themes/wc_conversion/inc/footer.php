<footer class="main_footer">
    <article id="footeroptin">
        <div class="content">
            <div class="content_left box box2">
                <img
                    src="<?= INCLUDE_PATH; ?>/images/footerconversion.png"
                    alt="Workshop Projeto e Produção!"
                    title="Workshop Projeto e Produção!"
                    /><div>
                    <h1>Aqui um <b>Call To Action</b>!</h1>
                    <p>Lorem Ipsum is simply dummy text of the and <b>lorem Ipsum has been the industry's</b> standard dummy!</p>
                </div>
            </div><div class="content_right box box2">
                <p>Cadastre-se e descubra a <b>Minha call To Action</b></p>
                <?php
                $AC_BUTTON = 'CADASTRE-SE!';
                require REQUIRE_PATH . '/inc/activeform.php';
                ?>
            </div>
        </div>
        <div class="clear"></div>
    </article>

    <div class="content">
        <nav>
            <h1 class="site_title"><?= SITE_SUBNAME; ?></h1>
            <?php
            if (empty($URL[1]) || $URL[0] == 'index'):
                require REQUIRE_PATH . '/inc/menu.php';
            else:
                require REQUIRE_PATH . '/inc/menulink.php';
            endif;
            ?>
        </nav>

        <section class="box box3">
            <h1 class="section_title">Últimas <b>do site!</b></h1>
            <?php
            $Read->ExeRead(DB_POSTS, "WHERE post_status = 1 AND post_date <= NOW() ORDER BY post_date DESC LIMIT :limit OFFSET :offset", "offset=0&limit=4");
            foreach ($Read->getResult() as $Bora):
                extract($Bora);
                echo "<article><h1><a href='" . BASE . "/artigo/{$post_name}' title='{$post_title}'>{$post_title}</a></h1></article>";
            endforeach;
            ?>
        </section><section class="box box3">
            <h1 class="section_title">Mais <b>acessados!</b></h1>
            <?php
            $Read->ExeRead(DB_POSTS, "WHERE post_status = 1 AND post_date <= NOW() ORDER BY post_views DESC LIMIT :limit OFFSET :offset", "offset=0&limit=4");
            foreach ($Read->getResult() as $Bora):
                extract($Bora);
                echo "<article><h1><a href='" . BASE . "/artigo/{$post_name}' title='{$post_title}'>{$post_title}</a></h1></article>";
            endforeach;
            ?>
        </section><section class="box box3 conect">
            <h1 class="section_title">Conecte-se <b>comigo</b></h1>
            <?php require REQUIRE_PATH . '/inc/social.php'; ?>
        </section>

        <div class="clear"></div>
    </div>
    <div class="wc_privacity">
        <div class="content">
            <div class="left">
                <a href="<?= BASE; ?>" title="<?= SITE_NAME; ?> - <?= SITE_SUBNAME; ?>">
                    <img class="main_logo" src="<?= INCLUDE_PATH; ?>/images/workcontrol_w.png" alt="<?= SITE_NAME; ?> - <?= SITE_SUBNAME; ?>" title="<?= SITE_NAME; ?> - <?= SITE_SUBNAME; ?>"/>
                </a>
            </div><div class="right">
                <ul>
                    <li><a rel="shadowbox" href="https://www.upinside.com.br/termos.php" title="">Termos de Uso</a></li>
                    <li><a rel="shadowbox" href="https://www.upinside.com.br/politicas.php" title="">Política de Privacidade</a></li>
                    <li><a rel="shadowbox" href="https://www.upinside.com.br/aviso.php" title="">Aviso Legal</a></li>
                </ul>
            </div>
            <div class="copy">
                Orgulhosamente desenvolvido com Work Control!<br>
                Copyright © <?= date('Y'); ?> <?= SITE_NAME; ?> - Todos os direitos reservados.
            </div>
            <div class="clear"></div>
        </div>
    </div>
</footer>