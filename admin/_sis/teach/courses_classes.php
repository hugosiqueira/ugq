<?php
$AdminLevel = LEVEL_WC_EAD_COURSES;
if (!APP_EAD || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

$Read = new Read;
$Create = new Create;

$CourseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$CourseId):
    $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou adicionar aulas a um módulo sem informar o curso a que ele pertence!", E_USER_NOTICE);
    header('Location: dashboard.php?wc=teach/courses');
    exit;
else:
    $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = :id", "id={$CourseId}");
    if ($Read->getResult()):
        extract($Read->getResult()[0]);
    else:
        $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou adicionar aulas a um módulo sem informar o curso a que ele pertence!", E_USER_NOTICE);
        header('Location: dashboard.php?wc=teach/courses');
        exit;
    endif;
endif;


$ModId = filter_input(INPUT_GET, 'module', FILTER_VALIDATE_INT);
if ($ModId):
    $Read->ExeRead(DB_EAD_MODULES, "WHERE module_id = :id", "id={$ModId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);
    else:
        $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou adicionar aulas a um módulo de curso que não existe ou que foi removida recentemente!", E_USER_NOTICE);
        header('Location: dashboard.php?wc=teach/courses_gerente&id=' . $CourseId);
        exit;
    endif;
else:
    $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou adicionar aulas a um módulo de curso que não existe ou que foi removida recentemente!", E_USER_NOTICE);
    header('Location: dashboard.php?wc=teach/courses_gerente&id=' . $CourseId);
    exit;
endif;

$Read->FullRead("SELECT class_order FROM " . DB_EAD_CLASSES . " WHERE module_id = :mod ORDER BY class_order DESC LIMIT 1", "mod={$ModId}");
if ($Read->getResult()):
    $ClassOrder = $Read->getResult()[0]['class_order'] + 1;
else:
    $ClassOrder = 1;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-play2"><?= $module_title; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses">Cursos</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses_gerent&id=<?= $CourseId; ?>"><?= $course_title; ?></a>
            <span class="crumb">/</span>
            Adicionar Aulas
        </p>
    </div>

    <div class="dashboard_header_search">
        <a class="btn btn_blue icon-pencil2 icon-notext" href="dashboard.php?wc=teach/courses_modules&id=<?= $course_id; ?>&module=<?= $module_id; ?>" title="Cadastrar Aulas"></a>
        <span class="btn btn_green icon-notext icon-spinner9 wc_drag_active" title="Organizar Aulas"></span>
        <a title="Voltar ao Curso!" href="dashboard.php?wc=teach/courses_gerent&id=<?= $CourseId; ?>" class="wc_view btn btn_blue icon-lab">Ver Curso!</a>
    </div>

</header>
<div class="dashboard_content">
    <article class="box box100" style="margin: 0; padding: 0;">
        <section class="course_gerent_classes j_content">
            <?php
            $Read->ExeRead(DB_EAD_CLASSES, "WHERE module_id = :id ORDER BY class_order ASC", "id={$ModId}");
            if (!$Read->getResult()):
                echo '<div class="trigger trigger_info trigger_none al_center icon-info">Ainda não existem aulas cadastradas em ' . $module_title . '!</div>';
                echo '<div class="clear"></div>';
            else:
                foreach ($Read->getResult() as $CLASS):
                    extract($CLASS);

                    $Read->FullRead("SELECT SUM(student_class_views) AS ClassTotalViews FROM " . DB_EAD_STUDENT_CLASSES . " WHERE class_id = :id", "id={$class_id}");
                    $ClassTotalViews = $Read->getResult()[0]['ClassTotalViews'];
                    ?><article class="course_gerent_class wc_draganddrop" callback='Courses' callback_action='class_order' id="<?= $class_id; ?>">
                        <h1 class="row_title">
                            <?= $class_title; ?>
                        </h1><p class="row icon-clock">
                            <?= str_pad($class_time, 2, 0, 0); ?> min
                        </p><p class="row icon-bubbles3">
                            <?= $class_support ? 'Sim!' : 'Não!'; ?>
                        </p><p class="row icon-file-zip">
                            <?= $class_material && file_exists("../uploads/{$class_material}") && !is_dir("../uploads/{$class_material}") ? "<a target='blank' href='" . BASE . "/admin/_sis/teach/courses_downloads_f.php?f={$class_id}' title='Baixar Material de Apoio!'>Baixar</a>" : 'Não!' ?>
                        </p><p class="row icon-eye">
                            <?= str_pad($ClassTotalViews, 4, 0, 0); ?>
                        </p><p class="actions">
                            <a href="dashboard.php?wc=teach/courses_classedit&id=<?= $CourseId; ?>&module=<?= $module_id; ?>&class=<?= $class_id; ?>" title="Editar Aula" class="btn btn_blue icon-pencil2 icon-notext"></a>
                            <span rel='course_gerent_class' class='j_delete_action icon-cancel-circle icon-notext btn btn_red' id='<?= $class_id; ?>'></span>
                            <span rel='course_gerent_class' callback='Courses' callback_action='class_delete' class='j_delete_action_confirm icon-warning icon-notext btn btn_yellow' style='display: none' id='<?= $class_id; ?>'></span>
                        </p>
                    </article><?php
                endforeach;
            endif;
            ?>
        </section>
    </article>
    <div class="box box100" style="margin: 0; padding: 0;">
        <div class="box_content">
            <form name="class_add" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="callback" value="Courses"/>
                <input type="hidden" name="callback_action" value="class_add"/>
                <input type="hidden" name="module_id" value="<?= $ModId; ?>"/>
                <input type="hidden" name="course_id" value="<?= $CourseId; ?>"/>
                <input type="hidden" name="class_order" value="<?= $ClassOrder; ?>"  class="wc_value"/>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Título:</span>
                        <input style="font-size: 1.2em;" type="text" name="class_title" value="" placeholder="Título da aula:" required/>
                    </label>

                    <label class="label">
                        <span class="legend icon-vimeo">Id do Vídeo:</span>
                        <input style="font-size: 1.2em;" type="text" name="class_video" value="" placeholder="Id do vídeo no Vimeo:"/>
                    </label>
                </div>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Tempo:</span>
                        <input type="number" name="class_time" value="" placeholder="Tempo em minutos:"/>
                    </label>

                    <label class="label">
                        <span class="legend">Material de apoio:</span>
                        <input type="file" name="class_material"/>
                    </label>
                </div>

                <label class="label">
                    <span class="legend">Descrição:</span>
                    <textarea class="work_mce_basic" style="font-size: 1.2em;" name="class_desc" rows="3" placeholder="Descrição da aula:"></textarea>
                </label>

                <img class="form_load fl_right none" style="margin-left: 10px; margin-top: 1px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                <button class="btn btn_green icon-plus fl_right">CADASTRAR AULA!</button>
                <?php
                if (EAD_TASK_SUPPORT_DEFAULT):
                    echo '<input type="hidden" value="1" name="class_support">';
                else:
                    echo '<label style="margin-right: 10px; font-weight: 500;" class="label_check label_publish fl_right"><input style="margin-top: -1px;" type="checkbox" value="1" name="class_support">HABILITAR SUPORTE!</label>';
                endif;
                ?>
                <div class="clear"></div>
            </form>
        </div>
    </div>
</div>