<?php
$AdminLevel = LEVEL_WC_POSTS;
if (!APP_POSTS || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

//AUTO DELETE POST TRASH
if (DB_AUTO_TRASH):
    $Delete = new Delete;
    $Delete->ExeDelete(DB_POSTS, "WHERE post_title IS NULL AND post_content IS NULL and post_status = :st", "st=0");

    //AUTO TRASH IMAGES
    $Read->FullRead("SELECT image FROM " . DB_POSTS_IMAGE . " WHERE post_id NOT IN(SELECT post_id FROM " . DB_POSTS . ")");
    if ($Read->getResult()):
        $Delete->ExeDelete(DB_POSTS_IMAGE, "WHERE id >= :id AND post_id NOT IN(SELECT post_id FROM " . DB_POSTS . ")", "id=1");
        foreach ($Read->getResult() as $ImageRemove):
            if (file_exists("../uploads/{$ImageRemove['image']}") && !is_dir("../uploads/{$ImageRemove['image']}")):
                unlink("../uploads/{$ImageRemove['image']}");
            endif;
        endforeach;
    endif;
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

$S = filter_input(INPUT_GET, "s", FILTER_DEFAULT);
$C = filter_input(INPUT_GET, "cat", FILTER_DEFAULT);
$T = filter_input(INPUT_GET, "tag", FILTER_DEFAULT);

$Search = filter_input_array(INPUT_POST);
if ($Search && (isset($Search['s']) || isset($Search['status']))):
    $S = (isset($Search['s']) ? urlencode($Search['s']) : $S);
    $SearchCat = (!empty($Search['searchcat']) ? $Search['searchcat'] : null);
    header("Location: dashboard.php?wc=posts/home&s={$S}&cat={$SearchCat}&tag={$T}");
    exit;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-blog">Posts<?= ($C ? " por Categoria" : ""); ?><?= ($T ? " em {$T}" : ""); ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="Todos os Posts" href="dashboard.php?wc=posts/home">Posts</a>
            <?= ($S ? "<span class='crumb'>/</span> <span class='icon-search'>{$S}</span>" : ''); ?>
        </p>
    </div>

    <div class="dashboard_header_search">

        <form style="width: 100%; display: inline-block;" name="searchCategoriesPost" action="" method="post" enctype="multipart/form-data" class="ajax_off">
            <input type="search" value="<?= $S; ?>" name="s" placeholder="Pesquisar:" style="width: 38%; margin-right: 3px;">
            <select name="searchcat" style="width: 45%; margin-right: 3px; padding: 5px 10px">
                <option value="">Todos</option>
                <?php
                $Read->FullRead("SELECT category_id, category_title FROM " . DB_CATEGORIES . " WHERE category_parent IS NULL ORDER BY category_title ASC");
                if (!$Read->getResult()):
                    echo "<option value='' disabled='disabled'>Não existem categorias cadastradas!</option>";
                else:
                    foreach ($Read->getResult() as $SearchCategory):
                        echo "<option " . ($C == $SearchCategory['category_id'] ? "selected='selected'" : null) . " value='{$SearchCategory['category_id']}'>{$SearchCategory['category_title']}</option>";

                        $Read->FullRead("SELECT category_id, category_title FROM " . DB_CATEGORIES . " WHERE category_parent = {$SearchCategory['category_id']} ORDER BY category_title ASC");
                        if ($Read->getResult()):
                            foreach ($Read->getResult() as $SearchCategorySub):
                                echo "<option " . ($C == $SearchCategorySub['category_id'] ? "selected='selected'" : null) . " value='{$SearchCategorySub['category_id']}'> &raquo; {$SearchCategorySub['category_title']}</option>";
                            endforeach;
                        endif;
                    endforeach;
                endif;
                ?>
            </select>
            <button class="btn btn_green icon icon-search icon-notext"></button>
        </form>        
    </div>
</header>

<div class="dashboard_content">
    <?php
    $getPage = filter_input(INPUT_GET, 'pg', FILTER_VALIDATE_INT);
    $Page = ($getPage ? $getPage : 1);
    $Paginator = new Pager("dashboard.php?wc=posts/home&s={$S}&cat={$C}&tag={$T}&pg=", '<<', '>>', 5);
    $Paginator->ExePager($Page, 12);

    if (!empty($C)):
        $WhereCat[0] = "AND ((post_category = :cat OR FIND_IN_SET(:cat, post_category_parent)) OR :cat = '')";
        $WhereCat[1] = "&cat={$C}";
    else:
        $WhereCat[0] = "";
        $WhereCat[1] = "";
    endif;
    
    if (!empty($T)):
        $WhereTag[0] = "AND post_tags LIKE '%' :tag '%'";
        $WhereTag[1] = "&tag={$T}";
    else:
        $WhereTag[0] = "";
        $WhereTag[1] = "";
    endif;
    
    if (!empty($S)):
        $WhereString[0] = "AND (post_title LIKE '%' :s '%' OR post_content LIKE '%' :s '%')";
        $WhereString[1] = "&s={$S}";
    else:
        $WhereString[0] = "";
        $WhereString[1] = "";
    endif;

    $Read->FullRead("SELECT * FROM " . DB_POSTS . " WHERE 1=1 "
            . "{$WhereCat[0]} "
            . "{$WhereTag[0]} "
            . "{$WhereString[0]} "
            . "ORDER BY post_status ASC, post_date DESC "
            . "LIMIT :limit OFFSET :offset", "limit={$Paginator->getLimit()}&offset={$Paginator->getOffset()}{$WhereCat[1]}{$WhereTag[1]}{$WhereString[1]}"
    );
            
    if (!$Read->getResult()):
        $Paginator->ReturnPage();
        echo Erro("<span class='al_center icon-notification'>Ainda não existem posts cadastrados {$Admin['user_name']}. Comece agora mesmo criando seu primeiro post!</span>", E_USER_NOTICE);
    else:
        foreach ($Read->getResult() as $POST):
            extract($POST);

            $PostCover = (file_exists("../uploads/{$post_cover}") && !is_dir("../uploads/{$post_cover}") ? "uploads/{$post_cover}" : 'admin/_img/no_image.jpg');
            $PostStatus = ($post_status == 1 && strtotime($post_date) >= strtotime(date('Y-m-d H:i:s')) ? '<span class="btn btn_blue icon-clock icon-notext wc_tooltip"><span class="wc_tooltip_baloon">Agendado</span></span>' : ($post_status == 1 ? '<span class="btn btn_green icon-checkmark icon-notext wc_tooltip"><span class="wc_tooltip_baloon">Publicado</span></span>' : '<span class="btn btn_yellow icon-warning icon-notext wc_tooltip"><span class="wc_tooltip_baloon">Pendente</span></span>'));
            $post_title = (!empty($post_title) ? $post_title : 'Edite esse rascunho para poder exibir como artigo em seu site!');

            $postTags = null;
            if ($post_tags):
                foreach (explode(",", $post_tags) AS $tags):
                    $tag = ltrim(rtrim($tags));
                    $postTags .= "<a class='icon-price-tag radius' title='Artigos marcados com {$tag}' href='dashboard.php?wc=posts/home&s={$S}&cat={$C}&tag=" . urlencode($tag) . "'>{$tag}</a>";
                endforeach;
            endif;

            $Category = null;
            if (!empty($post_category)):
                $Read->FullRead("SELECT category_id, category_title FROM " . DB_CATEGORIES . " WHERE category_id = :ct", "ct={$post_category}");
                if ($Read->getResult()):
                    $Category = "<span class='icon-bookmark'><a title='Artigos em {$Read->getResult()[0]['category_title']}' href='dashboard.php?wc=posts/home&s={$S}&cat={$Read->getResult()[0]['category_id']}&tag=" . urlencode($T) . "'>{$Read->getResult()[0]['category_title']}</a></span> ";
                endif;
            endif;

            if (!empty($post_category_parent)):
                $Read->FullRead("SELECT category_title, category_id FROM " . DB_CATEGORIES . " WHERE category_id IN({$post_category_parent})");
                if ($Read->getResult()):
                    foreach ($Read->getResult() as $SubCat):
                        $Category .= "<span class='icon-bookmarks'><a title='Artigos em {$SubCat['category_title']}' href='dashboard.php?wc=posts/home&s={$S}&cat={$SubCat['category_id']}&tag=" . urlencode($T) . "'>{$SubCat['category_title']}</a></span> ";
                    endforeach;
                endif;
            endif;

            echo "<article class='box box25 post_single' id='{$post_id}'>           
                <div class='post_single_cover'>
                    <a title='Ver artigo no site' target='_blank' href='" . BASE . "/artigo/{$post_name}'><img alt='{$post_title}' title='{$post_title}' src='../tim.php?src={$PostCover}&w=" . IMAGE_W / 2 . "&h=" . IMAGE_H / 2 . "'/></a>
                    <div class='post_single_status'>
                        <span class='btn wc_tooltip'>" . str_pad($post_views, 4, 0, STR_PAD_LEFT) . " <span class='wc_tooltip_baloon'>Visualizações</span></span>
                        {$PostStatus}
                        " . (APP_POSTS_INSTANT_ARTICLE && $post_instant_article == '1' ? "<span class='btn btn_blue icon-facebook2 icon-notext wc_tooltip'><span class='wc_tooltip_baloon'>Instant Article</span></span>" : '') . " 
                    </div>
                    " . (!empty($postTags) ? "<div class='post_single_tag'>{$postTags}</div>" : "") . "
                </div>
                <div class='post_single_content wc_normalize_height'>
                    <h1 class='title'><a title='Ver artigo no site' target='_blank' href='" . BASE . "/artigo/{$post_name}'>{$post_title}</a></h1>
                    <p class='post_single_cat'>{$Category}</p>
                </div>
                <div class='post_single_actions'>
                    <a title='Editar Artigo' href='dashboard.php?wc=posts/create&id={$post_id}' class='post_single_center icon-pencil btn btn_blue'>Editar</a>
                    <span rel='post_single' class='j_delete_action icon-cancel-circle btn btn_red' id='{$post_id}'>Deletar</span>
                    <span rel='post_single' callback='Posts' callback_action='delete' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='{$post_id}'>Deletar Post?</span>
                </div>
            </article>";
        endforeach;

        $Paginator->ExePaginator(DB_POSTS, "WHERE "
                . "((post_category = :cat OR FIND_IN_SET(:cat, post_category_parent)) OR :cat = '') "
                . "AND (FIND_IN_SET(:tag, post_tags) OR :tag = '') "
                . "AND (post_title LIKE '%' :s '%' OR post_content LIKE '%' :s '%')", "cat={$C}&tag={$T}&s={$S}"
        );
        echo $Paginator->getPaginator();
    endif;
    ?>
</div>