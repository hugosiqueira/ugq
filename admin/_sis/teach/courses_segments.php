<?php
$AdminLevel = LEVEL_WC_EAD_COURSES;
if (!APP_EAD || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-share2">Segmentos de Cursos</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses">Cursos</a>
            <span class="crumb">/</span>
            Segmentos
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Novo Fabricante" href="dashboard.php?wc=teach/courses_segmentedit" class="btn btn_green icon-plus">Novo Segmento!</a>
        <span class="btn btn_blue icon-spinner9 wc_drag_active" title="Organizar Cursos">Ordenar</span>
    </div>
</header>

<div class="dashboard_content">
    <?php
    $Read->ExeRead(DB_EAD_COURSES_SEGMENTS, "ORDER BY segment_order ASC, segment_title ASC");
    if (!$Read->getResult()):
        echo Erro("<span class='al_center icon-notification'>Ainda não existem segmentos cadastrados {$Admin['user_name']}. Comece agora mesmo criando seu primeiro segmento de curso!</span>", E_USER_NOTICE);
    else:
        foreach ($Read->getResult() as $Segment):
            extract($Segment);

            $Read->FullRead("SELECT count(course_id) as TotalCourse FROM " . DB_EAD_COURSES . " WHERE course_segment = :segment", "segment={$Segment['segment_id']}");
            $TotalCoursesBySegment = str_pad($Read->getResult()[0]['TotalCourse'], 2, 0, 0);

            $Segment['segment_title'] = ($Segment['segment_title'] ? $Segment['segment_title'] : 'Edite este segmento!');
            ?>
            <article class="course_segment box box100 wc_draganddrop" callback="Courses" callback_action="segment_order" id="<?= $Segment['segment_id']; ?>">
                <div class='box_content'>
                    <h1 class='<?= $Segment['segment_icon']; ?>'><b><?= $Segment['segment_title']; ?> <a title='Ver Cursos' href='dashboard.php?wc=teach/courses&segment=<?= $Segment['segment_id']; ?>'><?= $TotalCoursesBySegment; ?> cursos aqui!</a></b></h1>
                    <p><?= $Segment['segment_desc']; ?></p>
                    <a title='Editar Segmento!' href='dashboard.php?wc=teach/courses_segmentedit&id=<?= $Segment['segment_id']; ?>' class='btn btn_blue icon-pencil icon-notext'></a>
                    <span rel='course_segment' class='j_delete_action btn btn_red icon-cancel-circle icon-notext' id='<?= $Segment['segment_id']; ?>'></span>
                    <span rel='course_segment' callback='Courses' callback_action='segment_remove' class='j_delete_action_confirm btn btn_yellow icon-warning' style='display: none;' id='<?= $Segment['segment_id']; ?>'>Deletar Segmento?</span>
                </div>
            </article>
            <?php
        endforeach;
    endif;
    ?>
</div>