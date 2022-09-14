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
        exit;
    endif;
else:
    $_SESSION['trigger_controll'] = "<b>OPPSS {$Admin['user_name']}</b>, você tentou gerenciar um curso que não existe ou que foi removido recentemente!";
    header('Location: dashboard.php?wc=teach/courses');
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-stats-dots">Relatótio de Andamento</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses">Cursos</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/courses_create&id=<?= $course_id; ?>"><?= $course_title; ?></a>
            <span class="crumb">/</span>
            Relatório de Andamento
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Gerenciar Curso!" href="dashboard.php?wc=teach/courses_gerent&id=<?= $CourseId; ?>" class="wc_view btn btn_blue icon-lab">Gerenciar Curso!</a>
    </div>
</header>
<div class="dashboard_content students_course_view">
    <div class="box box100">
        <div class="box_content">
            <?php
            $getPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            $Page = ($getPage ? $getPage : 1);
            $Pager = new Pager("dashboard.php?wc=teach/courses_students&id={$course_id}&page=", "<<", ">>", 5);
            $Pager->ExePager($Page, 20);
            $Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE course_id = :course ORDER BY enrollment_access DESC, enrollment_start DESC LIMIT :limit OFFSET :offset", "course={$course_id}&limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
            if (!$Read->getResult()):
                $Pager->ReturnPage();
                echo "<div class='trigger trigger_info icon-info al_center font_medium'>Ainda não existem alunos matrículados no curso {$course_title}!</div>";
            else:
                ?>
                <article class="students_course_view_class students_course_view_student_class">
                    <h1 class="row">
                        <b class="icon-user-plus">Aluno:</b>
                    </h1><p class="row">
                        <b class="icon-clock">Matrícula:</b>
                    </p><p class="row">
                        <b class="icon-clock2">Último Acesso:</b>
                    </p><p class="row">
                        <b class="icon-checkmark2">Aulas Vistas:</b>
                    </p><p class="row last">
                        <b class="icon-checkmark">Aulas Concluídas:</b>
                    </p>
                </article>
                <?php
                foreach ($Read->getResult() as $Enroll):
                    extract($Enroll);

                    $Read->FullRead("SELECT user_id, user_name, user_lastname, user_email FROM " . DB_USERS . " WHERE user_id = :user", "user={$user_id}");
                    $Studend = $Read->getResult()[0];

                    //PROGRESS VIEW
                    $Read->FullRead("SELECT COUNT(class_id) AS ClassCount, SUM(class_time) AS ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :cs)", "cs={$course_id}");
                    $ClassCount = $Read->getResult()[0]['ClassCount'];

                    $Read->FullRead("SELECT COUNT(student_class_id) as ClassStudentCount FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND course_id = :course", "user={$user_id}&course={$course_id}");
                    $ClassStudenCount = $Read->getResult()[0]['ClassStudentCount'];

                    $Read->FullRead("SELECT COUNT(student_class_id) as ClassStudentCount FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND course_id = :course AND student_class_check IS NOT NULL", "user={$user_id}&course={$course_id}");
                    $ClassStudenCompleteCount = $Read->getResult()[0]['ClassStudentCount'];

                    $CourseViewPercent = ($ClassStudenCount && $ClassCount ? round(($ClassStudenCount * 100) / $ClassCount) : "0");
                    $CourseCompletedPercent = ($ClassStudenCompleteCount && $ClassCount ? round(($ClassStudenCompleteCount * 100) / $ClassCount) : "0");
                    ?>
                    <article class="students_course_view_class students_course_view_student_class">
                        <h1 class="row">
                            <span><a href="dashboard.php?wc=teach/students_gerent&id=<?= $user_id; ?>" title="Gerenciar Alunos!"><?= "{$Studend['user_name']} {$Studend['user_lastname']}"; ?></a></span>
                        </h1><p class="row">
                            <span><?= date("d/m/Y H\hi", strtotime($enrollment_start)); ?></span>
                        </p><p class="row">
                            <span><?= date("d/m/Y H\hi", strtotime(($enrollment_access ? $enrollment_access : $enrollment_start))); ?></span>
                        </p><p class="row">
                            <span class="upload_bar al_left"><span class="upload_progress bg_blue" style="width: <?= $CourseViewPercent; ?>%"><?= $CourseViewPercent; ?>%</span></span>
                        </p><p class="row last">
                            <span class="upload_bar al_left"><span class="upload_progress"style="width: <?= $CourseCompletedPercent; ?>%"><?= $CourseCompletedPercent; ?>%</span></span>
                        </p>
                    </article>
                    <?php
                endforeach;
            endif;
            ?>
        </div>
        <?php
        $Pager->ExePaginator(DB_EAD_ENROLLMENTS, "WHERE course_id = :course", "course={$course_id}");
        echo $Pager->getPaginator();
        ?>
    </div>
</div>