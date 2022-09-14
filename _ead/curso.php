<?php
//CLASS
if (empty($Read)):
    $Read = new Read;
endif;

//GET STUDENT
if (!empty($_SESSION['userLogin'])):
    $Read->ExeRead(DB_USERS, "WHERE user_id = :user", "user={$_SESSION['userLogin']['user_id']}");
    if (!$Read->getResult()):
        unset($_SESSION['userLogin']);
        header("Location: " . BASE . "/campus/login/restrito");
        exit;
    else:
        $Student = $Read->getResult()[0];
        extract($Student);
        $UpdateStudentAcess = ['user_lastaccess' => date('Y-m-d H:i:s'), 'user_login' => time()];

        if (empty($Update)):
            $Update = new Update;
        endif;
        $Update->ExeUpdate(DB_USERS, $UpdateStudentAcess, "WHERE user_id = :user", "user={$user_id}");
    endif;
else:
    header("Location: " . BASE . "/campus/login/restrito");
    exit;
endif;

//VALIDATE CHARGEBACK
$Read->ExeRead(DB_EAD_ORDERS, "WHERE user_id = :user AND order_status = :status", "user={$user_id}&status=chargeback");
if ($Read->getResult()):
    header("Location: " . BASE . "/campus/");
    exit;
endif;

//GET COURSE
$CourseName = (!empty($URL[2]) ? strip_tags($URL[2]) : null);
if (!$CourseName):
    $_SESSION['wc_ead_alert'] = ["yellow", "Oppps {$user_name}, erro ao acessar:", "Não foi possível identificar o curso acessado :/"];
    header("Location: " . BASE . "/campus");
    exit;
else:
    $Read->ExeRead(DB_EAD_COURSES, "WHERE course_name = :name", "name={$CourseName}");
    if (!$Read->getResult()):
        $_SESSION['wc_ead_alert'] = ["yellow", "Oppps {$user_name}, erro ao acessar:", "Não foi possível identificar o curso acessado :/"];
        header("Location: " . BASE . "/campus");
        exit;
    else:
        extract($Read->getResult()[0]);

        //STUDENT CONTROL NAVIGATION
        $_SESSION['wc_student_course'] = $course_id;

        $TaskRedirect = (!empty($URL[3]) ? $URL[3] : null);
        if ($TaskRedirect):
            header("Location: " . BASE . "/campus/tarefa/{$TaskRedirect}#play");
        endif;

        $Read->LinkResult(DB_USERS, "user_id", $course_author, "user_name, user_lastname");
        $CourseTutor = $Read->getResult()[0];

        $Read->LinkResult(DB_EAD_COURSES_SEGMENTS, "segment_id", $course_segment);
        if ($Read->getResult()):
            $CourseSegment = $Read->getResult()[0];
        endif;

        $Read->FullRead("SELECT COUNT(class_id) AS ClassCount, SUM(class_time) AS ClassTime FROM " . DB_EAD_CLASSES . " WHERE module_id IN(SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :cs)", "cs={$course_id}");
        $ClassCount = $Read->getResult()[0]['ClassCount'];
        $ClassTime = floor($Read->getResult()[0]['ClassTime'] / 60) . ":" . str_pad($Read->getResult()[0]['ClassTime'] % 60, 2, 0, 0);
    endif;
endif;

//VALID ENROLLMENT
$Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE course_id = :course AND user_id = :user", "course={$course_id}&user={$user_id}");
if (!$Read->getResult()):
    $_SESSION['wc_ead_alert'] = ['yellow', "Oppsss {$user_name}, acesso negado:", "Você tentou acessar o curso {$course_title}, mas não é aluno dele!"];
    header("Location: " . BASE . "/campus");
    exit;
else:
    extract($Read->getResult()[0]);

    //ENROLLMENT EXPIRED
    if (!empty($enrollment_bonus)):
        $Read->LinkResult(DB_EAD_ENROLLMENTS, "enrollment_id", $enrollment_bonus, 'enrollment_end');
        if (!empty($Read->getResult()) && !empty($Read->getResult()[0]['enrollment_end']) && time() >= strtotime($Read->getResult()[0]['enrollment_end'])):
            $_SESSION['wc_ead_alert'] = ['red', "Oppsss, bônus bloqueado:", "Desculpe {$user_name}, mas para acessar um bônus a assinatura ou curso que liberou o mesmo não pode estar expirada(o)."];
            header("Location: " . BASE . "/campus");
            exit;
        endif;
    elseif (!empty($enrollment_end) && time() >= strtotime($enrollment_end)):
        $_SESSION['wc_ead_alert'] = ['red', "Oppsss, acesso expirado:", "Desculpe {$user_name}, mas sua assinatura ao curso {$course_title} expirou dia " . date("d/m/y \a\s H\hi", strtotime($enrollment_end)) . "."];
        header("Location: " . BASE . "/campus");
        exit;
    endif;

    //UPDATE ENROLLMENT ACCESS
    $UpdateEnrollmentAcess = ['enrollment_access' => date("Y-m-d H:i:s")];
    $Update->ExeUpdate(DB_EAD_ENROLLMENTS, $UpdateEnrollmentAcess, "WHERE enrollment_id = :enrol", "enrol={$enrollment_id}");

    //STUDENT CLASS COUNT
    $Read->FullRead("SELECT COUNT(student_class_id) as ClassStudentCount FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND course_id = :course AND student_class_check IS NOT NULL", "user={$user_id}&course={$course_id}");
    $ClassStudenCount = $Read->getResult()[0]['ClassStudentCount'];
endif;

//COURSE VARS
$course_cover = (file_exists("uploads/{$course_cover}") && !is_dir("uploads/{$course_cover}") ? "uploads/{$course_cover}" : 'admin/_img/no_image.jpg');

//GET FEEBACK ALERTS
if (!empty($_SESSION['wc_ead_alert'])):
    echo "<div class='wc_ead_alert' style='display: flex;'>
    <div class='wc_ead_alert_box {$_SESSION['wc_ead_alert'][0]}'>
        <div class='wc_ead_alert_icon icon-switch icon-notext'></div><div class='wc_ead_alert_text'>
            <p class='wc_ead_alert_title'>{$_SESSION['wc_ead_alert'][1]}</p>
            <p class='wc_ead_alert_content'>{$_SESSION['wc_ead_alert'][2]}</p>
        </div><div class='wc_ead_alert_close'><span class='icon-cross icon-notext'></span></div>
    </div>
</div>";
    unset($_SESSION['wc_ead_alert']);
endif;

//MORE VARS
$CourseCompletedPercent = ($ClassStudenCount && $ClassCount ? round(($ClassStudenCount * 100) / $ClassCount) : "0");

//GET PENDING CLASS
$Table1 = DB_EAD_MODULES;
$Table2 = DB_EAD_CLASSES;
$Read->FullRead("SELECT {$Table2}.class_id, {$Table2}.class_name, {$Table2}.class_title FROM {$Table1}, {$Table2} WHERE {$Table1}.course_id = :course AND {$Table1}.module_id = {$Table2}.module_id AND {$Table2}.class_id NOT IN(SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " WHERE user_id = :user AND course_id = :course AND student_class_check IS NOT NULL) ORDER BY {$Table1}.module_order ASC, {$Table2}.class_order ASC LIMIT 1", "user={$user_id}&course={$course_id}");
$ClassPending = ($Read->getResult() ? $Read->getResult()[0] : null);
?>
<article class="wc_ead_course_course jwc_ead_restrict">
    <div class="wc_ead_content">
        <header class="wc_ead_course_course_header">
            <img src="<?= BASE; ?>/tim.php?src=<?= $course_cover; ?>&w<?= IMAGE_W / 3; ?>&h=<?= IMAGE_H / 3; ?>" alt="<?= $course_title; ?>" title="<?= $course_title; ?>"/>
            <h1 class="icon-lab">Curso <?= $course_title; ?></h1>
        </header>
        <div class="box box4">
            <p class="icon icon-mic icon-notext"></p>
            <p class="title">Tutor do Curso</p>
            <p><?= "{$CourseTutor['user_name']} {$CourseTutor['user_lastname']}"; ?></p>
        </div><div class="box box4">
            <p class="icon icon-clock icon-notext"></p>
            <p class="title">Duração do Curso</p>
            <p><?= $ClassTime; ?>h em <?= $ClassCount; ?> aulas</p>
        </div><div class="box box4">
            <p class="icon icon-stats-bars2 icon-notext"></p>
            <p class="title">Seu Andamento</p>
            <div class="progress"><span class="progress_bar" style="width: <?= $CourseCompletedPercent ?>%;"><?= $CourseCompletedPercent; ?>%</span></div>
        </div><div class="box box4">
            <p class="icon icon-play2 icon-notext"></p>
            <p class="title">Próxima Aula</p>
            <p><?= !empty($ClassPending) ? "<a href='" . BASE . "/campus/tarefa/{$ClassPending['class_name']}' title='{$ClassPending['class_title']}' class='wc_tooltip'>Continue de Onde Parou<span class='wc_tooltip_balloon'>{$ClassPending['class_title']}</span></a>" : "Nenhuma tarefa pendente!</a>"; ?></p>
        </div>
    </div>
</article>

<div class="wc_ead_content" style="padding-top: 10px;">
    <?php
    $Read->ExeRead(DB_EAD_MODULES, "WHERE course_id = :course ORDER BY module_order ASC", "course={$course_id}");
    $Modules = $Read->getResult();

    if (!$Modules):
        echo "<div class='trigger trigger_info trigger_none al_center icon-info m_top'>Ainda não existem módulos cadastrados para o curso {$course_title}!</div>";
    else:
        foreach ($Modules as $MOD):
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

            $barRequired = "";
            if ($module_required == 1):

                $Read->ExeRead(DB_EAD_STUDENT_CLASSES, "WHERE user_id = :user AND course_id = :course AND student_class_check IS NOT NULL AND class_id IN (SELECT class_id FROM " . DB_EAD_CLASSES . " WHERE module_id = :module)", "user={$user_id}&course={$course_id}&module={$module_id}");
                $ClassesChecked = $Read->getRowCount();

                $Read->ExeRead(DB_EAD_CLASSES, "WHERE module_id = :module AND course_id = :course", "module={$module_id}&course={$course_id}");
                $ClassesModule = $Read->getRowCount();

                if ($ClassesChecked < $ClassesModule):
                    $barRequired = "<span class='bar_red radius icon-flag wc_tooltip'>Módulo obrigatório<span style='font-size: 1.2em;' class='wc_tooltip_balloon'>Para assistir as demais aulas, conclua todas deste módulo.</span></span>";
                endif;

            endif;
            ?>
            <section class="wc_ead_course_module" id="<?= $module_name; ?>">
                <header class="module_header">
                    <h1 class="icon-tree"><?= $module_title; ?>: <?= $ModuleRelease; ?> <?= $barRequired; ?></h1>
                    <?= ($module_desc ? "<p>{$module_desc}</p>" : ''); ?>
                    <span class="wc_ead_course_module_bar"></span>
                </header>
                <?php
                $Read->ExeRead(DB_EAD_CLASSES, "WHERE module_id = :module ORDER BY class_order ASC", "module={$module_id}");
                if (!$Read->getResult()):
                    echo "<div class='trigger trigger_info trigger_none al_center icon-info'>Ainda não existem aulas cadastradas para o módulo {$module_title}!</div>";
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
                        $Read->FullRead("SELECT support_status FROM " . DB_EAD_SUPPORT . " WHERE user_id = :user AND class_id = :class", "user={$user_id}&class={$class_id}");
                        $TaskSupport = (!$Read->getResult() ? 'Não Abriu' : ($Read->getResult()[0]['support_status'] == 1 ? 'Em Aberto' : ($Read->getResult()[0]['support_status'] == 2 ? '<b>Respondida</b>' : 'Concluída')));
                        ?>
                        <article class="wc_ead_course_module_class <?= (!empty($ClassPending) && $ClassPending['class_id'] == $class_id ? 'active' : ''); ?>">
                            <h1 class="row">
                                <span class="icon-play2"><?= (($ModuleUnlocked || $_SESSION['userLogin']['user_level'] > 5) ? "<a href='" . BASE . "/campus/tarefa/{$class_name}' title='Estudar {$class_title}'>{$class_title}</a>" : $class_title); ?></span>
                            </h1><p class="row">
                                <span class="icon-hour-glass wc_tooltip"><?= $class_time; ?>min.<span class="wc_tooltip_balloon">É o tempo desta aula!</span></span>
                            </p><p class="row views">
                                <span class="icon-stats-dots wc_tooltip"><?= $ClassViews; ?><span class="wc_tooltip_balloon">Quantas vezes você viu!</span></span>
                            </p><p class="row">
                                <span class="icon-clock wc_tooltip"><?= $ClassPlay; ?><span class="wc_tooltip_balloon">Último acesso!</span></span>
                            </p><p class="row">
                                <span class="icon-bubbles3 wc_tooltip"><?= $TaskSupport; ?><span class="wc_tooltip_balloon">Minha dúvida!</span></span>
                            </p><p class="row">
                                <span class="wc_tooltip <?= $ClassCheck ? "icon-checkmark" : 'icon-checkmark2'; ?>"><?= $ClassCheck ? $ClassCheck : "00/00/00"; ?><span class="wc_tooltip_balloon">Marcou como concluída!</span></span>
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

