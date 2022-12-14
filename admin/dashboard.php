<?php
ob_start();
session_start();
require '../_app/Config.inc.php';
require '../_cdn/cronjob.php';

if (isset($_SESSION['userLogin']) && isset($_SESSION['userLogin']['user_level']) && $_SESSION['userLogin']['user_level'] >= 6):
    $Read = new Read;
    $Read->FullRead("SELECT user_level FROM " . DB_USERS . " WHERE user_id = :user", "user={$_SESSION['userLogin']['user_id']}");
    if (!$Read->getResult() || $Read->getResult()[0]['user_level'] < 6):
        unset($_SESSION['userLogin']);
        header('Location: ./index.php');
        exit;
    else:
        $Admin = $_SESSION['userLogin'];
        $Admin['user_thumb'] = (!empty($Admin['user_thumb']) && file_exists("../uploads/{$Admin['user_thumb']}") && !is_dir("../uploads/{$Admin['user_thumb']}") ? $Admin['user_thumb'] : '../admin/_img/no_avatar.jpg');
        $DashboardLogin = true;
    endif;
else:
    unset($_SESSION['userLogin']);
    header('Location: ./index.php');
    exit;
endif;

$AdminLogOff = filter_input(INPUT_GET, 'logoff', FILTER_VALIDATE_BOOLEAN);
if ($AdminLogOff):
    $_SESSION['trigger_login'] = Erro("<b>LOGOFF:</b> Olá {$Admin['user_name']}, você desconectou com sucesso do " . ADMIN_NAME . ", volte logo!");
    unset($_SESSION['userLogin']);
    header('Location: ./index.php');
    exit;
endif;

$getViewInput = filter_input(INPUT_GET, 'wc', FILTER_DEFAULT);
$getView = ($getViewInput == 'home' ? 'home' . ADMIN_MODE : $getViewInput);


//SITEMAP GENERATE (1X DAY)
$SiteMapCheck = fopen('sitemap.txt', "a+");
$SiteMapCheckDate = fgets($SiteMapCheck);
if ($SiteMapCheckDate != date('Y-m-d')):
    $SiteMapCheck = fopen('sitemap.txt', "w");
    fwrite($SiteMapCheck, date('Y-m-d'));
    fclose($SiteMapCheck);

    $SiteMap = new Sitemap;
    $SiteMap->exeSitemap(DB_AUTO_PING);
endif;
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title><?= ADMIN_NAME; ?> - <?= SITE_NAME; ?></title>
        <meta name="description" content="<?= ADMIN_DESC; ?>"/>
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0">
        <meta name="robots" content="noindex, nofollow"/>

        <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800' rel='stylesheet' type='text/css'>
        <link href='https://fonts.googleapis.com/css?family=Source+Code+Pro:300,500' rel='stylesheet' type='text/css'>
        <link rel="base" href="<?= BASE; ?>/admin/">
        <link rel="shortcut icon" href="_img/favicon.png" />

        <link rel="stylesheet" href="../_cdn/datepicker/datepicker.min.css"/>
        <link rel="stylesheet" href="_css/reset.css"/>        
        <link rel="stylesheet" href="_css/workcontrol.css"/>
        <link rel="stylesheet" href="_css/workcontrol-860.css" media="screen and (max-width: 860px)"/>
        <link rel="stylesheet" href="_css/workcontrol-480.css" media="screen and (max-width: 480px)"/>
        <link rel="stylesheet" href="../_cdn/bootcss/fonticon.css"/>

        <script src="../_cdn/jquery.js"></script>
        <script src="../_cdn/jquery.form.js"></script>
        <script src="_js/workcontrol.js"></script>

        <script src="_js/tinymce/tinymce.min.js"></script>
        <script src="_js/maskinput.js"></script>
        <script src="_js/workplugins.js"></script>

        <script src="../_cdn/highcharts.js"></script>
        <script src="../_cdn/datepicker/datepicker.min.js"></script>
        <script src="../_cdn/datepicker/datepicker.pt-BR.js"></script>

        
        <!-- tablesorter plugin -->
        <script src="../_cdn/tablesorter/dist/js/jquery.tablesorter.js"></script>
        <!-- tablesorter widget file - loaded after the plugin -->
        <script src="../_cdn/tablesorter/dist/js/jquery.tablesorter.widgets.js"></script>
    </head>
    <body class="dashboard_main">
        <div class="workcontrol_upload workcontrol_loadmodal">
            <div class="workcontrol_upload_bar">
                <img class="m_botton" width="50" src="_img/load_w.gif" alt="Processando requisição!" title="Processando requisição!"/>
                <p><span class="workcontrol_upload_progrees">0%</span> - Processando requisição!</p>
            </div>
        </div>

        <div class="dashboard_fix">
            <?php
            if (isset($_SESSION['trigger_controll'])):
                echo "<div class='trigger_modal' style='display: block'>";
                Erro("<span class='icon-warning'>{$_SESSION['trigger_controll']}</span>", E_USER_ERROR);
                echo "</div>";
                unset($_SESSION['trigger_controll']);
            endif;
            ?>

            <nav class="dashboard_nav">
                <div class="dashboard_nav_admin">
                    <img class="dashboard_nav_admin_thumb rounded" alt="" title="" src="../tim.php?src=uploads/<?= $Admin['user_thumb']; ?>&w=76&h=76"/>
                    <p><a href="dashboard.php?wc=<?= (APP_EAD ? 'teach/students_gerent' : 'users/create'); ?>&id=<?= $Admin['user_id']; ?>" title="Meu Perfil"><?= $Admin['user_name']; ?> <?= $Admin['user_lastname']; ?></a></p>
                </div>
                <ul class="dashboard_nav_menu">
                    <li class="dashboard_nav_menu_li <?= $getViewInput == 'home' ? 'dashboard_nav_menu_active' : ''; ?>"><a class="icon-home" title="Dashboard" href="dashboard.php?wc=home">Dashboard</a></li>
                    <li class="dashboard_nav_menu_li"><a  class="icon-calendar" title="Pendências" href="dashboard.php?wc=pendencias/home">Pendências</a></li>
                    <?php
                    if (APP_USERS && $_SESSION['userLogin']['user_level'] >= LEVEL_WC_USERS):
                        ?>
                        <li class="dashboard_nav_menu_li <?= strstr($getViewInput, 'users/') ? 'dashboard_nav_menu_active' : ''; ?>"><a class="icon-users" title="Colaboradores" href="dashboard.php?wc=users/home">Colaboradores</a>
                            <ul class="dashboard_nav_menu_sub">
                                <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'users/home' ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Ver Usuários" href="dashboard.php?wc=users/home">&raquo; Ver Usuários</a></li>
                                <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'users/create' ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Novo Usuário" href="dashboard.php?wc=users/create">&raquo; Novo Usuário</a></li>
                            </ul>
                        </li>
                        <?php
                    endif;

                    //SISWC verifica personalizações!
                    if (ADMIN_WC_CUSTOM && file_exists(__DIR__ . "/_siswc/wc_menu.php")):
                        require __DIR__ . "/_siswc/wc_menu.php";
                    endif;

                    
                    if (APP_POSTS && $_SESSION['userLogin']['user_level'] >= LEVEL_WC_POSTS):
                        $wc_posts_alerts = null;
                        $Read->FullRead("SELECT count(post_id) as total FROM " . DB_POSTS . " WHERE post_status != 1");
                        if ($Read->getResult() && $Read->getResult()[0]['total'] >= 1):
                            $wc_posts_alerts .= "<span class='wc_alert bar_yellow'>{$Read->getResult()[0]['total']}</span>";
                        endif;
                        ?>
                        <li class="dashboard_nav_menu_li <?= strstr($getViewInput, 'posts/') ? 'dashboard_nav_menu_active' : ''; ?>"><a class="icon-blog" title="Posts" href="dashboard.php?wc=posts/home">Posts <?= $wc_posts_alerts; ?></a>
                            <ul class="dashboard_nav_menu_sub">
                                <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'posts/home' ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Ver Posts" href="dashboard.php?wc=posts/home">&raquo; Ver Posts <?= $wc_posts_alerts; ?></a></li>
                                <li class="dashboard_nav_menu_sub_li <?= strstr($getViewInput, 'posts/categor') ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Categorias" href="dashboard.php?wc=posts/categories">&raquo; Categorias</a></li>
                                <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'posts/create' ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Novo Post" href="dashboard.php?wc=posts/create">&raquo; Novo Post</a></li>
                            </ul>
                        </li>
                        <?php
                    endif;

                    if (APP_COMMENTS && $_SESSION['userLogin']['user_level'] >= LEVEL_WC_COMMENTS):
                        $wc_comment_alerts = null;
                        $Read->FullRead("SELECT count(id) as total FROM " . DB_COMMENTS . " WHERE status != 1 AND alias_id IS NULL");
                        if ($Read->getResult() && $Read->getResult()[0]['total'] >= 1):
                            $wc_comment_alerts .= "<span class='wc_alert bar_yellow'>{$Read->getResult()[0]['total']}</span>";
                        endif;
                        ?>
                        <li class="dashboard_nav_menu_li <?= strstr($getViewInput, 'comments/') ? 'dashboard_nav_menu_active' : ''; ?>"><a class="icon-bubbles2" title="Comentários" href="dashboard.php?wc=comments/home">Comentários<?= $wc_comment_alerts; ?></a></li>
                        <?php
                    endif;

                    //WORKCONTROL E-LEARNING
                    if (APP_EAD):
                        $wc_ead_courses_alerts = null;
                        $Read->FullRead("SELECT count(course_id) as total FROM " . DB_EAD_COURSES . " WHERE course_status != 1");
                        if ($Read->getResult() && $Read->getResult()[0]['total'] >= 1):
                            $wc_ead_courses_alerts .= "<span class='wc_alert bar_yellow'>{$Read->getResult()[0]['total']}</span>";
                        endif;

                        $wc_ead_support_alerts = null;
                        $Read->FullRead("SELECT count(support_id) as total FROM " . DB_EAD_SUPPORT . " WHERE support_status = 1");
                        if ($Read->getResult() && $Read->getResult()[0]['total'] >= 1):
                            $wc_ead_support_alerts .= "<span class='wc_alert bar_yellow'>{$Read->getResult()[0]['total']}</span>";
                        endif;

                        $wc_ead_orders_alerts = null;
                        $Read->FullRead("SELECT count(order_id) as total FROM " . DB_EAD_ORDERS . " WHERE order_status = 'chargeback'");
                        if ($Read->getResult() && $Read->getResult()[0]['total'] >= 1):
                            $wc_ead_orders_alerts .= "<span class='wc_alert bar_yellow'>{$Read->getResult()[0]['total']}</span>";
                        endif;

                        $SupportEadStatus = filter_input(INPUT_GET, 'status', FILTER_VALIDATE_INT);
                        $OrdersEadStatus = filter_input(INPUT_GET, 'status', FILTER_DEFAULT);
                        ?>
                        <?php if ($Admin['user_level'] >= LEVEL_WC_EAD_COURSES): ?>
                            <li class="dashboard_nav_menu_li <?= strstr($getViewInput, 'teach/courses') ? 'dashboard_nav_menu_active' : ''; ?>"><a class="icon-books" title="Cursos" href="dashboard.php?wc=teach/courses">Cursos<?= $wc_ead_courses_alerts; ?></a>
                                <ul class="dashboard_nav_menu_sub">
                                    <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'teach/courses' ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Todos os Cursos" href="dashboard.php?wc=teach/courses">&raquo; Todos os Cursos</a></li>
                                    <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'teach/courses_segments' ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Segmentos de Cursos" href="dashboard.php?wc=teach/courses_segments">&raquo; Segmentos</a></li>
                                    <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'teach/courses_create' ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Cadastrar Novo Curso" href="dashboard.php?wc=teach/courses_create">&raquo; Novo Curso</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                        <?php if ($Admin['user_level'] >= LEVEL_WC_EAD_STUDENTS): ?><li class="dashboard_nav_menu_li <?= strstr($getViewInput, 'teach/students') ? 'dashboard_nav_menu_active' : ''; ?>"><a class="icon-user-check" title="Alunos" href="dashboard.php?wc=teach/students">Alunos</a></li><?php endif; ?>
                        <?php if ($Admin['user_level'] >= LEVEL_WC_EAD_SUPPORT): ?><li class="dashboard_nav_menu_li <?= strstr($getViewInput, 'teach/support') ? 'dashboard_nav_menu_active' : ''; ?>"><a class="icon-bubbles3" title="Suporte" href="dashboard.php?wc=teach/support">Suporte<?= $wc_ead_support_alerts; ?></a>
                                <ul class="dashboard_nav_menu_sub">
                                    <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'teach/support' && $SupportEadStatus == 1 ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Tickets Em Aberto" href="dashboard.php?wc=teach/support&support_status=1">&raquo; Em aberto <?= $wc_ead_support_alerts; ?></a></li>
                                    <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'teach/support' && $SupportEadStatus == 2 ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Tickets Respondidos" href="dashboard.php?wc=teach/support&support_status=2">&raquo; Respondidos</a></li>
                                    <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'teach/support' && $SupportEadStatus == 3 ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Tickets Concluídos" href="dashboard.php?wc=teach/support&support_status=3">&raquo; Concluídos</a></li>
                                </ul>
                            </li><?php endif; ?>                   
                        <?php
                    endif;

                    if (APP_SLIDE && $_SESSION['userLogin']['user_level'] >= LEVEL_WC_SLIDES):
                        $wc_slide_alerts = null;
                        $Read->FullRead("SELECT count(slide_id) as total FROM " . DB_SLIDES . " WHERE slide_end <= NOW()");
                        if ($Read->getResult() && $Read->getResult()[0]['total'] >= 1):
                            $wc_slide_alerts .= "<span class='wc_alert bar_yellow'>{$Read->getResult()[0]['total']}</span>";
                        endif;
                        ?>
                        <li class="dashboard_nav_menu_li <?= strstr($getViewInput, 'slide/') ? 'dashboard_nav_menu_active' : ''; ?>"><a class="icon-images" title="Em destaque" href="dashboard.php?wc=slide/home">Em destaque<?= $wc_slide_alerts; ?></a>
                            <ul class="dashboard_nav_menu_sub">
                                <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'slide/home' ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Destaques ativos" href="dashboard.php?wc=slide/home">&raquo; Em Destaque</a></li>
                                <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'slide/end' ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Agendados ou Inativos" href="dashboard.php?wc=slide/end">&raquo; Slides Inativos<?= $wc_slide_alerts; ?></a></li>
                            </ul>
                        </li>
                        <?php
                    endif;

                    if (APP_PAGES && $_SESSION['userLogin']['user_level'] >= LEVEL_WC_PAGES):
                        $wc_pages_alerts = null;
                        $Read->FullRead("SELECT count(page_id) as total FROM " . DB_PAGES . " WHERE page_status != 1");
                        if ($Read->getResult() && $Read->getResult()[0]['total'] >= 1):
                            $wc_pages_alerts .= "<span class='wc_alert bar_yellow'>{$Read->getResult()[0]['total']}</span>";
                        endif;
                        ?>
                        <li class="dashboard_nav_menu_li <?= strstr($getViewInput, 'pages/') ? 'dashboard_nav_menu_active' : ''; ?>"><a class="icon-pagebreak" title="Páginas" href="dashboard.php?wc=pages/home">Páginas<?= $wc_pages_alerts; ?></a></li>
                        <?php
                    endif;

                   

                    if ($_SESSION['userLogin']['user_level'] >= LEVEL_WC_REPORTS):
                        ?>
                        <li class="dashboard_nav_menu_li <?= strstr($getViewInput, 'report') ? 'dashboard_nav_menu_active' : ''; ?>"><a class="icon-pie-chart" title="Relatório" href="dashboard.php?wc=reports/home">Relatórios</a>
                            <ul class="dashboard_nav_menu_sub top">
                                <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'reports/home' ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Relatório de Acessos" href="dashboard.php?wc=reports/home">&raquo; Acessos</a></li>
                                <?php if (APP_EAD): ?>
                                    <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'teach/report_students' ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Relatório de Alunos" href="dashboard.php?wc=teach/report_students">&raquo; Alunos</a></li>
                                    <li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'teach/report_support' ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Relatório de Suporte" href="dashboard.php?wc=teach/report_support">&raquo; Suporte</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php
                    endif;

                    if ($Admin['user_level'] >= LEVEL_WC_CONFIG_MASTER || $Admin['user_level'] >= LEVEL_WC_CONFIG_API || $Admin['user_level'] >= LEVEL_WC_CONFIG_CODES):
                        ?>
                        <li class="dashboard_nav_menu_li <?= strstr($getViewInput, 'config/') ? 'dashboard_nav_menu_active' : ''; ?>"><a style="cursor: default;" onclick="return false;" class="icon-cogs" title="Configurações" href="#">Configurações</a>
                            <ul class="dashboard_nav_menu_sub top">
                                <?php if ($Admin['user_level'] >= LEVEL_WC_CONFIG_MASTER): ?><li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'config/home' ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Configurações Gerais" href="dashboard.php?wc=config/home">&raquo; Configurações Gerais</a></li><?php endif; ?>
                                <?php if ($Admin['user_level'] >= LEVEL_WC_CONFIG_MASTER): ?><li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'config/license' ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Licenciar Domínio" href="dashboard.php?wc=config/license">&raquo; Licenciar Domínio</a></li><?php endif; ?>
                                <?php if ($Admin['user_level'] >= LEVEL_WC_CONFIG_CODES): ?><li class="dashboard_nav_menu_sub_li <?= $getViewInput == 'config/codes' ? 'dashboard_nav_menu_active' : ''; ?>"><a title="Gerenciar Pixels" href="dashboard.php?wc=config/codes">&raquo; Gerenciar Pixels</a></li><?php endif; ?>
                            </ul>
                        </li>
                        <?php
                    endif;
                    ?>
                    <!--
                    <li class="dashboard_nav_menu_li"><a class="icon-lifebuoy" title="Suporte" href="dashboard.php?wc=home">Suporte</a></li>
                    -->
                    <li class="dashboard_nav_menu_li"><a target="_blank" class="icon-forward" title="Ver Site" href="<?= BASE; ?>">Ver Site</a></li>
                </ul>
                <div class="dashboard_nav_normalize"></div>        
            </nav>

            <div class="dashboard">
                <?php
                if (file_exists('../DATABASE.sql')):
                    echo "<div>";
                    echo Erro("<span class='al_center'><b class='icon-warning'>IMPORTANTE:</b> Para sua segurança delete o arquivo DATABASE.sql da pasta do projeto! <a class='btn btn_yellow' href='dashboard.php?wc=home&database=true' title=''>Deletar Agora!</a></span>", E_USER_ERROR);
                    echo "</div>";

                    $DeleteDatabase = filter_input(INPUT_GET, 'database', FILTER_VALIDATE_BOOLEAN);
                    if ($DeleteDatabase):
                        unlink('../DATABASE.sql');
                        header('Location: dashboard.php?wc=home');
                        exit;
                    endif;
                endif;



                //DB TEST
                $Read->FullRead("SELECT VERSION() as mysql_version");
                if ($Read->getResult()):
                    $MysqlVersion = $Read->getResult()[0]['mysql_version'];
                    if (!stripos($MysqlVersion, "MariaDB")):
                        echo "<div>";
                        echo Erro('<span class="al_center"><b class="icon-warning">ATENÇÃO:</b> O Work Control® foi projetado com <b>banco de dados MariaDB superior a 10.1</b>, você está usando ' . $MysqlVersion . '!</span>', E_USER_ERROR);
                        echo "</div>";
                    endif;
                endif;

                //PHP TEST
                $PHPVersion = phpversion();
                if ($PHPVersion < '5.6'):
                    echo "<div>";
                    echo Erro('<span class="al_center"><b class="icon-warning">ATENÇÃO:</b> O Work Control® foi projetado com <b>PHP 5.6 ou superior</b>, a versão do seu PHP é ' . $PHPVersion . '!</span>', E_USER_ERROR);
                    echo "</div>";
                endif;
                ?>
                <div class="dashboard_sidebar">
                    <span class="mobile_menu btn btn_blue icon-menu icon-notext"></span>
                    <div class="fl_right">
                        <span class="dashboard_sidebar_welcome m_right">Bem-vindo(a) ao <?= ADMIN_NAME; ?>, Hoje <?= date('d/m/y H\hi'); ?></span>
                        <a class="icon-exit btn btn_red" title="Desconectar do <?= ADMIN_NAME; ?>!" href="dashboard.php?wc=home&logoff=true">Sair!</a>
                    </div>
                </div>

                <?php
                //QUERY STRING
                if (!empty($getView)):
                    $includepatch = __DIR__ . '/_sis/' . strip_tags(trim($getView)) . '.php';
                else:
                    $includepatch = __DIR__ . '/_sis/' . 'dashboard.php';
                endif;

                if (file_exists(__DIR__ . "/_siswc/" . strip_tags(trim($getView)) . '.php')):
                    require_once __DIR__ . "/_siswc/" . strip_tags(trim($getView)) . '.php';
                elseif (file_exists($includepatch)):
                    require_once($includepatch);
                else:
                    $_SESSION['trigger_controll'] = "<b>OPPSSS:</b> <span class='fontred'>_sis/{$getView}.php</span> ainda está em contrução!";
                    header('Location: dashboard.php?wc=home');
                    exit;
                endif;
                ?>
            </div>
        </div>
    </body>
</html>
<?php
ob_end_flush();
