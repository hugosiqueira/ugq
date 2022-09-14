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
if ($CourseId):
    $Read->ExeRead(DB_EAD_COURSES, "WHERE course_id = :id", "id={$CourseId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);

        $Author = $Read->LinkResult(DB_USERS, "user_id", $course_author, "user_name, user_lastname, user_thumb");

        $Thumb = (file_exists("../uploads/{$Author['user_thumb']}") && !is_dir("../uploads/{$Author['user_thumb']}") ? "uploads/{$Author['user_thumb']}" : 'admin/_img/no_avatar.jpg');
        $Cover = (file_exists("../uploads/{$course_cover}") && !is_dir("../uploads/{$course_cover}") ? "uploads/{$course_cover}" : 'admin/_img/no_image.jpg');
        $Status = ($course_status != 1 ? 'inactive' : '');
    else:
        $_SESSION['trigger_controll'] = "<b>OPPSS {$Admin['user_name']}</b>, você tentou gerenciar um curso que não existe ou que foi removido recentemente!";
        header('Location: dashboard.php?wc=teach/courses');
    endif;
else:
    $_SESSION['trigger_controll'] = "<b>OPPSS {$Admin['user_name']}</b>, você tentou gerenciar um curso que não existe ou que foi removido recentemente!";
    header('Location: dashboard.php?wc=teach/courses');
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-lab">Gerenciar <?= $course_title ? $course_title : 'Curso'; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses">Cursos</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses_create&id=<?= $course_id; ?>">Editar <?= $course_title; ?></a>
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Relatório de Andamento!" href="dashboard.php?wc=teach/courses_students&id=<?= $CourseId; ?>" class="wc_view btn btn_green icon-stats-dots icon-notext"></a>
        <a title="Gerenciar Curso!" href="dashboard.php?wc=teach/courses_create&id=<?= $CourseId; ?>" class="wc_view btn btn_blue icon-lab">Editar Curso!</a>
    </div>
</header>

<div class="dashboard_content">
    <form class="auto_save" name="course_create" action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="callback" value="Courses"/>
        <input type="hidden" name="callback_action" value="manager"/>
        <input type="hidden" name="course_id" value="<?= $CourseId; ?>"/>

        <div class="box box70">
            <div class="course_gerent_modules">
                <div class="panel_header success">
                    <span>
                        <a href="dashboard.php?wc=teach/courses_downloads&id=<?= $course_id; ?>" class="fl_right btn btn_green icon-folder" title="Gerenciar Material">Downloads</a>
                        <a href="dashboard.php?wc=teach/courses_modules&id=<?= $course_id; ?>" class="fl_right btn btn_green icon-plus icon-notext" title="Adicionar Módulo"></a>
                        <span class="fl_right btn btn_green icon-notext icon-spinner9 wc_drag_active" title="Organizar Módulos"></span>
                    </span>
                    <h2 class="icon-file-text2">Módulos:</h2>
                </div>
                <div class="panel">
                    <?php
                    $Read->ExeRead(DB_EAD_MODULES, "WHERE course_id = :id ORDER BY module_order ASC", "id={$CourseId}");
                    $CourseClasses = 0;
                    $CourseHours = 0;

                    if (!$Read->getResult()):
                        echo "<div class='trigger al_center trigger_info trigger_none font_medium'>Ainda não existem módulo cadastrados. Clique em <span class='icon-plus icon-notext'></span> para cadastrar o primeiro!</div>";
                    else:
                        foreach ($Read->getResult() as $Module):
                            extract($Module);

                            $Read->LinkResult(DB_EAD_CLASSES, "module_id", $module_id, "class_id");
                            $ModClasses = $Read->getRowCount();
                            $CourseClasses += $ModClasses;

                            $Read->FullRead("SELECT SUM(class_time) as ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id = :mod", "mod={$module_id}");
                            $ModMinutes = $Read->getResult()[0]['ClassTime'];
                            $CourseHours += $ModMinutes;
                            ?>
                            <article class="course_gerent_module wc_draganddrop" callback='Courses' callback_action='modules_order' id="<?= $module_id; ?>">
                                <h1 class="row_title <?= (!empty($module_required) ? 'icon-pushpin' : 'icon-tree'); ?>">
                                    <?= $module_title; ?>
                                </h1><p class="row">
                                    <span><?= str_pad($module_order, 2, 0, 0); ?>º - <?= !empty($module_release_date) ? "Dia " . date("d/m/y", strtotime($module_release_date)) : str_pad($module_release, 2, 0, 0) . " dias"; ?></span><span><?= str_pad($ModClasses, 2, 0, 0); ?> aulas</span><span><?= floor($ModMinutes / 60) . ":" . str_pad($ModMinutes % 60, 2, 0, 0); ?> hrs.</span>
                                </p><p class="row">
                                    <a href="dashboard.php?wc=teach/courses_modules&id=<?= $CourseId; ?>&module=<?= $module_id; ?>" class="btn btn_blue icon-pencil2 icon-notext"></a>
                                    <a href="dashboard.php?wc=teach/courses_classes&id=<?= $CourseId; ?>&module=<?= $module_id; ?>" class="btn btn_green icon-play2 icon-notext"></a>
                                </p>
                            </article>
                            <?php
                        endforeach;
                    endif;
                    ?>
                    <div class="clear"></div>
                </div>
                <div class="panel_footer_external">
                    <span class="icon-trophy"><?= ceil($CourseHours * 10 / 60); ?>h00</span>
                    <span class="icon-clock2"><?= floor($CourseHours / 60) . "h" . str_pad($CourseHours % 60, 2, 0, 0); ?></span>
                    <span class="icon-play2"><?= str_pad($CourseClasses, 2, 0, 0); ?></span>
                </div>
            </div>
        </div>

        <div class="box box30">
            <img src='../tim.php?src=<?= $Cover; ?>&w=<?= IMAGE_W / 3; ?>&h=<?= IMAGE_H / 3; ?>' title='<?= $course_title; ?>' alt='<?= $course_title; ?>'/>
            <div class='course_gerent_thumb panel'>
                <h1>Curso <?= $course_title; ?></h1>
                <p><?= $course_headline; ?></p>
            </div>

            <div class="panel_header default">
                <h2 class="icon-user-plus">Alunos Neste Curso:</h2>
            </div>
            <div class='course_gerent_students box_content'>
                <?php
                $StudentsDisplay = 0;
                $Read->FullRead("SELECT user_id, user_name, user_lastname, user_thumb FROM " . DB_USERS . " WHERE user_id IN(SELECT user_id FROM " . DB_EAD_ENROLLMENTS . " WHERE course_id = :cs) LIMIT 20", "cs={$course_id}");
                if (!$Read->getResult()):
                    echo "<div class='al_center trigger trigger_alert trigger_none icon-info'>Ainda não existem alunos matriculados neste curso!</div><div class='clear'></div>";
                else:
                    foreach ($Read->getResult() as $Students):
                        extract($Students);
                        $StudentsDisplay ++;

                        $user_name = ($user_name ? $user_name : 'Novo');
                        $user_lastname = ($user_lastname ? $user_lastname : 'Aluno');
                        $UserThumb = "../uploads/{$user_thumb}";
                        $user_thumb = (file_exists($UserThumb) && !is_dir($UserThumb) ? "uploads/{$user_thumb}" : 'admin/_img/no_avatar.jpg');
                        echo "<a href='dashboard.php?wc=teach/students_gerent&id={$user_id}' title='{$user_name} {$user_lastname}'><img alt='{$user_name} {$user_lastname}' title='{$user_name} {$user_lastname}' src='../tim.php?src={$user_thumb}&w=" . AVATAR_W / 5 . "&h=" . AVATAR_H / 5 . "'/></a>";
                    endforeach;

                    $Read->FullRead("SELECT COUNT(user_id) AS TotalStudents FROM " . DB_USERS . " WHERE user_id IN(SELECT user_id FROM " . DB_EAD_ENROLLMENTS . " WHERE course_id = :cs)", "cs={$course_id}");
                    $TotalStudents = $Read->getResult()[0]['TotalStudents'] - $StudentsDisplay;

                    echo "<div class='al_center' style='margin-top: 15px'><a class='btn ds_block box100 btn_blue icon-eye-plus' href='dashboard.php?wc=teach/students&course={$course_id}' title='Ver todos os alunos em {$course_title}'>E MAIS " . str_pad($TotalStudents, 4, 0, 0) . " ALUNOS!</a></div>";
                endif;
                ?>
            </div>
        </div>
    </form>
</div>