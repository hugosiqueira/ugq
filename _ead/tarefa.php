<?php
//CLASS
if (empty($Read)):
    $Read = new Read;
endif;

if (empty($Create)):
    $Create = new Create;
endif;

if (empty($Update)):
    $Update = new Update;
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

//GET COURSE NAME BY SESSION
if (!empty($_SESSION['wc_student_course'])):
    $Read->FullRead("SELECT course_name FROM " . DB_EAD_COURSES . " WHERE course_id = :course", "course={$_SESSION['wc_student_course']}");
    if ($Read->getResult()):
        $course_name = $Read->getResult()[0]['course_name'];
        $course_id = $_SESSION['wc_student_course'];
    else: $_SESSION['wc_ead_alert'] = ["yellow", "Oppps {$user_name}, erro ao acessar:", "Não foi possível identificar o curso que você tentou acessar :/"];
        header("Location: " . BASE . "/campus");
        exit;
    endif;
endif;

//GET TASK
$ClassName = (!empty($URL[2]) ? strip_tags($URL[2]) : null);
if (!$ClassName):
    $_SESSION['wc_ead_alert'] = ["yellow", "Oppps {$user_name}, erro ao acessar:", "Não foi possível identificar a aula que você tentou acessar :/"];
    header("Location: " . BASE . "/campus/curso/{$course_name}");
    exit;
else: $Read->ExeRead(DB_EAD_CLASSES, "WHERE class_name = :name AND course_id = :course", "name={$ClassName}&course={$course_id}");
    if (!$Read->getResult()):
        $_SESSION['wc_ead_alert'] = ["yellow", "Oppps {$user_name}, erro ao acessar:", "Não foi possível identificar a aula que você tentou acessar :/"];
        header("Location: " . BASE . "/campus/curso/{$course_name}");
        exit;
    else: extract($Read->getResult()[0]);

        $Read->ExeRead(DB_EAD_COURSES, "WHERE course_id = :course", "course={$course_id}");
        extract($Read->getResult()[0]);

        //STUDENT CONTROL NAVIGATION RENEW
        $_SESSION['wc_student_course'] = $course_id;
    endif;
endif;

//VALID ENROLLMENT
$Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE course_id = :course AND user_id = :user", "course={$course_id}&user={$user_id}");
if (!$Read->getResult()):
    $_SESSION['wc_ead_alert'] = ['yellow', "Oppsss {$user_name}, acesso negado:", "Você tentou acessar o curso {$course_name}, mas não é aluno dele!"];
    header("Location: " . BASE . "/campus");
    exit;
else: extract($Read->getResult()[0]);
    $_SESSION['wc_student_enrollment_id'] = ($_SESSION['userLogin']['user_level'] < 6 ? $enrollment_id : null);

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
endif;

//VALID MODULE
$Read->ExeRead(DB_EAD_MODULES, "WHERE course_id = :cs AND module_id = :mod", "cs={$course_id}&mod={$module_id}");
if (!$Read->getResult()):
    $_SESSION['wc_ead_alert'] = ['yellow', "Oppsss, erro ao acessar:", "Desculpe {$user_name}, mas não foi possível identificar o módulo da aula {$class_title}."];
    header("Location: " . BASE . "/campus/curso/{$course_name}");
    exit;
else: extract($Read->getResult()[0]);

//REQUEST MODULE SEARCH
    $Read->FullRead(
            "SELECT "
            . "COUNT(c.class_id) as class_pending, "
            . "m.module_title "
            . "FROM " . DB_EAD_CLASSES . " c "
            . "INNER JOIN " . DB_EAD_MODULES . " m ON m.module_id = c.module_id "
            . "WHERE m.course_id = :cs "
            . "AND m.module_order < :mo "
            . "AND m.module_required = 1 "
            . "AND c.class_id NOT IN(SELECT class_id FROM " . DB_EAD_STUDENT_CLASSES . " s WHERE s.course_id = :cs AND s.user_id = :us AND s.student_class_check IS NOT NULL) "
            . "GROUP BY m.module_title "
            . "ORDER BY m.module_order ASC, c.class_order ASC "
            . "LIMIT 1", "cs={$course_id}&mo={$module_order}&us={$user_id}"
    );

//REQUEST MODULE VALIDATION
    $ClassesPendent = ($Read->getResult() ? $Read->getResult()[0]['class_pending'] : 0);
    if ($ClassesPendent > 0):
        if ($_SESSION['userLogin']['user_level'] <= 5):
            $_SESSION['wc_ead_alert'] = ['red', "Módulo {$Read->getResult()[0]['module_title']} obrigatório!", "Existem <b>{$Read->getResult()[0]['class_pending']} aulas pendente(s)</b> {$user_name}. Marque todas como concluídas para continuar o curso!"];
            header("Location: " . BASE . "/campus/curso/{$course_name}");
            exit;
        endif;
    endif;

//VALID FOR MODULE TIME TO ACCESS
    if (!empty($module_release_date)):
        $ReleaseUnlock = strtotime($module_release_date);
        $ModuleUnlocked = (($ReleaseUnlock <= time() && $ClassesPendent == 0) ? true : false);
        $ModuleUnlockDate = date("d/m/Y \a\s H\hi", strtotime($module_release_date));
    else:
        $ReleaseUnlock = strtotime($enrollment_start . "+{$module_release}days");
        $ModuleUnlocked = (($ReleaseUnlock <= time() && $ClassesPendent == 0) ? true : false);
        $ModuleUnlockDate = date("d/m/Y \a\s H\hi", strtotime($enrollment_start . "+{$module_release}days"));
    endif;

    if (!$ModuleUnlocked && $_SESSION['userLogin']['user_level'] < 6):
        $_SESSION['wc_ead_alert'] = ['red', "Módulo {$module_title} bloqueado:", "{$user_name}, acesse este módulo a partir do dia " . date("d/m/y \a\s H\hi", strtotime($enrollment_start . "+{$module_release}days")) . "."];
        header("Location: " . BASE . "/campus/curso/{$course_name}");
        exit;
    endif;
endif;

//VALID TASK AND COUNT CHECK
$DateThis = date('Y-m-d H:i:s');
$Read->ExeRead(DB_EAD_STUDENT_CLASSES, "WHERE user_id = :user AND course_id = :course AND class_id = :class", "user={$user_id}&course={$course_id}&class={$class_id}");
if ($Read->getResult()):
    extract($Read->getResult()[0]);

    if (empty($_SESSION['wc_student_class']) || $_SESSION['wc_student_class'] != $student_class_id):
        $UpdateStudenClass = ['student_class_play' => $DateThis, 'student_class_views' => $student_class_views + 1];
        $Update->ExeUpdate(DB_EAD_STUDENT_CLASSES, $UpdateStudenClass, "WHERE student_class_id = :class_id", "class_id={$student_class_id}");
        $student_class_views = $student_class_views + 1;
    endif;
else: $CreateStudentClass = ['user_id' => $user_id, 'enrollment_id' => $enrollment_id, 'course_id' => $course_id, 'class_id' => $class_id, 'student_class_play' => $DateThis, 'student_class_views' => 1];
    $Create->ExeCreate(DB_EAD_STUDENT_CLASSES, $CreateStudentClass);

    $Read->ExeRead(DB_EAD_STUDENT_CLASSES, "WHERE student_class_id = :class_id", "class_id={$Create->getResult()}");
    extract($Read->getResult()[0]);
endif;

//STUDENT CONTROL NAVIGATION
$_SESSION['wc_student_class'] = $student_class_id;
$_SESSION['wc_student_task'] = time();

//TASK VARS :: CLASS PREV
$ClassPreview = "<a class='icon-arrow-left class_next' href='" . BASE . "/campus/curso/{$course_name}#play' title='Voltar ao Índice do Curso'>Voltar ao Índice do Curso</a>";
$Read->FullRead("SELECT class_title, class_name FROM " . DB_EAD_CLASSES . " WHERE course_id = :course AND module_id = :module AND class_order < :order ORDER BY class_order DESC LIMIT 1", "course={$course_id}&module={$module_id}&order={$class_order}");
if ($Read->getResult()):
    $ClassPreview = "<a class='icon-arrow-left class_next' href='" . BASE . "/campus/tarefa/{$Read->getResult()[0]['class_name']}#play' title='{$Read->getResult()[0]['class_title']}'>" . Check::Chars($Read->getResult()[0]['class_title'], 32) . "</a>";
else: $Read->FullRead("SELECT class_title, class_name FROM " . DB_EAD_CLASSES . " WHERE course_id = :course AND module_id = (SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :course AND module_order < :order ORDER BY module_order DESC LIMIT 1) ORDER BY class_order DESC LIMIT 1", "course={$course_id}&order={$module_order}");
    if ($Read->getResult()):
        $ClassPreview = "<a class='icon-arrow-left class_next' href='" . BASE . "/campus/tarefa/{$Read->getResult()[0]['class_name']}#play' title='{$Read->getResult()[0]['class_title']}'>" . Check::Chars($Read->getResult()[0]['class_title'], 32) . "</a>";
    endif;
endif;

//TASK VARS :: CLASS NEXT
$ClassNext = "<a class='icon-arrow-right class_next' href='" . BASE . "/campus/curso/{$course_name}#play' title='Voltar ao Índice do Curso'>Voltar ao Índice do Curso</a>";
$Read->FullRead("SELECT class_title, class_name FROM " . DB_EAD_CLASSES . " WHERE course_id = :course AND module_id = :module AND class_order > :order ORDER BY class_order ASC LIMIT 1", "course={$course_id}&module={$module_id}&order={$class_order}");
if ($Read->getResult()):
    $ClassNext = "<a class='icon-arrow-right class_next' href='" . BASE . "/campus/tarefa/{$Read->getResult()[0]['class_name']}#play' title='{$Read->getResult()[0]['class_title']}'>" . Check::Chars($Read->getResult()[0]['class_title'], 32) . "</a>";
else: $Read->ExeRead(DB_EAD_MODULES, "WHERE course_id = :course AND module_order > :order ORDER BY module_order ASC LIMIT 1", "course={$course_id}&order={$module_order}");
    if ($Read->getResult()):
        //MODULE DATE OR DAYS
        if (!empty($Read->getResult()[0]['module_release_date'])):
            $CheckReleaseUnlock = strtotime($Read->getResult()[0]['module_release_date']);
            $CheckModuleUnlocked = ($CheckReleaseUnlock <= time() ? true : false);
        else:
            $CheckReleaseUnlock = strtotime($enrollment_start . "+{$Read->getResult()[0]['module_release']}days");
            $CheckModuleUnlocked = ($CheckReleaseUnlock <= time() ? true : false);
        endif;

        if ($CheckModuleUnlocked):
            $Read->FullRead("SELECT class_title, class_name FROM " . DB_EAD_CLASSES . " WHERE course_id = :course AND module_id = :mod ORDER BY class_order ASC LIMIT 1", "course={$course_id}&mod={$Read->getResult()[0]['module_id']}");
            if ($Read->getResult()):
                $ClassNext = "<a class='icon-arrow-right class_next' href='" . BASE . "/campus/tarefa/{$Read->getResult()[0]['class_name']}#play' title='{$Read->getResult()[0]['class_title']}'>" . Check::Chars($Read->getResult()[0]['class_title'], 32) . "</a>";
            endif;
        else:
            $ClassNextModule = $Read->getResult()[0];

            $DayOpen = (!empty($ClassNextModule['module_release_date'] && $ClassNextModule['module_release_date'] > date('Y-m-d H:i:s')) ? date("d/m/Y H:i", strtotime($ClassNextModule['module_release_date'])) : date("d/m/y H\hi", strtotime($enrollment_start . "+{$ClassNextModule['module_release']}days")));
            $DayThis = new DateTime(date("Y-m-d H:i:s"));
            $DaysFree = $ClassNextModule['module_release'] + 1;
            $DayPlay = (!empty($ClassNextModule['module_release_date'] && $ClassNextModule['module_release_date'] > date('Y-m-d H:i')) ? new DateTime($ClassNextModule['module_release_date']) : new DateTime($enrollment_start . "+{$DaysFree}days"));
            $DaysDif = str_pad($DayThis->diff($DayPlay)->days, 2, 0, 0);

            if ($DayThis->diff($DayPlay)->days > 99):
                $Diff = '+99 dias';
            elseif ($DayThis->diff($DayPlay)->days == 1):
                $Diff = str_pad($DayThis->diff($DayPlay)->days, 2, 0, 0) . ' dia';
            elseif ($DayThis->diff($DayPlay)->days > 0):
                $Diff = str_pad($DayThis->diff($DayPlay)->days, 2, 0, 0) . ' dias';
            else:
                $Diff = str_pad($DayThis->diff($DayPlay)->h, 2, 0, 0) . 'h' . str_pad($DayThis->diff($DayPlay)->m, 2, 0, 0);
            endif;

            $ClassNext = "<a class='icon-lock class_next' href='" . BASE . "/campus/curso/{$course_name}#{$ClassNextModule['module_name']}' title='Módulo {$ClassNextModule['module_title']} ainda não liberado!'><span class='wc_tooltip'>Próximo módulo libera em {$Diff}!<span class='wc_tooltip_balloon'>Módulo {$ClassNextModule['module_title']} libera dia " . $DayOpen . "</span></span></a>";
        endif;
    endif;
endif;

//COURSE VARS
$course_cover = (file_exists("uploads/{$course_cover}") && !is_dir("uploads/{$course_cover}") ? "uploads/{$course_cover}" : 'admin/_img/no_image.jpg');

//SUPPORT
$Read->FullRead("SELECT support_status FROM " . DB_EAD_SUPPORT . " WHERE user_id = :user AND class_id = :class", "user={$user_id}&class={$class_id}");
$TaskSupport = (!$Read->getResult() ? 'Não Abriu' : ($Read->getResult()[0]['support_status'] == 1 ? 'Em Aberto' : ($Read->getResult()[0]['support_status'] == 2 ? '<b>Respondida</b>' : 'Concluída')));
?>

<article class="wc_ead_course_task jwc_ead_restrict" id="play">
    <div class="wc_ead_content">
        <header>
            <h1 class="icon-lab"><?= $class_title; ?></h1>
            <p class="course">
                <a href="<?= BASE; ?>/campus/curso/<?= $course_name; ?>" title="<?= $course_title; ?>">Curso <?= $course_title; ?></a> <span><span class="icon-arrow-right icon-notext"></span></span> <a href="<?= BASE; ?>/campus/curso/<?= $course_name; ?>#<?= $module_name; ?>" title="<?= $module_title; ?>">Módulo <?= $module_title; ?></a>
            </p>
        </header>
    </div>
</article>

<?php if ($class_video): ?>
    <article class="wc_ead_course_task_media">
        <div class="wc_ead_content">
            <div class="wc_ead_course_task_media_play jwc_play_task">
                <p class="task">
                    <span class="icon-hour-glass"><?= $class_time; ?> min.</span>
                    <span class="icon-stats-dots"><?= str_pad($student_class_views, 4, 0, 0); ?></span>
                    <span class="icon-bubbles3"><?= $TaskSupport; ?></span>
                </p>

                <div class="embed-container">
                    <?php if (is_numeric($class_video)): ?>
                        <iframe src="https://player.vimeo.com/video/<?= $class_video; ?>?title=0&amp;byline=0&amp;portrait=0" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                    <?php else: ?>
                        <iframe width="640" height="360" src="https://www.youtube.com/embed/<?= $class_video; ?>?showinfo=0&amp;rel=0" frameborder="0" allowfullscreen></iframe>
                    <?php endif; ?>
                </div>
            </div>

            <nav class="wc_ead_course_task_media_nav">
                <?php
                echo $ClassPreview;
                echo $ClassNext;
                echo "<span class='jwc_ead_task'>";
                if (empty($student_class_check)):
                    if ($student_class_free || !EAD_STUDENT_CLASS_PERCENT):
                        echo "<span class='a check icon-checkmark2 jwc_ead_task_check'>Concluir Tarefa</span>";
                    else:
                        echo "<span class='a'><span class='icon-clock2 wc_tooltip'>Tarefa Pendente<span class='wc_tooltip_balloon'>Assista " . EAD_STUDENT_CLASS_PERCENT . "% da aula para concluir a tarefa!</span></span></span>";
                    endif;
                else:
                    echo "<span class='a active icon-checkmark jwc_ead_task_uncheck'>" . date("d/m/Y H\hi", strtotime($student_class_check)) . "</span>";
                endif;
                echo "</span>";
                ?>
            </nav>
            <?php
            if ($class_material):
                echo "<div style='text-align: center; margin-top: 40px;'><a class='btn btn_cta_green icon-download' href='" . BASE . "/_ead/download.php?f={$class_id}' target='_blanck' title='Baixar Material de Apoio'>CLIQUE AQUI PARA BAIXAR O MATERIAL</a></div>";
            endif;
            ?>
        </div>
    </article>
    <?php
endif;

if ($class_desc):
    ?>
    <article class="wc_ead_course_task_content">
        <div class="wc_ead_content">
            <div class="box box_side wc_ead_course_task_content_content">
                <div class="htmlchars" style="padding: 0; font-size: 1.1em;">
                    <?= $class_desc; ?>
                </div>
                <footer>
                    <?php
                    echo $ClassPreview;
                    echo $ClassNext;
                    ?>
                </footer>
            </div><aside class="box box_bar wc_ead_course_task_content_sidebar">
                <img class="course_cover" alt="<?= $course_title; ?>" title="<?= $course_title; ?>" src="<?= BASE; ?>/tim.php?src=<?= $course_cover; ?>&w=<?= IMAGE_W / 5; ?>&h=<?= IMAGE_H / 5; ?>"/>
                <nav class="wc_ead_course_task_media_nav wc_ead_course_task_media_nav_bar">
                    <?php
                    echo "<span class='jwc_ead_task'>";
                    if (empty($student_class_check)):
                        if ($student_class_free || !EAD_STUDENT_CLASS_PERCENT):
                            echo "<span class='a check icon-checkmark2 jwc_ead_task_check'>Concluir Tarefa</span>";
                        else:
                            echo "<span class='a'><span class='icon-clock2 wc_tooltip'>Tarefa Pendente<span class='wc_tooltip_balloon'>Assista " . EAD_STUDENT_CLASS_PERCENT . "% da aula para concluir a tarefa!</span></span></span>";
                        endif;
                    else:
                        echo "<span class='a active icon-checkmark jwc_ead_task_uncheck'>" . date("d/m/Y H\hi", strtotime($student_class_check)) . "</span>";
                    endif;
                    echo "</span>";
                    ?>
                </nav>
                <article>
                    <header>
                        <h1 class="icon-tree">Módulo <?= $module_title; ?>:</h1>
                    </header>
                    <div class="wc_ead_course_task_content_sidebar_links">
                        <?php
                        $Read->ExeRead(DB_EAD_CLASSES, "WHERE module_id = :module ORDER BY class_order ASC", "module={$module_id}");
                        if (!$Read->getResult()):
                            echo "<div class='trigger trigger_info trigger_none al_center icon-info'>Ainda não existem aulas aqui!</div>";
                        else:
                            foreach ($Read->getResult() as $ModuleClasses):
                                ?>
                                <a class="<?= ($ModuleClasses['class_id'] == $class_id ? 'active icon-arrow-right' : 'icon-play2'); ?>" title="Ir para <?= $ModuleClasses['class_title']; ?>" href="<?= BASE . "/campus/tarefa/{$ModuleClasses['class_name']}"; ?>#play"><?= $ModuleClasses['class_title']; ?></a>
                                <?php
                            endforeach;
                        endif;
                        ?>
                    </div>
                </article>
                <?php
                if ($class_material):
                    echo "<div style='text-align: center; margin-top: 10px;'><a style='width: 100%; font-size: 0.8em;' class='btn btn_cta_green icon-download' href='" . BASE . "/_ead/download.php?f={$class_id}' target='_blanck' title='Baixar Material de Apoio'>Baixar Material de Apoio!</a></div>";
                endif;
                ?>
            </aside>
        </div>
    </article>
    <?php
endif;

if (!$class_support):
    echo "<div class='wc_ead_course_task_forum_none'><div class='wc_ead_content'>Fórum fechado para a aula {$class_title}!</div></div>";
else:
    ?>
    <!--MODAL FROM REVIEW-->
    <div class="wc_ead_course_task_modal jwc_ticket_review_content">
        <div class="wc_ead_course_task_modal_content">
            <span class="btn btn_red icon-cross wc_ead_course_task_modal_content_close icon-notext j_wc_ticket_close"></span>
            <header class="review">
                <span class="wc_ead_course_task_modal_content_icon icon-heart icon-notext"></span>
                <h1>Enviar Avaliação:</h1>
            </header>
            <div class="wc_ead_course_task_modal_content_desc">
                <form name="wc_ead_student_task_ticket_review" method="post" action="">
                    <input type="hidden" name="support_id"/>
                    <label>
                        <span>Qual sua nota <?= $_SESSION['userLogin']['user_name']; ?>?</span>
                        <select name="support_review" required>
                            <option value="5">&starf;&starf;&starf;&starf;&starf; - Excelente</option>
                            <option value="4">&starf;&starf;&starf;&starf;&star; - Bom</option>
                            <option value="3">&starf;&starf;&starf;&star;&star; - Regular</option>
                            <option value="2">&starf;&starf;&star;&star;&star; - Ruim</option>
                            <option value="1">&starf;&star;&star;&star;&star; - Péssimo</option>
                        </select>
                    </label>
                    <label>
                        <span class="icon-sad2">
                            Teve algum problema com esse suporte?<br>
                            <small style="color: #888;">Informe o campo abaixo <b>para enviar sua reclamação!</b></small>
                        </span>
                        <textarea name="support_comment" rows="5"></textarea>
                    </label>

                    <div class="form_actions">
                        <button class="btn btn_blue icon-heart">Enviar Avaliação</button>
                        <img class="jwc_load" style="margin-left: 10px;" alt="Enviando Avaliação!" title="Enviando Avaliação!" src="<?= BASE; ?>/_ead/images/load.gif"/>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!--MODAL FROM REPLY-->
    <div class="wc_ead_course_task_modal jwc_ticket_reply_content">
        <div class="wc_ead_course_task_modal_content">
            <span class="btn btn_red icon-cross wc_ead_course_task_modal_content_close icon-notext j_wc_ticket_close"></span>
            <header class="reply">
                <span class="wc_ead_course_task_modal_content_icon icon-bubbles3 icon-notext"></span>
                <h1>Adicionar Resposta:</h1>
            </header>
            <div class="wc_ead_course_task_modal_content_desc">
                <form name="wc_ead_student_task_ticket_reply" method="post" action="">
                    <input type="hidden" name="support_id"/>
                    <label>
                        <span class="icon-bubble">Escreva sua Resposta:</span>
                        <textarea name="ticket_content" class="jwc_ead_editor" rows="4"></textarea>
                    </label>

                    <div class="form_actions">
                        <button class="btn btn_green icon-bubble">Enviar Resposta</button>
                        <img class="jwc_load" style="margin-left: 10px; margin-top: -2px;" alt="Enviando Resposta!" title="Enviando Resposta!" src="<?= BASE; ?>/_ead/images/load.gif"/>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <section class="wc_ead_course_task_forum">
        <div class="wc_ead_content">
            <header class="wc_ead_course_task_forum_header">
                <h1 class="icon-bubbles4">Fórum desta aula:</h1>
                <p>Tire suas dúvidas <b>sobre <?= $class_title; ?>!</b></p>
            </header>

            <p class="al_right" style="padding-bottom: 40px;"><span class="btn btn_green btn_xxlarge icon-bubbles3 m_right wc_ead_allsupport">Todas as Dúvidas</span><span class="btn btn_blue btn_xxlarge icon-bubbles2 wc_ead_mysupport">Minhas Dúvidas</span><p>

            <div class="jwc_ead_forum_content jwc_allsupport">
                <?php
                $Read->ExeRead(DB_EAD_SUPPORT, "WHERE class_id = :class AND (support_published = 1 OR user_id = {$_SESSION['userLogin']['user_id']} OR {$_SESSION['userLogin']['user_level']} > 5) ORDER BY support_status ASC, support_reply DESC, support_open DESC", "class={$class_id}");
                if (!$Read->getResult()):
                    echo '<div class="jwc_content"></div>';
                    echo "<div class='wc_ead_course_task_forum_none'>Olá {$_SESSION['userLogin']['user_name']}, ainda não existem dúvidas ou respostas para <b>{$class_title}!</b></div>";
                else:
                    foreach ($Read->getResult() as $Ticket):
                        $Read->LinkResult(DB_USERS, "user_id", $Ticket['user_id']);
                        $StudentSupport = $Read->getResult()[0];

                        $StudentSupport['user_name'] = ($StudentSupport['user_name'] ? $StudentSupport['user_name'] : 'Novo');
                        $StudentSupport['user_lastname'] = ($StudentSupport['user_lastname'] ? $StudentSupport['user_lastname'] : 'Aluno');
                        $StudentUserThumb = "uploads/{$StudentSupport['user_thumb']}";
                        $StudentSupport['user_thumb'] = (file_exists($StudentUserThumb) && !is_dir($StudentUserThumb) ? "uploads/{$StudentSupport['user_thumb']}" : 'admin/_img/no_avatar.jpg');

                        $TicketStatus = ($Ticket['support_status'] == 1 ? "<span class='status bar_red radius'>Em Aberto</span>" : ($Ticket['support_status'] == 2 ? "<span class='status bar_blue radius'>Respondido</span>" : ($Ticket['support_status'] == 3 ? "<span class='status bar_green radius'>Concluído</span>" : '')));
                        ?>
                        <article class="wc_ead_course_task_forum_ticket" id="<?= $Ticket['support_id']; ?>">
                            <div class="wc_ead_course_task_forum_ticket_thumb <?= ($StudentSupport['user_level'] > 5 ? 'admin' : ''); ?>">
                                <img class="rounded thumb" src="<?= BASE; ?>/tim.php?src=<?= $StudentSupport['user_thumb']; ?>&w=<?= AVATAR_W / 3; ?>&h=<?= AVATAR_H / 3; ?>"/>
                            </div><div class="wc_ead_course_task_forum_ticket_content">
                                <header class="wc_ead_course_task_forum_ticket_header">
                                    <h1 class="icon-bubble2"><span class="user">Pergunta de <?= "{$StudentSupport['user_name']} {$StudentSupport['user_lastname']}"; ?></span> <span class="time">dia <?= date("d/m/Y H\hi", strtotime($Ticket['support_open'])); ?></span> <?= $TicketStatus; ?></h1>
                                </header>
                                <div class="htmlchars"><?= $Ticket['support_content']; ?></div>
                                <div class="wc_ead_course_task_forum_response">
                                    <?php
                                    $StudentReply = null;
                                    $Read->ExeRead(DB_EAD_SUPPORT_REPLY, "WHERE support_id = :support ORDER BY response_open ASC", "support={$Ticket['support_id']}");
                                    if ($Read->getResult()):
                                        foreach ($Read->getResult() as $Reply):
                                            $Read->LinkResult(DB_USERS, "user_id", $Reply['user_id']);
                                            $StudentReply = $Read->getResult()[0];

                                            $StudentReply['user_name'] = ($StudentReply['user_name'] ? $StudentReply['user_name'] : 'Novo');
                                            $StudentReply['user_lastname'] = ($StudentReply['user_lastname'] ? $StudentReply['user_lastname'] : 'Aluno');
                                            $StudentReplyThumb = "uploads/{$StudentReply['user_thumb']}";
                                            $StudentReply['user_thumb'] = (file_exists($StudentReplyThumb) && !is_dir($StudentReplyThumb) ? "uploads/{$StudentReply['user_thumb']}" : 'admin/_img/no_avatar.jpg');
                                            ?>
                                            <article class="wc_ead_course_task_forum_ticket" id="<?= $StudentReply['response_id']; ?>">
                                                <div class="wc_ead_course_task_forum_ticket_thumb <?= ($StudentReply['user_level'] > 5 ? 'admin' : ''); ?>">
                                                    <img class="rounded thumb" src="<?= BASE; ?>/tim.php?src=<?= $StudentReply['user_thumb']; ?>&w=<?= AVATAR_W / 3; ?>&h=<?= AVATAR_H / 3; ?>"/>
                                                </div><div class="wc_ead_course_task_forum_ticket_content">
                                                    <header class="wc_ead_course_task_forum_ticket_header">
                                                        <h1 class="icon-bubbles3"><span class="user">Resposta de <?= "{$StudentReply['user_name']} {$StudentReply['user_lastname']}"; ?></span> <span class="time">dia <?= date("d/m/Y H\hi", strtotime($Reply['response_open'])); ?></span></h1>
                                                    </header>
                                                    <div class="htmlchars"><?= $Reply['response_content']; ?></div>
                                                </div>
                                            </article>
                                            <?php
                                        endforeach;
                                    else:
                                        echo "<div class='wc_ead_course_task_forum_ticket_line'></div>";
                                    endif;
                                    ?>
                                </div>
                                <div class="wc_ead_course_task_forum_ticket_actions">
                                    <?php
                                    //SUPPORT :: REVIEW PRESENTATION
                                    $ReviewPositive = '<span class="icon-star-full icon-notext font_green"></span>';
                                    $ReviewNegative = '<span class="icon-star-empty icon-notext font_red"></span>';
                                    $ReviewTicket = ($Ticket['support_review'] ? str_repeat($ReviewPositive, $Ticket['support_review']) . str_repeat($ReviewNegative, 5 - $Ticket['support_review']) : '');
                                    echo "<span class='review jwc_review_target'>{$ReviewTicket}</span>";

                                    //SUPPORT :: NEW OR UPDATE REVIEW
                                    if ($Ticket['user_id'] == $_SESSION['userLogin']['user_id'] && !empty($StudentReply)):
                                        echo "<span class='btn btn_blue icon-heart jwc_ticket_review' id='{$Ticket['support_id']}'>Avaliar Suporte</span>";
                                    endif;

                                    //SUPPORT :: RESPONSE ADD
                                    if (EAD_TASK_SUPPORT_STUDENT_RESPONSE || $_SESSION['userLogin']['user_id'] == $Ticket['user_id'] || $_SESSION['userLogin']['user_level'] > 5):
                                        echo "<span class='btn btn_green icon-bubble jwc_ticket_reply' id='{$Ticket['support_id']}'>Adicionar Resposta</span>";
                                    endif;
                                    ?>
                                </div>
                        </article>
                        <?php
                    endforeach;
                    echo '<div class="jwc_content"></div>';
                endif;
                ?>
            </div>


            <div class="jwc_ead_forum_content jwc_mysupport ds_none">
                <?php
                $Read->ExeRead(DB_EAD_SUPPORT, "WHERE class_id = :class AND user_id = :user ORDER BY support_status ASC, support_reply DESC, support_open DESC", "class={$class_id}&user={$_SESSION['userLogin']['user_id']}");
                if (!$Read->getResult()):
                    echo '<div class="jwc_content"></div>';
                    echo "<div class='wc_ead_course_task_forum_none'>Olá {$_SESSION['userLogin']['user_name']}, você não publicou dúvidas para <b>{$class_title}!</b></div>";
                else:
                    foreach ($Read->getResult() as $Ticket):
                        $Read->LinkResult(DB_USERS, "user_id", $Ticket['user_id']);
                        $StudentSupport = $Read->getResult()[0];

                        $StudentSupport['user_name'] = ($StudentSupport['user_name'] ? $StudentSupport['user_name'] : 'Novo');
                        $StudentSupport['user_lastname'] = ($StudentSupport['user_lastname'] ? $StudentSupport['user_lastname'] : 'Aluno');
                        $StudentUserThumb = "uploads/{$StudentSupport['user_thumb']}";
                        $StudentSupport['user_thumb'] = (file_exists($StudentUserThumb) && !is_dir($StudentUserThumb) ? "uploads/{$StudentSupport['user_thumb']}" : 'admin/_img/no_avatar.jpg');

                        $TicketStatus = ($Ticket['support_status'] == 1 ? "<span class='status bar_red radius'>Em Aberto</span>" : ($Ticket['support_status'] == 2 ? "<span class='status bar_blue radius'>Respondido</span>" : ($Ticket['support_status'] == 3 ? "<span class='status bar_green radius'>Concluído</span>" : '')));
                        ?>
                        <article class="wc_ead_course_task_forum_ticket" id="<?= $Ticket['support_id']; ?>">
                            <div class="wc_ead_course_task_forum_ticket_thumb <?= ($StudentSupport['user_level'] > 5 ? 'admin' : ''); ?>">
                                <img class="rounded thumb" src="<?= BASE; ?>/tim.php?src=<?= $StudentSupport['user_thumb']; ?>&w=<?= AVATAR_W / 3; ?>&h=<?= AVATAR_H / 3; ?>"/>
                            </div><div class="wc_ead_course_task_forum_ticket_content">
                                <header class="wc_ead_course_task_forum_ticket_header">
                                    <h1 class="icon-bubble2"><span class="user">Pergunta de <?= "{$StudentSupport['user_name']} {$StudentSupport['user_lastname']}"; ?></span> <span class="time">dia <?= date("d/m/Y H\hi", strtotime($Ticket['support_open'])); ?></span> <?= $TicketStatus; ?></h1>
                                </header>
                                <div class="htmlchars"><?= $Ticket['support_content']; ?></div>
                                <div class="wc_ead_course_task_forum_response">
                                    <?php
                                    $StudentReply = null;
                                    $Read->ExeRead(DB_EAD_SUPPORT_REPLY, "WHERE support_id = :support ORDER BY response_open ASC", "support={$Ticket['support_id']}");
                                    if ($Read->getResult()):
                                        foreach ($Read->getResult() as $Reply):
                                            $Read->LinkResult(DB_USERS, "user_id", $Reply['user_id']);
                                            $StudentReply = $Read->getResult()[0];

                                            $StudentReply['user_name'] = ($StudentReply['user_name'] ? $StudentReply['user_name'] : 'Novo');
                                            $StudentReply['user_lastname'] = ($StudentReply['user_lastname'] ? $StudentReply['user_lastname'] : 'Aluno');
                                            $StudentReplyThumb = "uploads/{$StudentReply['user_thumb']}";
                                            $StudentReply['user_thumb'] = (file_exists($StudentReplyThumb) && !is_dir($StudentReplyThumb) ? "uploads/{$StudentReply['user_thumb']}" : 'admin/_img/no_avatar.jpg');
                                            ?>
                                            <article class="wc_ead_course_task_forum_ticket" id="<?= $StudentReply['response_id']; ?>">
                                                <div class="wc_ead_course_task_forum_ticket_thumb <?= ($StudentReply['user_level'] > 5 ? 'admin' : ''); ?>">
                                                    <img class="rounded thumb" src="<?= BASE; ?>/tim.php?src=<?= $StudentReply['user_thumb']; ?>&w=<?= AVATAR_W / 3; ?>&h=<?= AVATAR_H / 3; ?>"/>
                                                </div><div class="wc_ead_course_task_forum_ticket_content">
                                                    <header class="wc_ead_course_task_forum_ticket_header">
                                                        <h1 class="icon-bubbles3"><span class="user">Resposta de <?= "{$StudentReply['user_name']} {$StudentReply['user_lastname']}"; ?></span> <span class="time">dia <?= date("d/m/Y H\hi", strtotime($Reply['response_open'])); ?></span></h1>
                                                    </header>
                                                    <div class="htmlchars"><?= $Reply['response_content']; ?></div>
                                                </div>
                                            </article>
                                            <?php
                                        endforeach;
                                    else:
                                        echo "<div class='wc_ead_course_task_forum_ticket_line'></div>";
                                    endif;
                                    ?>
                                </div>
                                <div class="wc_ead_course_task_forum_ticket_actions">
                                    <?php
                                    //SUPPORT :: REVIEW PRESENTATION
                                    $ReviewPositive = '<span class="icon-star-full icon-notext font_green"></span>';
                                    $ReviewNegative = '<span class="icon-star-empty icon-notext font_red"></span>';
                                    $ReviewTicket = ($Ticket['support_review'] ? str_repeat($ReviewPositive, $Ticket['support_review']) . str_repeat($ReviewNegative, 5 - $Ticket['support_review']) : '');
                                    echo "<span class='review jwc_review_target'>{$ReviewTicket}</span>";

                                    //SUPPORT :: NEW OR UPDATE REVIEW
                                    if ($Ticket['user_id'] == $_SESSION['userLogin']['user_id'] && !empty($StudentReply)):
                                        echo "<span class='btn btn_blue icon-heart jwc_ticket_review' id='{$Ticket['support_id']}'>Avaliar Suporte</span>";
                                    endif;

                                    //SUPPORT :: RESPONSE ADD
                                    if (EAD_TASK_SUPPORT_STUDENT_RESPONSE || $_SESSION['userLogin']['user_id'] == $Ticket['user_id'] || $_SESSION['userLogin']['user_level'] > 5):
                                        echo "<span class='btn btn_green icon-bubble jwc_ticket_reply' id='{$Ticket['support_id']}'>Adicionar Resposta</span>";
                                    endif;
                                    ?>
                                </div>
                        </article>
                        <?php
                    endforeach;
                    echo '<div class="jwc_content"></div>';
                endif;
                ?>
            </div>
        </div>

        <article class="wc_ead_course_task_forum_ticket_new">
            <div class="wc_ead_content" style="padding-top: 0;">
                <header>
                    <h1 class="icon-bubbles2">Envie sua dúvida:</h1>
                    <p>Envie sua pergunta <b>sobre <?= $class_title; ?>!</b></p>
                </header>

                <?php
                //QTDE SUPPORT WITHOUT REVIEW
                $Read->ExeRead(DB_EAD_SUPPORT, "WHERE user_id = :user AND support_review IS NULL AND support_status = 1 AND class_id IN (SELECT class_id FROM " . DB_EAD_CLASSES . " WHERE course_id = :course)", "user={$_SESSION['userLogin']['user_id']}&course={$course_id}");

                if (EAD_TASK_SUPPORT_PENDING_REVIEW > 0 && $Read->getRowCount() >= EAD_TASK_SUPPORT_PENDING_REVIEW):
                    ?>
                    <article class="wc_ead_student_task_ticket_closed">
                        <div class="box box_bar al_center wc_ead_student_task_ticket_closed_icon">
                            <span class="icon-heart icon-notext font_red"></span>
                        </div><div class="box box_side">
                            <h3>Avalie nosso suporte <?= $user_name; ?>,</h3>
                            <p>Faz sentido para você ter o melhor suporte em seu curso? <b>Nesse momento você tem <?= $Read->getRowCount(); ?> tickets em aberto,</b> e para ter o melhor suporte precisamos de sua ajuda. Você só precisa avaliar nossas respostas para voltar a abrir novos tickts.</p>
                            <p class="icon-info" style="margin-bottom: 25px;">Volte ao íncice do seu curso, encontre aulas com tickets marcados como RESPONDIDOS, avalie nosso suporte e pronto...</p>
                            <a title="Acessar Íncide do Curso <?= $course_title; ?>" href="<?= BASE; ?>/campus/curso/<?= $course_name; ?>" class="btn btn_green btn_medium icon-arrow-left">ACESSAR ÍNDICE DO CURSO!</a>
                        </div>
                    </article>

                    <?php
                else:
                    ?>
                    <form name="wc_ead_student_task_ticket_add" method="post" enctype="multipart/form-data">
                        <div class="text_editor">
                            <textarea name="ticket_content" class="jwc_ead_editor" rows="4"></textarea>
                        </div>

                        <div class="form_actions">
                            <button class="btn btn_blue icon-bubble">ENVIAR MINHA DÚVIDA!</button>
                            <img class="jwc_load" style="margin-left: 10px; margin-top: -2px;" alt="Enviando Dúvida!" title="Enviando Dúvida!" src="<?= BASE; ?>/_ead/images/load.gif"/>
                        </div>
                    </form>
                <?php
                endif;
                ?>
            </div>

            <script src="<?= BASE; ?>/admin/_js/tinymce/tinymce.min.js"></script>
        </article>            
    </section>
<?php endif; ?>

