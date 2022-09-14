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


$CourseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$CourseId):
    $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou editar um módulo sem informar o curso a que ele pertence!", E_USER_NOTICE);
    header('Location: dashboard.php?wc=teach/courses');
else:
    $Read->FullRead("SELECT course_id, course_title FROM " . DB_EAD_COURSES . " WHERE course_id = :id", "id={$CourseId}");
    if ($Read->getResult()):
        extract($Read->getResult()[0]);
    else:
        $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou editar um módulo sem informar o curso a que ele pertence!", E_USER_NOTICE);
        header('Location: dashboard.php?wc=teach/courses');
    endif;
endif;


$ModId = filter_input(INPUT_GET, 'module', FILTER_VALIDATE_INT);
if ($ModId):
    $Read->ExeRead(DB_EAD_MODULES, "WHERE module_id = :id", "id={$ModId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);

        $ModuleRelease = 0;
        $Read->FullRead("SELECT module_release FROM " . DB_EAD_MODULES . " WHERE module_id != :id AND course_id = :cid AND module_order < :order ORDER BY module_release DESC", "id={$module_id}&cid={$course_id}&order={$module_order}");
        if ($Read->getResult()):
            $ModuleRelease = $Read->getResult()[0]['module_release'];
        endif;
    else:
        $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou editar um módulo de curso que não existe ou que foi removida recentemente!", E_USER_NOTICE);
        header('Location: dashboard.php?wc=teach/courses_gerent&id=' . $CourseId);
    endif;
else:
    $ModuleOrder = 1;
    $Read->FullRead("SELECT module_order FROM " . DB_EAD_MODULES . " WHERE course_id = :id ORDER BY module_order DESC", "id={$CourseId}");
    if ($Read->getResult()):
        $ModuleOrder = $Read->getResult()[0]['module_order'] + 1;
    endif;

    $Date = date('Y-m-d H:i:s');
    $ModCreate = ['course_id' => $CourseId, 'module_created' => $Date, "module_order" => $ModuleOrder];
    $Create->ExeCreate(DB_EAD_MODULES, $ModCreate);
    header('Location: dashboard.php?wc=teach/courses_modules&id=' . $CourseId . '&module=' . $Create->getResult());
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-tree"><?= $module_title ? $module_title : 'Novo Módulo'; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses">Cursos</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses_gerent&id=<?= $CourseId; ?>"><?= $course_title; ?></a>
            <span class="crumb">/</span>
            Gerenciar Módulo
        </p>
    </div>

    <div class="dashboard_header_search" id="<?= $ModId; ?>">
        <a title="Voltar ao Curso!" href="dashboard.php?wc=teach/courses_gerent&id=<?= $CourseId; ?>" class="wc_view btn btn_blue icon-lab icon-notext"></a>
        <a class="btn btn_green icon-play2 icon-notext" href="dashboard.php?wc=teach/courses_classes&id=<?= $course_id; ?>&module=<?= $module_id; ?>" title="Cadastrar Aulas"></a>
        <a class="btn btn_green icon-plus" href="dashboard.php?wc=teach/courses_modules&id=<?= $CourseId; ?>" title="Novo Módulo">Módulo</a>
        <span rel='dashboard_header_search' class='j_delete_action icon-warning btn btn_red' id='<?= $ModId; ?>'>Deletar!</span>
        <span rel='dashboard_header_search' callback='Courses' callback_action='module_delete' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='<?= $ModId; ?>'>Excluir!</span>
    </div>

</header>

<div class="dashboard_content">

    <div class="box box100">

        <div class="panel_header default">
            <h2 class="icon-tree">Dados sobre o módulo</h2>
        </div>

        <div class="panel">
            <form class="auto_save" name="module_add" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="callback" value="Courses"/>
                <input type="hidden" name="callback_action" value="module_manage"/>
                <input type="hidden" name="module_id" value="<?= $ModId; ?>"/>
                <label class="label">
                    <span class="legend">Título:</span>
                    <input style="font-size: 1.5em;" type="text" name="module_title" value="<?= $module_title; ?>" placeholder="Título do Módulo:" required/>
                </label>

                <label class="label">
                    <span class="legend">Descrição:</span>
                    <textarea style="font-size: 1.2em;" name="module_desc" rows="3" placeholder="Sobre o Módulo:"><?= $module_desc; ?></textarea>
                </label>

                <div class="label_33">
                    <label class="label">
                        <span class="legend">Liberação a partir de:</span>
                        <input type="text" name="module_release_date" data-timepicker="true" value="<?= (!empty($module_release_date) ? date('d/m/Y H:i', strtotime($module_release_date)) : null); ?>" placeholder="Data para começar:" class="jwc_datepicker wc_value"/>
                    </label>

                    <label class="label">
                        <span class="legend">Ou em XX dias: (se data menor)</span>
                        <input type="number" name="module_release" value="<?= (!empty($module_release) ? $module_release : $ModuleRelease); ?>" placeholder="Dias para começar:"/>
                    </label>

                    <label class="label">
                        <span class="legend">Módulo Obrigatório:</span>
                        <select name="module_required">
                            <option value="0" <?= (($module_required == 0) ? "selected='selected'" : ""); ?>>Não</option>
                            <option value="1" <?= (($module_required == 1) ? "selected='selected'" : ""); ?>>Sim</option>
                        </select>
                    </label>
                </div>

                <div class="m_top">&nbsp;</div>
                <img class="form_load fl_right none" style="margin-left: 10px; margin-top: 2px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                <button class="btn btn_green icon-price-tags fl_right">Atualizar Módulo!</button>
                <div class="clear"></div>
            </form>
        </div>
    </div>
</div>