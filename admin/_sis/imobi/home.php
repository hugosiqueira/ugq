<?php
$AdminLevel = LEVEL_WC_IMOBI;
if (!APP_IMOBI || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

$Search = filter_input_array(INPUT_POST);
if ($Search && $Search['s']):
    $S = urlencode($Search['s']);
    header("Location: dashboard.php?wc=imobi/search&s={$S}");
    exit;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-home3">Imóveis</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            Imóveis
        </p>
    </div>

    <div class="dashboard_header_search">
        <form name="searchImobi" action="" method="post" enctype="multipart/form-data" class="ajax_off">
            <input type="search" name="s" placeholder="Pesquisar Imóvel:" required/>
            <button class="btn btn_green icon icon-search icon-notext"></button>
        </form>
    </div>

</header>
<div class="dashboard_content">
    <?php
    $getPage = filter_input(INPUT_GET, 'pg', FILTER_VALIDATE_INT);
    $Page = ($getPage ? $getPage : 1);
    $Paginator = new Pager('dashboard.php?wc=imobi/home&pg=', '<<', '>>', 5);
    $Paginator->ExePager($Page, 12);

    $Read->ExeRead(DB_IMOBI, "WHERE realty_status = 1 ORDER BY realty_date DESC LIMIT :limit OFFSET :offset", "limit={$Paginator->getLimit()}&offset={$Paginator->getOffset()}");
    if (!$Read->getResult()):
        $Paginator->ReturnPage();
        echo Erro("<span class='al_center icon-notification'>Ainda não existem imóveis cadastrados {$Admin['user_name']}. Comece agora mesmo cadastrando seu primeiro imóvel!</span>", E_USER_NOTICE);
    else:
        foreach ($Read->getResult() as $REALTY):
            extract($REALTY);

            $RealTyCover = (file_exists("../uploads/{$realty_cover}") && !is_dir("../uploads/{$realty_cover}") ? "uploads/{$realty_cover}" : 'admin/_img/no_image.jpg');
            $RealTyStatus = ($realty_status == 1 ? '<span class="btn btn_green icon-checkmark icon-notext"></span>' : '<span class="btn btn_yellow icon-warning icon-notext"></span>');
            $RealTyType = (getWcRealtyType($realty_type) && is_string(getWcRealtyType($realty_type)) ? getWcRealtyType($realty_type) : 'Indefinido');
            $realty_title = (!empty($realty_title) ? $realty_title : 'Edite esse rascunho para exibir o imóvel no site!');
            $realty_observation = ($realty_observation && is_string(getWcRealtyNote($realty_observation)) ? getWcRealtyNote($realty_observation) : null);

            echo "<article class='box box25 imobi_single' id='{$realty_id}'>           
                <div class='post_single_cover'>
                    <img alt='{$realty_title}' title='{$realty_title}' src='../tim.php?src={$RealTyCover}&w=" . IMAGE_W / 3 . "&h=" . IMAGE_H / 3 . "'/>
                    <div class='post_single_status'><span class='btn'>" . str_pad($realty_views, 4, 0, STR_PAD_LEFT) . "</span>{$RealTyStatus}</div>
                </div>
                <div class='box_content wc_normalize_height'>
                    <p class='info icon-home2'>{$RealTyType} " . (is_string(getWcRealtyFinality($realty_finality)) ? getWcRealtyFinality($realty_finality) : '') . "</p>
                    <p class='info icon-info'>{$realty_ref}" . (is_string(getWcRealtyTransaction($realty_transaction)) ? " - " . getWcRealtyTransaction($realty_transaction) : '') . "</p>
                    <h1 class='title'><a title='Ver imóvel no site' target='_blank' href='" . BASE . "/imovel/{$realty_name}'>" . Check::Chars($realty_title, 40) . "</a> <b class='font_yellow'> " . ($realty_price ? "R$&nbsp;" . number_format($realty_price, 2, ',', '.') : 'A Combinar') . "</b></h1>
                    <p class='resume'>" . Check::Chars($realty_desc, 120) . "</p>
                    <p class='wc_imobi_tag'>ÁREA ÚTIL: {$realty_builtarea}m<sup>2</sup></p><p class='wc_imobi_tag'>ÁREA TOTAL: {$realty_totalarea}m<sup>2</sup></p>
                    <p class='wc_imobi_tag'>QUARTOS: {$realty_bathrooms}</p><p class='wc_imobi_tag'>SUÍTES: {$realty_apartments}</p>
                    <p class='wc_imobi_tag'>BANHEIROS: {$realty_bathrooms}</p><p class='wc_imobi_tag'>GARAGEM: {$realty_parkings}</p>
                </div>
                <div class='box_actions'>
                    <a title='Editar Imóvel' href='dashboard.php?wc=imobi/create&id={$realty_id}' class='post_single_center icon-pencil btn btn_blue'>Editar</a>
                    <span rel='imobi_single' class='j_delete_action icon-cancel-circle btn btn_red' id='{$realty_id}'>Excluir</span>
                    <span rel='imobi_single' callback='Properties' callback_action='delete' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='{$realty_id}'>Deletar Imóvel?</span>
                </div>
            </article>";
        endforeach;

        $Paginator->ExePaginator(DB_IMOBI, "WHERE realty_status = 1");
        echo $Paginator->getPaginator();
    endif;
    ?>
</div>