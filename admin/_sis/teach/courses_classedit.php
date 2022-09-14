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

// AUTO INSTANCE OBJECT UPDATE
if (empty($Update)):
    $Update = new Update;
endif;

$ClassId = filter_input(INPUT_GET, 'class', FILTER_VALIDATE_INT);
$ModuleId = filter_input(INPUT_GET, 'module', FILTER_VALIDATE_INT);
$CourseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($ClassId):
    $Read->ExeRead(DB_EAD_CLASSES, "WHERE class_id = :id", "id={$ClassId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);

        $Read->LinkResult(DB_EAD_MODULES, "module_id", $module_id);
        extract($Read->getResult()[0]);

        $Read->LinkResult(DB_EAD_COURSES, "course_id", $course_id);
        extract($Read->getResult()[0]);
    elseif ($ModuleId):
        $Read->FullRead("SELECT course_id FROM " . DB_EAD_MODULES . " WHERE module_id = :mod", "mod={$ModuleId}");
        if ($Read->getResult()):
            $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou editar uma aula que não existe ou foi removida recentemente!", E_USER_NOTICE);
            header('Location: dashboard.php?wc=teach/courses_classes&id=' . $Read->getResult()[0]['course_id'] . '&module=' . $ModuleId);
            exit;
        else:
            $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou editar uma aula sem informar o módulo a que ela pertence!", E_USER_NOTICE);
            header('Location: dashboard.php?wc=teach/courses');
            exit;
        endif;
    endif;
elseif ($ModuleId):
    $Read->FullRead("SELECT course_id FROM " . DB_EAD_MODULES . " WHERE module_id = :mod", "mod={$ModuleId}");
    if ($Read->getResult()):
        $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou editar uma aula que não existe ou foi removida recentemente!", E_USER_NOTICE);
        header('Location: dashboard.php?wc=teach/courses_classes&id=' . $Read->getResult()[0]['course_id'] . '&module=' . $ModuleId);
        exit;
    else:
        $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou editar uma aula sem informar o módulo a que ela pertence!", E_USER_NOTICE);
        header('Location: dashboard.php?wc=teach/courses');
        exit;
    endif;
else:
    $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou editar uma aula sem informar o módulo a que ela pertence!", E_USER_NOTICE);
    header('Location: dashboard.php?wc=teach/courses');
    exit;
endif;

if ($class_material && (!file_exists("../uploads/{$class_material}") || is_dir("../uploads/{$class_material}"))):
    $UpdateClass = ['class_material' => null];
    $Update->ExeUpdate(DB_EAD_CLASSES, $UpdateClass, "WHERE class_id = :id", "id={$ClassId}");
    $class_material = null;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-play"><?= $class_title; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses">Cursos</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses_gerent&id=<?= $course_id; ?>"><?= $course_title; ?></a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses_classes&id=<?= $course_id; ?>&module=<?= $module_id; ?>"><?= $module_title; ?></a>
            <span class="crumb">/</span>
            Editar aula
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Voltar ao Módulo!" href="dashboard.php?wc=teach/courses_classes&id=<?= $course_id; ?>&module=<?= $module_id; ?>" class="wc_view btn btn_blue icon-lab">Ver Módulo!</a>
    </div>

</header>
<div class="dashboard_content">
    <div class="box box100">
        <div class="j_content">
            <?php if ($class_video): ?>
                <div class="embed-container">
                    <?php if (is_numeric($class_video)): ?>
                        <iframe src="https://player.vimeo.com/video/<?= $class_video; ?>?color=<?= EAD_VIMEO_COLOR; ?>&title=0&byline=0&portrait=0" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                    <?php else: ?>
                        <iframe width="640" height="360" src="https://www.youtube.com/embed/<?= $class_video; ?>?showinfo=0&amp;rel=0" frameborder="0" allowfullscreen></iframe>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="panel">
            <form name="class_add" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="callback" value="Courses"/>
                <input type="hidden" name="callback_action" value="class_edit"/>
                <input type="hidden" name="class_id" value="<?= $class_id; ?>"/>
                <input type="hidden" name="course_id" value="<?= $course_id; ?>"/>
                <input type="hidden" name="class_order" value="<?= $class_order; ?>" class="wc_value"/>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Título:</span>
                        <input style="font-size: 1.2em;" type="text" name="class_title" value="<?= $class_title; ?>" placeholder="Título da aula:" required/>
                    </label>

                    <label class="label">
                        <span class="legend icon-vimeo">Id do Vídeo: (Vimeo ou YouTube)</span>
                        <input style="font-size: 1.2em;" type="text" name="class_video" value="<?= $class_video; ?>" placeholder="Id do vídeo no Vimeo:"/>
                    </label>
                </div>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Tempo:</span>
                        <input type="number" name="class_time" value="<?= $class_time; ?>" placeholder="Tempo em minutos:"/>
                    </label>

                    <label class="label">
                        <span class="legend">Material de apoio:</span>
                        <input type="file" name="class_material"/>
                    </label>
                </div>

                <div class="j_download">
                    <?php
                    if ($class_material):
                        echo "<div class='course_gerent_class_download' id='{$class_id}'>
                        <a target='blank' href='" . BASE . "/admin/_sis/teach/courses_downloads_f.php?f={$class_id}' class='btn btn_green icon-download' title='Baixar Material de Apoio!'>Baixar!</a>
                        <span rel='course_gerent_class_download' class='j_delete_action icon-cancel-circle btn btn_red' id='{$class_id}'>Deletar Material!</span>
                        <span rel='course_gerent_class_download' callback='Courses' callback_action='class_delete_file' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='{$class_id}'>Excluir Arquivo!</span>
                    </div>";
                    endif;
                    ?>
                </div>

                <label class="label">
                    <span class="legend">Descrição:</span>
                    <textarea class="work_mce_basic" style="font-size: 1.2em;" name="class_desc" rows="3" placeholder="Descrição da aula:"><?= $class_desc; ?></textarea>
                </label>

                <img class="form_load fl_right none" style="margin-left: 10px; margin-top: 1px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                <button class="btn btn_green icon-pencil2 fl_right">ATUALIZAR AULA!</button>
                <label style="margin-right: 10px; font-weight: 500;" class="label_check label_publish fl_right <?= ($class_support == 1 ? 'active' : ''); ?>"><input style="margin-top: -1px;" type="checkbox" <?= ($class_support == 1 ? 'checked' : ''); ?> value="1" name="class_support">HABILITAR SUPORTE!</label>
                <div class="clear"></div>
            </form>
        </div>
    </div>
</div>