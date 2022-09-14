<?php
$AdminLevel = LEVEL_WC_EAD_COURSES;
if (!APP_EAD || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
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


$SegmentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($SegmentId):
    $Read->ExeRead(DB_EAD_COURSES_SEGMENTS, "WHERE segment_id = :id", "id={$SegmentId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);
    else:
        $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou editar um segmento de curso que não existe ou que foi removido recentemente!", E_USER_NOTICE);
        header('Location: dashboard.php?wc=teach/courses_segments');
    endif;
else:
    $Read->FullRead("SELECT segment_id FROM " . DB_EAD_COURSES_SEGMENTS);
    $SegmentOrder = $Read->getRowCount() + 1;

    $Date = date('Y-m-d H:i:s');
    $PostCreate = ['segment_created' => $Date, "segment_order" => $SegmentOrder];
    $Create->ExeCreate(DB_EAD_COURSES_SEGMENTS, $PostCreate);
    header('Location: dashboard.php?wc=teach/courses_segmentedit&id=' . $Create->getResult());
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-share2"><?= $segment_title ? $segment_title : 'Novo Segmento'; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses">Cursos</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses_segments">Segmentos</a>
            <span class="crumb">/</span>
            Gerenciar Segmento
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Ver Segmentos!" href="dashboard.php?wc=teach/courses_segments" class="btn btn_blue icon-eye">Ver Segmentos!</a>
        <a title="Novo Segmento!" href="dashboard.php?wc=teach/courses_segmentedit" class="btn btn_green icon-plus">Novo Segmento!</a>
    </div>

</header>

<div class="dashboard_content">
    <form class="auto_save" name="category_add" action="" method="post" enctype="multipart/form-data">
        <div class="callback_return"></div>
        <input type="hidden" name="callback" value="Courses"/>
        <input type="hidden" name="callback_action" value="segment_manager"/>
        <input type="hidden" name="segment_id" value="<?= $segment_id; ?>"/>

        <div class="box box100">
            <div class="box_content">
                <label class="label">
                    <span class="legend">Segmento:</span>
                    <input style="font-size: 1.5em;" type="text" name="segment_title" value="<?= $segment_title; ?>" placeholder="Título do Segmento:" required/>
                </label>

                <label class="label">
                    <span class="legend">Descrição:</span>
                    <textarea name="segment_desc" rows="3" required><?= $segment_desc; ?></textarea>
                </label>

                <label class="label">
                    <span class="legend">Ícone: <a class="icon-IcoMoon" target="_blank" title="Consultar Ícones" href="dashboard.php?wc=config/samples#icons">VER ÍCONES DISPONÍVEIS!</a></span>
                    <input style="font-size: 1.5em;" type="text" name="segment_icon" value="<?= $segment_icon; ?>" placeholder="Ícone do Segmento:" required/>
                </label>

                <div class="wc_actions">
                    <button class="btn btn_green icon-price-tags">ATUALIZAR SEGMENTO!</button>
                    <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                </div>
            </div>
        </div>
    </form>
</div>