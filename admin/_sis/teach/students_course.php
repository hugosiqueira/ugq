<?php
$AdminLevel = LEVEL_WC_EAD_STUDENTS;
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

$StudentId = filter_input(INPUT_GET, 'student', FILTER_VALIDATE_INT);
$EnrollmentId = filter_input(INPUT_GET, 'enrollment', FILTER_VALIDATE_INT);
if ($EnrollmentId):
    $Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE enrollment_id = :id", "id={$EnrollmentId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);

        $Read->LinkResult(DB_USERS, "user_id", "{$user_id}", "user_name, user_lastname");
        extract($Read->getResult()[0]);

        $Read->LinkResult(DB_EAD_COURSES, "course_id", "{$course_id}", "course_title");
        extract($Read->getResult()[0]);

    else:
        $_SESSION['trigger_controll'] = "<b>OPPSS {$Admin['user_name']}</b>, você tentou visualizar o curso em uma matrícula que não existe ou que foi removida recentemente!";
        if ($StudentId):
            header('Location: dashboard.php?wc=teach/students_gerent&id=' . $StudentId);
        else:
            header('Location: dashboard.php?wc=teach/students');
        endif;
    endif;
else:
    $_SESSION['trigger_controll'] = "<b>OPPSS {$Admin['user_name']}</b>, você tentou visualizar o curso em uma matrícula que não existe ou que foi removida recentemente!";
    header('Location: dashboard.php?wc=teach/students');
endif;

//PROGRESS
$Read->FullRead("SELECT COUNT(class_id) AS ClassCount FROM " . DB_EAD_CLASSES . " WHERE module_id IN (SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :cs)", "cs={$course_id}");
$ClassCount = $Read->getResult()[0]['ClassCount'];

$Read->FullRead("SELECT COUNT(student_class_id) as ClassStudentCount FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND course_id = :course", "user={$user_id}&course={$course_id}");
$ClassStudenCount = $Read->getResult()[0]['ClassStudentCount'];

$CourseCompletedPercent = ($ClassStudenCount && $ClassCount ? round(($ClassStudenCount * 100) / $ClassCount) : "0");
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-shuffle">Andamento do curso</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/students">Alunos</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/students_gerent&id=<?= $user_id; ?>"><?= $user_name; ?></a>
            <span class="crumb">/</span>
            Andamento do curso!
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Gerenciar Aluno!" href="dashboard.php?wc=teach/students_gerent&id=<?= $user_id; ?>#courses" class="wc_view btn btn_blue icon-backward2">Voltar</a>
    </div>
</header>
<div class="dashboard_content students_course_view">
    <div class="box box100">

        <div class="panel_header default">
            <h2 class="icon-lab al_center" style="font-size: 1em; font-weight: 300;"><?= $course_title; ?> de <?= "{$user_name} {$user_lastname}"; ?></h2>
        </div>

        <div class="upload_bar"><span class="upload_progress" style="width: <?= $CourseCompletedPercent; ?>%"><?= $CourseCompletedPercent; ?>%</span></div>

        <div class="box_content">
            <?php
            $Read->ExeRead(DB_EAD_MODULES, "WHERE course_id = :id ORDER BY module_order ASC", "id={$course_id}");
            if (!$Read->getResult()):
                echo "<div class='trigger trigger_info trigger_none icon-info al_center'>Ainda não existem módulos cadastrados no curso {$course_title}!</div>";
                echo "<div class='clear'></div>";
            else:
                foreach ($Read->getResult() as $MOD):
                    extract($MOD);

                    $Read->FullRead("(SELECT "
                            . "class_id "
                            . "FROM ws_ead_classes class "
                            . "WHERE class.module_id IN (SELECT modu.module_id FROM ws_ead_modules modu WHERE modu.course_id = {$course_id} AND modu.module_order < {$module_order} AND modu.module_required = 1) "
                            . "AND class.class_id NOT IN (SELECT stclass.class_id FROM ws_ead_student_classes stclass WHERE stclass.student_class_check IS NOT NULL AND stclass.user_id = {$user_id} AND stclass.course_id = {$course_id} AND stclass.class_id IN (SELECT classmod.class_id FROM ws_ead_classes classmod WHERE classmod.module_id IN (SELECT modreq.module_id FROM ws_ead_modules modreq WHERE modreq.course_id = {$course_id} AND modreq.module_order < {$module_order} AND modreq.module_required = 1))))");

                    $ClassesPendent = $Read->getRowCount();

                    if (empty($module_release_date)):
                        $ReleaseUnlock = strtotime($enrollment_start . "+{$module_release}days");
                        $ModuleUnlocked = (($ReleaseUnlock <= time() && $ClassesPendent == 0) ? true : false);
                        $ModuleRelease = ($ReleaseUnlock <= time() ? '<span class="bar_green bar_icon radius icon-unlocked">' . date('d/m/Y \a\s H\hi', $ReleaseUnlock) . '</span>' : '<span class="bar_yellow bar_icon radius icon-lock">' . date('d/m/Y \a\s H\hi', $ReleaseUnlock) . '</span>');
                    else:
                        $ReleaseUnlock = $module_release_date;
                        $ModuleUnlocked = ((strtotime($ReleaseUnlock) <= time() && $ClassesPendent == 0) ? true : false);
                        $ModuleRelease = (strtotime($ReleaseUnlock) <= time() ? '<span class="bar_green bar_icon radius icon-unlocked">' . date('d/m/Y \a\s H\hi', strtotime($ReleaseUnlock)) . '</span>' : '<span class="bar_yellow bar_icon radius icon-lock">' . date('d/m/Y \a\s H\hi', strtotime($ReleaseUnlock)) . '</span>');
                    endif;

                    if ($module_required == 1):
                        $barRequired = "<span class='bar_red radius icon-flag'>Módulo obrigatório</span>";
                    else:
                        $barRequired = "";
                    endif;
                    ?>
                    <section class="students_course_view_module">
                        <h1 class="icon-tree"><?= $module_title; ?>:<?= $ModuleRelease; ?> <?= $barRequired; ?></h1>
                        <?php
                        $Read->ExeRead(DB_EAD_CLASSES, "WHERE module_id = :mod ORDER BY class_order ASC", "mod={$module_id}");
                        if (!$Read->getResult()):
                            echo "<div class='trigger trigger_info trigger_none icon-info m_botton'>Ainda não existem aulas cadastradas neste módulo!</div>";
                            echo "<div class='clear'></div>";
                        else:
                            foreach ($Read->getResult() as $CLASS):
                                extract($CLASS);

                                $Read->ExeRead(DB_EAD_STUDENT_CLASSES, "WHERE class_id = :class AND user_id = :user", "class={$class_id}&user={$user_id}");
                                if ($Read->getResult()):
                                    extract($Read->getResult()[0]);
                                    $ClassViews = str_pad($student_class_views, 4, 0, 0);

                                    //MAKE ACESS DAYS
                                    $DayThis = new DateTime(date("Y-m-d H:i:s"));
                                    $DayPlay = new DateTime($student_class_play);
                                    $DaysDif = $DayThis->diff($DayPlay)->days;

                                    $ClassPlay = (!$student_class_play ? 'NUNCA' : ($DaysDif < 1 ? "Hoje" : ($DaysDif == 1 ? "Ontem" : str_pad($DaysDif, 2, 0, 0) . " dias")));
                                    $ClassCheck = ($student_class_check ? date("d/m/y", strtotime($student_class_check)) : null);
                                else:
                                    $ClassViews = "0000";
                                    $ClassPlay = "Nunca";
                                    $ClassCheck = null;
                                endif;

                                //SUPPORT
                                $Read->FullRead("SELECT support_status FROM " . DB_EAD_SUPPORT . " WHERE user_id = :user AND class_id = :class");
                                $TaskSupport = (!$Read->getResult() ? 'Não Abriu' : ($Read->getResult()[0]['support_status'] == 1 ? 'Em Aberto' : ($Read->getResult()[0]['support_status'] == 2 ? '<b>Respondida</b>' : 'Concluída')));

                                //DOWNLOADS
                                $Read->FullRead("SELECT COUNT(download_id) AS class_downloads FROM " . DB_EAD_STUDENT_DOWNLOADS . " WHERE user_id = :user AND class_id = :class", "user={$user_id}&class={$class_id}");
                                $class_downloads = $Read->getResult()[0]['class_downloads'];
                                ?>
                                <article class="students_course_view_class">
                                    <h1 class="row">
                                        <span class="icon-play2"><a target="_blank" href="<?= BASE; ?>/campus/tarefa/<?= $class_name; ?>" title="Ver tarefa!"><?= $class_title; ?></a></span>
                                    </h1><p class="row">
                                        <span class="icon-download"><?= str_pad($class_downloads, 4, 0, 0); ?></span>
                                    </p><p class="row">
                                        <span class="icon-stats-dots"><?= $ClassViews; ?></span>
                                    </p><p class="row">
                                        <span class="icon-clock"><?= $ClassPlay; ?></span>
                                    </p><p class="row">
                                        <span class="icon-bubbles3"><?= $TaskSupport; ?></span>
                                    </p><p class="row">
                                        <span class="<?= $ClassCheck ? "icon-checkmark" : 'icon-checkmark2'; ?>"><?= $ClassCheck ? $ClassCheck : "00/00/00"; ?></span>
                                    </p>
                                </article>
                                <?php
                            endforeach;
                        endif;
                        ?>
                    </section>
                    <?php
                endforeach;
            endif;
            ?>
        </div>
    </div>
</div>