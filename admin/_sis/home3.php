<?php
$AdminLevel = 6;
if (empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;
?>
<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-home">Dashboard</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
        </p>
    </div>
</header>

<div class="dashboard_content">

    <div class="box box25">
        <div class="panel_header success">
            <span>
                <a href="javascript:void(0)" class="btn btn_green icon-loop icon-notext" id="loopDashboard"></a>
            </span>
            <h2 class="icon-earth">ONLINE AGORA:</h2>
        </div>
        <div class="panel dashboard_onlinenow">
            <?php
            $Read->FullRead("SELECT count(online_id) AS total from " . DB_VIEWS_ONLINE . " WHERE online_endview >= NOW()");
            echo "<p class='icon-users wc_useronline'>" . str_pad($Read->getResult()[0]['total'], 4, 0, STR_PAD_LEFT) . "</p>";
            ?>
            <a class="icon-target" href="dashboard.php?wc=onlinenow" title="Ver Usuários Online">ACOMPANHAR USUÁRIOS</a>
            <div class="clear"></div>
        </div>
    </div>

    <div class="box box25">
        <div class="panel_header info">
            <h2 class="icon-stats-dots">HOJE:</h2>
        </div>
        <div class="panel dashboard_stats">
            <?php
            $Read->ExeRead(DB_VIEWS_VIEWS, "WHERE views_date = date(NOW())");
            if (!$Read->getResult()):
                echo "<p class='wc_viewsusers'><b>0000</b><span>Usuários</span></p>";
                echo "<p class='wc_viewsviews'><b>0000</b><span>Visitas</span></p>";
                echo "<p class='wc_viewspages'><b>0000</b><span>Páginas</span></p>";
                echo "<h3 class='wc_viewsstats icon-shuffle'><b>0.00</b> Páginas por Visita</h3>";
            else:
                $Views = $Read->getResult()[0];
                $Stats = number_format($Views['views_pages'] / $Views['views_views'], 2, '.', '');
                echo "<p class='wc_viewsusers'><b>" . str_pad($Views['views_users'], 4, 0, STR_PAD_LEFT) . "</b><span>Usuários</span></p>";
                echo "<p class='wc_viewsviews'><b>" . str_pad($Views['views_views'], 4, 0, STR_PAD_LEFT) . "</b><span>Visitas</span></p>";
                echo "<p class='wc_viewspages'><b>" . str_pad($Views['views_pages'], 4, 0, STR_PAD_LEFT) . "</b><span>Páginas</span></p>";
                echo "<h3 class='wc_viewsstats icon-shuffle'><b>{$Stats}</b> Páginas por Visita</h3>";
            endif;
            ?>
            <div class="clear"></div>
        </div>
    </div>

    <div class="box box25">
        <div class="panel_header alert">
            <h2 class="icon-stats-dots">ESTE MÊS:</h2>
        </div>
        <div class="panel dashboard_stats">
            <?php
            $Read->FullRead("SELECT sum(views_users) AS users, sum(views_views) AS views, sum(views_pages) AS pages FROM " . DB_VIEWS_VIEWS . " WHERE year(views_date) = year(NOW()) AND month(views_date) = month(NOW())");
            if (!$Read->getResult()):
                echo "<p>0000<span>Usuários</span></p>";
                echo "<p>0000<span>Visitas</span></p>";
                echo "<p>0000<span>Páginas</span></p>";
                echo "<h3 class='icon-shuffle'>0.00 Páginas por Visita</h3>";
            else:
                $mViews = $Read->getResult()[0];
                $Stats = (!empty($mViews['pages']) ? number_format($mViews['pages'] / $mViews['views'], 2, '.', '') : '0.00');
                echo "<p>" . str_pad($mViews['users'], 4, 0, STR_PAD_LEFT) . "<span>Usuários</span></p>";
                echo "<p>" . str_pad($mViews['views'], 4, 0, STR_PAD_LEFT) . "<span>Visitas</span></p>";
                echo "<p>" . str_pad($mViews['pages'], 4, 0, STR_PAD_LEFT) . "<span>Páginas</span></p>";
                echo "<h3 class='icon-shuffle'>{$Stats} Páginas por Visita</h3>";
            endif;
            ?>
            <div class="clear"></div>
        </div>
    </div>

    <div class="box box25">
        <div class="panel_header warning">
            <h2 class="icon-stats-dots">TOTAL:</h2>
        </div>
        <div class="panel dashboard_stats">
            <?php
            $Read->FullRead("SELECT sum(views_users) AS users, sum(views_views) AS views, sum(views_pages) AS pages FROM " . DB_VIEWS_VIEWS);
            if (!$Read->getResult()):
                echo "<p>0000<span>Usuários</span></p>";
                echo "<p>0000<span>Visitas</span></p>";
                echo "<p>0000<span>Páginas</span></p>";
                echo "<h3 class='icon-shuffle'>0.00 Páginas por Visita</h3>";
            else:
                $tViews = $Read->getResult()[0];
                $Stats = (!empty($tViews['pages']) ? number_format($tViews['pages'] / $tViews['views'], 2, '.', '') : '0.00');
                echo "<p>" . str_pad($tViews['users'], 4, 0, STR_PAD_LEFT) . "<span>Usuários</span></p>";
                echo "<p>" . str_pad($tViews['views'], 4, 0, STR_PAD_LEFT) . "<span>Visitas</span></p>";
                echo "<p>" . str_pad($tViews['pages'], 4, 0, STR_PAD_LEFT) . "<span>Páginas</span></p>";
                echo "<h3 class='icon-shuffle'>{$Stats} Páginas por Visita</h3>";
            endif;
            ?>
            <div class="clear"></div>
        </div>
    </div>

    <div class="box box50">
        <div class="panel_header success">
            <h2 class="icon-home2">ÚLTIMOS IMÓVEIS:</h2>
        </div>
        <div class="panel dashboard_mostviews">
            <?php
            $Read->ExeRead(DB_IMOBI, "ORDER BY realty_date DESC LIMIT 5");
            if (!$Read->getResult()):
                echo Erro("<span class='icon-info al_center'>Ainda Não Existem Imóveis Cadastrados!</span>", E_USER_NOTICE);
            else:
                foreach ($Read->getResult() as $Realty):
                    extract($Realty);
                    $realty_views = (!empty($realty_views) ? $realty_views : 0);
                    echo "
                        <article>
                            <img src='" . BASE . "/tim.php?src=uploads/{$realty_cover}&w=" . IMAGE_W / 6 . "&h=" . IMAGE_H / 6 . "' title='{$realty_title}' alt='{$realty_title}'/>
                            <div class='info'>
                                <span>({$realty_ref}) - {$realty_views} visitas</span>
                                <h1><a target='_blank' href='" . BASE . "/imovel/{$realty_name}' title='Ver Imóvel'>{$realty_title}</a></h1>
                            </div>
                         </article>
                    ";
                endforeach;
            endif;
            ?>
            <div class="clear"></div>
        </div>
    </div>

    <div class="box box50">
        <div class="panel_header info">
            <h2 class="icon-home3">IMÓVEIS MAIS VISTOS:</h2>
        </div>
        <div class="panel dashboard_mostviews">
            <?php
            $Read->ExeRead(DB_IMOBI, "ORDER BY realty_views DESC LIMIT 5");
            if (!$Read->getResult()):
                echo Erro("<span class='icon-info al_center'>Ainda Não Existem Imóveis Cadastrados!</span>", E_USER_NOTICE);
            else:
                foreach ($Read->getResult() as $Realty):
                    extract($Realty);
                    $realty_views = (!empty($realty_views) ? $realty_views : 0);
                    echo "
                        <article>
                            <img src='" . BASE . "/tim.php?src=uploads/{$realty_cover}&w=" . IMAGE_W / 6 . "&h=" . IMAGE_H / 6 . "' title='{$realty_title}' alt='{$realty_title}'/>
                            <div class='info'>
                                <span>({$realty_ref}) - {$realty_views} visitas</span>
                                <h1><a target='_blank' href='" . BASE . "/imovel/{$realty_name}' title='Ver Imóvel'>{$realty_title}</a></h1>
                            </div>
                         </article>
                    ";
                endforeach;
            endif;
            ?>
            <div class="clear"></div>
        </div>
    </div>

    <?php
    if (APP_SEARCH):
        ?>
        <div class="box box100">
            <div class="panel_header alert">
                <h2 class="icon-search">ÚLTIMAS PESQUISAS (30 DIAS):</h2>
            </div>
            <div class="panel dashboard_search">
                <?php
                $Read->ExeRead(DB_SEARCH, "WHERE search_commit >= date(NOW() - INTERVAL 30 DAY) AND search_publish IS NULL ORDER BY search_commit DESC, search_count DESC LIMIT 5");
                if (!$Read->getResult()):
                    echo Erro("<span class='icon-info al_center'>Seus usuários ainda não pesquisaram em seu site. Assim que isso acontecer você poderá receber dicas de conteúdo pelas pesquisas realizadas!</span>", E_USER_NOTICE);
                    echo "<div class='clear'></div>";
                else:
                    foreach ($Read->getResult() as $Search):
                        extract($Search);
                        $Read->FullRead("SELECT post_id FROM " . DB_POSTS . " WHERE post_status = 1 AND post_date <= NOW() AND (post_title LIKE '%' :s '%' OR post_subtitle LIKE '%' :s '%')", "s={$search_key}");
                        $ResultPosts = $Read->getRowCount();

                        $Read->FullRead("SELECT pdt_id FROM " . DB_PDT . " WHERE pdt_status = 1 AND (pdt_title LIKE '%' :s '%' OR pdt_subtitle LIKE '%' :s '%')", "s={$search_key}");
                        $ResultPdts = $Read->getRowCount();
                        echo "
                            <article>
                               <h1 class='icon-search'><a href='dashboard.php?wc=posts/home&s=" . urlencode($search_key) . "' title='Ver resultados'>{$search_key}</a></h1>
                               <p>DIA " . date('d/m/Y H\hi', strtotime($search_date)) . "</p>
                               <p>" . str_pad($search_count, 4, 0, STR_PAD_LEFT) . " VEZES</p>
                               <p>" . str_pad($ResultPosts + $ResultPdts, 4, 0, STR_PAD_LEFT) . " RESULTADOS</p>
                                <p>
                                    <button class='btn btn_green icon-notext icon-checkmark wc_tooltip j_wc_action' data-callback='Search' data-callback-action='publish' data-value='$search_id'><span class='wc_tooltip_balloon'>Publicar</span></button>
                                    <button class='btn btn_red icon-notext icon-cross wc_tooltip j_wc_action' data-callback='Search' data-callback-action='delete' data-value='$search_id'><span class='wc_tooltip_balloon'>Deletar</span></button>
                                </p>
                            </article>
                        ";
                    endforeach;
                endif;
                ?>
                <a class="dashboard_searchnowlink" href="dashboard.php?wc=searchnow" title="Ver Mais">MAIS PESQUISAS!</a>
                <div class="clear"></div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    //ICON REFRESH IN DASHBOARD
    $('#loopDashboard').click(function () {
        Dashboard();
    });

    //DASHBOARD REALTIME
    setInterval(function () {
        Dashboard();
    }, 10000);
</script>