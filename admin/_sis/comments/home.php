<?php
$AdminLevel = LEVEL_WC_COMMENTS;
if (!APP_COMMENTS || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-bubbles2">Comentários</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            Comentários
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Recarregar Comentários" href="dashboard.php?wc=comments/home" class="btn btn_blue icon-spinner11 icon-notext"></a>
        <a title="Recarregar Comentários" href="dashboard.php?wc=comments/comment_response" class="btn btn_green icon-play3">Play</a>
    </div>
</header>

<div class="dashboard_content">
    <section class="box box100">

        <div class="panel_header default">
            <h2 class="icon-bubbles3">#Responda seus comentários</h2>
        </div>

        <div class="panel">
            <?php
            //PAGINATOR
            $getPage = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT);
            $Page = ($getPage ? $getPage : 1);
            $Pager = new Pager("dashboard.php?wc=comments/home&p=", "<<", ">>", 1);
            $Pager->ExePager($Page, 10);

            //READ COMMENT
            $Read->ExeRead(DB_COMMENTS, "WHERE alias_id IS NULL ORDER BY status DESC, created DESC LIMIT :limit OFFSET :offset", "limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
            if (!$Read->getResult()):
                $Pager->ReturnPage();
                echo Erro("<span class='icon-info al_center'>Ainda não existem comentários {$_SESSION['userLogin']['user_name']}. Mas isso não deve demorar!</span>", E_USER_NOTICE);
            else:

                //STATUS
                $SupportStatus = [1 => 'Respondido', 2 => 'Em Aberto', 3 => 'Moderar'];
                $SupportStatusClass = [1 => 'btn_blue icon-bubbles3', 2 => 'btn_red icon-bubble2'];

                foreach ($Read->getResult() as $Comm):
                    
                    //USERS
                    $Read->FullRead("SELECT user_id, user_name, user_lastname, user_thumb, user_email FROM " . DB_USERS . " WHERE user_id = :id", "id={$Comm['user_id']}");
                    $UserId = $Read->getResult()[0]['user_id'];
                    $User = "{$Read->getResult()[0]['user_name']} {$Read->getResult()[0]['user_lastname']}";
                    $UserEmail = $Read->getResult()[0]['user_email'];
                    
                    //SOURCE COMMENT
                    if ($Comm['post_id']):
                        $Read->FullRead("SELECT post_name, post_title FROM " . DB_POSTS . " WHERE post_id = :id", "id={$Comm['post_id']}");
                        $Link = "artigo/{$Read->getResult()[0]['post_name']}";
                        $Title = $Read->getResult()[0]['post_title'];
                    elseif ($Comm['pdt_id']):
                        $Read->FullRead("SELECT pdt_name, pdt_title FROM " . DB_PDT . " WHERE pdt_id = :id", "id={$Comm['pdt_id']}");
                        $Link = "produto/{$Read->getResult()[0]['pdt_name']}";
                        $Title = $Read->getResult()[0]['pdt_title'];
                    elseif ($Comm['page_id']):
                        $Read->FullRead("SELECT page_name, page_title FROM " . DB_PAGES . " WHERE page_id = :id", "id={$Comm['page_id']}");
                        $Link = "{$Read->getResult()[0]['page_name']}";
                        $Title = $Read->getResult()[0]['page_title'];
                    endif;
                    
                    //COUNT REPLIES
                    $Read->ExeRead(DB_COMMENTS, "WHERE alias_id = :id", "id={$Comm['id']}");
                    $Reply = $Read->getResult();
                    $ReplyCount = ($Reply ? $Read->getRowCount() : '0');

                    //RANK
                    $Stars = str_repeat("<span class='icon-star-full icon-notext font_green review'></span>", $Comm['rank']);
                    ?>
                    <article class="ead_support_single single_comment" id="<?= $Comm['id']; ?>">
                        <h1 class="row">
                            <a class="a" target="_blank" href='<?= BASE; ?>/<?= $Link; ?>#<?= $Comm['id']; ?>' title='Ver Fórum na Aula'>#<?= str_pad($Comm['id'], 4, 0, 0); ?> - <?= $Title; ?></a>
                        </h1><p class="row icon-user-plus">
                            Por <a class="a" target="_blank"
                                   href="dashboard.php?wc=<?= (APP_EAD == 1 ? 'teach/students_gerent' : 'users/create') ?>&id=<?= $Comm['user_id']; ?>"
                                   title="<?= "{$User}"; ?>"><?= "{$User}"; ?></a>
                            <span><?= $UserEmail; ?></span>
                        </p><p class="row icon-bubble2">
                            <?= $ReplyCount; ?> Resposta<?= ($ReplyCount >= 2) ? 's' : ''?> - <?= $Stars; ?>
                        </p><p class="row icon-hour-glass">
                            <?= date('d/m/Y H\hi', strtotime(($Comm['interact'] ? $Comm['interact'] : $Comm['created']))); ?>
                        </p><p class="row btn_support">
                            <a title="Responder Ticket" href="dashboard.php?wc=comments/comment_response&id=<?= $Comm['id']; ?>" class="btn btn_blue <?= $SupportStatusClass[$Comm['status']]; ?>"><?= $SupportStatus[$Comm['status']]; ?></a>
                        </p>
                    </article>

                    <?php
                endforeach;
                $Pager->ExePaginator(DB_COMMENTS, "WHERE alias_id IS NULL");
                echo $Pager->getPaginator();
            endif;
            ?>
            <div class="clear"></div>
        </div>
    </section>
</div>