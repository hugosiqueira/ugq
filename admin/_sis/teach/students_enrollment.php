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
$Read->FullRead("SELECT COUNT(class_id) AS ClassCount, SUM(class_time) AS ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :cs)", "cs={$course_id}");
$ClassCount = $Read->getResult()[0]['ClassCount'];

$Read->FullRead("SELECT COUNT(student_class_id) as ClassStudentCount FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND course_id = :course", "user={$user_id}&course={$course_id}");
$ClassStudenCount = $Read->getResult()[0]['ClassStudentCount'];

$CourseCompletedPercent = ($ClassStudenCount && $ClassCount ? round(($ClassStudenCount * 100) / $ClassCount) : "0");
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-cogs">Gerenciar Matrícula:</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/students">Alunos</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/students_gerent&id=<?= $user_id; ?>"><?= $user_name; ?></a>
            <span class="crumb">/</span>
            Matrícula #<?= $EnrollmentId; ?>
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
        <div class="box_content">
            <form name="user_manager" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="callback" value="Courses"/>
                <input type="hidden" name="callback_action" value="student_enrollment"/>
                <input type="hidden" name="enrollment_id" value="<?= $enrollment_id; ?>"/>

                <label class="label">
                    <span class="legend">Bônus do curso:</span>
                    <select name="enrollment_bonus">
                        <option value="">Não é um bônus</option>
                        <?php
                        $Read->FullRead(
                                "SELECT "
                                . "e.enrollment_id,"
                                . "c.course_title "
                                . "FROM " . DB_EAD_ENROLLMENTS . " e "
                                . "INNER JOIN " . DB_EAD_COURSES . " c ON e.course_id = c.course_id "
                                . "WHERE e.user_id = :user "
                                . "AND e.enrollment_id != :enrol", "user={$user_id}&enrol={$enrollment_id}"
                        );
                        if ($Read->getResult()):
                            foreach ($Read->getResult() AS $COURSES):
                                echo "<option " . ($COURSES['enrollment_id'] == $enrollment_bonus ? "selected='selected'" : '') . " value='{$COURSES['enrollment_id']}'>{$COURSES['course_title']}</option>";
                            endforeach;
                        endif;
                        ?>
                    </select>
                </label>

                <div class="label_50">
                    <label class="label">
                        <span class="legend">Matrícula:</span>
                        <input class="jwc_datepicker" data-timepicker="true" value="<?= date("d/m/Y H:i:s", strtotime($enrollment_start)); ?>" type="text" name="enrollment_start" placeholder="Data da Matrícula:" required />
                    </label>

                    <label class="label">
                        <span class="legend">Vencimento:</span>
                        <input class="jwc_datepicker" data-timepicker="true" value="<?= (!empty($enrollment_end) ? date("d/m/Y H:i:s", strtotime($enrollment_end)) : null); ?>" type="text" name="enrollment_end" placeholder="Data de Vencimento:"/>
                    </label>
                </div>

                <img class="form_load none fl_right" style="margin-left: 10px; margin-top: 2px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                <button name="public" value="1" class="btn btn_green fl_right icon-share" style="margin-left: 5px;">Atualizar Matrículas!</button>
                <div class="clear"></div>
            </form>
        </div>
    </div>
</div>