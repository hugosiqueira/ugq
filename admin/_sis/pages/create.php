<?php
$AdminLevel = LEVEL_WC_PAGES;
if (!APP_PAGES || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

// AUTO INSTANCE OBJECT CREATE
if (empty($Create)):
    $Create = new Create;
endif;

$PageId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($PageId):
    $Read->ExeRead(DB_PAGES, "WHERE page_id = :id", "id={$PageId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);
    else:
        $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou editar uma página que não existe ou que foi removida recentemente!", E_USER_NOTICE);
        header('Location: dashboard.php?wc=pages/home');
        exit;
    endif;
else:
    $PageCreate = ['page_date' => date('Y-m-d H:i:s'), 'page_status' => 0];
    $Create->ExeCreate(DB_PAGES, $PageCreate);
    header('Location: dashboard.php?wc=pages/create&id=' . $Create->getResult());
    exit;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-page-break"><?= $page_title ? $page_title : 'Nova Página'; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=pages/home">Páginas</a>
            <span class="crumb">/</span>
            Gerenciar Página
        </p>
    </div>

    <div class="dashboard_header_search">
        <a target="_blank" title="Ver no site" href="<?= BASE; ?>/<?= $page_name; ?>" class="wc_view btn btn_green icon-eye">Ver página no site!</a>
    </div>
</header>

<div class="workcontrol_imageupload none" id="post_control">
    <div class="workcontrol_imageupload_content">
        <form name="workcontrol_post_upload" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="callback" value="Pages"/>
            <input type="hidden" name="callback_action" value="sendimage"/>
            <input type="hidden" name="page_id" value="<?= $PageId; ?>"/>
            <div class="upload_progress none" style="padding: 5px; background: #00B594; color: #fff; width: 0%; text-align: center; max-width: 100%;">0%</div>
            <div style="overflow: auto; max-height: 300px;">
                <img class="image image_default" alt="Nova Imagem" title="Nova Imagem" src="../tim.php?src=admin/_img/no_image.jpg&w=<?= IMAGE_W; ?>&h=<?= IMAGE_H; ?>" default="../tim.php?src=admin/_img/no_image.jpg&w=<?= IMAGE_W; ?>&h=<?= IMAGE_H; ?>"/>
            </div>
            <div class="workcontrol_imageupload_actions">
                <input class="wc_loadimage" type="file" name="image" required/>
                <span class="workcontrol_imageupload_close icon-cancel-circle btn btn_red" id="post_control" style="margin-right: 8px;">Fechar</span>
                <button class="btn btn_green icon-image">Enviar e Inserir!</button>
                <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
            </div>
            <div class="clear"></div>
        </form>
    </div>
</div>

<div class="dashboard_content">

    <form class="auto_save" name="page_add" action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="callback" value="Pages"/>
        <input type="hidden" name="callback_action" value="manage"/>
        <input type="hidden" name="page_id" value="<?= $PageId; ?>"/>

        <div class="box box70">

            <div class="panel_header default">
                <h2 class="icon-page-break">Insira as informações da Página</h2>
            </div>

            <div class="panel">
                <label class="label">
                    <span class="legend">Título:</span>
                    <input style="font-size: 1.4em;" type="text" name="page_title" value="<?= $page_title; ?>" placeholder="Título da Página:" required/>
                </label>

                <label class="label">
                    <span class="legend">Descrição:</span>
                    <textarea style="font-size: 1.2em;" name="page_subtitle" rows="3" placeholder="Sobre a Página:" required><?= $page_subtitle; ?></textarea>
                </label>

                <label class="label">
                    <span class="legend">Conteúdo:</span>
                    <textarea name="page_content" class="work_mce" rows="10" placeholder="Conteúdo da Página:"><?= $page_content; ?></textarea>
                </label>
                <div class="clear"></div>
            </div>
        </div>

        <div class="box box30">

            <div class="panel_header default">
                <h2>Dados Adicionais</h2>
            </div>

            <div class="panel">
                <div class="post_create_cover m_botton">
                    <div class="upload_progress none">0%</div>
                    <?php
                    $PageCover = (!empty($page_cover) && file_exists("../uploads/{$page_cover}") && !is_dir("../uploads/{$page_cover}") ? "uploads/{$page_cover}" : 'admin/_img/no_image.jpg');
                    ?>
                    <img class="post_thumb page_cover" alt="Capa" title="Capa" src="../tim.php?src=<?= $PageCover; ?>&w=<?= IMAGE_W / 3; ?>&h=<?= IMAGE_H / 3; ?>" default="../tim.php?src=<?= $PageCover; ?>&w=<?= IMAGE_W / 3; ?>&h=<?= IMAGE_H / 3; ?>"/>
                </div>

                <label class="label">
                    <span class="legend">Capa:</span>
                    <input type="file" class="wc_loadimage" name="page_cover"/>
                </label>

                <?php if (APP_LINK_PAGES): ?>
                    <label class="label">
                        <span class="legend">Link Alternativo (Opcional):</span>
                        <input id="page_add" type="text" name="page_name" value="<?= $page_name; ?>" placeholder="Link da Página:"/>
                    </label>
                <?php endif; ?>

                <div class="m_top">&nbsp;</div>
                <div class="wc_actions" style="text-align: center; margin-bottom: 10px;">
                    <label class="label_check label_publish <?= ($page_status == 1 ? 'active' : ''); ?>"><input style="margin-top: -1px;" type="checkbox" value="1" name="page_status" <?= ($page_status == 1 ? 'checked' : ''); ?>> Publicar Agora!</label>
                    <button name="public" value="1" class="btn btn_green icon-share">ATUALIZAR</button>
                    <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                </div>
            </div>
        </div>
    </form>
</div>