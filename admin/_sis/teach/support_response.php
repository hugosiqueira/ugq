<?php
$AdminLevel = LEVEL_WC_EAD_SUPPORT;
if (!APP_EAD || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

$SupportStatus = [1 => 'Em Aberto', 2 => 'Respondido', 3 => 'Concluído'];

$Support = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$Class = filter_input(INPUT_GET, 'class', FILTER_VALIDATE_INT);

//GET DATA SUPPORT
$getData = filter_input_array(INPUT_GET, FILTER_DEFAULT);
$Where = "";
$ParseStr = "";

//VALIDATE SUPPORT_ID
if (!empty($getData['support_id'])):
    $Where .= "AND support_id = :support_id ";
    $ParseStr .= "&support_id={$getData['support_id']}";
endif;

//VALIDATE USER_ID
if (!empty($getData['user_id'])):
    $Where .= "AND user_id = :user_id ";
    $ParseStr .= "&user_id={$getData['user_id']}";
endif;

//VALIDATE SUPPORT_STATUS
if (!empty($getData['support_status'])):
    $Where .= "AND support_status = :support_status ";
    $ParseStr .= "&support_status={$getData['support_status']}";
endif;

//VALIDATE CLASS, MODULE AND COURSE
if (!empty($getData['class_id'])):
    $Where .= "AND class_id = :class_id ";
    $ParseStr .= "&class_id={$getData['class_id']}";
elseif (!empty($getData['module_id']) && empty($getdata['class_id'])):
    $Where .= "AND class_id IN (SELECT class_id FROM " . DB_EAD_CLASSES . " WHERE module_id = :module_id) ";
    $ParseStr .= "&module_id={$getData['module_id']}";
elseif (!empty($getData['course_id']) && empty($getdata['module_id'])):
    $Where .= "AND class_id IN (SELECT class_id FROM " . DB_EAD_CLASSES . " WHERE module_id IN (SELECT module_id FROM " . DB_EAD_MODULES . " WHERE course_id = :course_id)) ";
    $ParseStr .= "&course_id={$getData['course_id']}";
endif;

//VALIDE CLICK "NEXT" DEFAULT
if (empty($getData['user_id']) && empty($getData['class_id']) && empty($getData['module_id']) && empty($getData['course_id']) && empty($getData['support_id'])):
    $Where .= "AND support_status = :support_status ";
    $ParseStr .= "&support_status=1";
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-bubbles">Responder Suporte</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/support">Suporte</a>
            <span class="crumb">/</span>
            Responder
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Recarregar Tickets!" href="dashboard.php?wc=teach/support_response&class=<?= $Class; ?>" class="btn btn_blue icon-spinner11 icon-notext"></a>
        <a title="Ver Tickets" href="dashboard.php?wc=teach/support" class="btn btn_green icon-bubble2">Ver Tickets</a>
    </div>
</header>

<div class="dashboard_content">
    <section class="box box100">
        <div class="panel" style="padding: 30px;">
            <?php
            $Read->ExeRead(DB_EAD_SUPPORT, "WHERE 1 = 1 {$Where} ORDER BY support_open ASC LIMIT 1", $ParseStr);
            if (!$Read->getResult()):
                if ($Class):
                    echo "<div class='trigger trigger_success trigger_none al_center icon-heart font_medium'>Não existem mais tickets em aberto para essa aula {$_SESSION['userLogin']['user_name']}, para responder outros tickets <a href='dashboard.php?wc=teach/support_response'>clique aqui</a> :)</div><div class='clear'></div>";
                else:
                    echo "<div class='trigger trigger_success trigger_none al_center icon-heart font_medium'>Não existem mais tickets em aberto {$_SESSION['userLogin']['user_name']} :)</div><div class='clear'></div>";
                endif;
            else:
                extract($Read->getResult()[0]);

                $Read->LinkResult(DB_EAD_CLASSES, "class_id", $class_id, 'class_name, class_title');
                $class_name = $Read->getResult()[0]['class_name'];
                $class_title = $Read->getResult()[0]['class_title'];

                $Read->FullRead("SELECT course_name, course_title FROM " . DB_EAD_COURSES . " WHERE course_id = (SELECT course_id FROM " . DB_EAD_MODULES . " WHERE module_id = (SELECT module_id FROM " . DB_EAD_CLASSES . " WHERE class_id = :class))", "class={$class_id}");
                $course_name = $Read->getResult()[0]['course_name'];
                $course_title = $Read->getResult()[0]['course_title'];

                $Read->ExeRead(DB_EAD_SUPPORT_REPLY, "WHERE support_id = :support ORDER BY response_open ASC", "support={$support_id}");
                $Reply = $Read->getResult();

                $Read->LinkResult(DB_USERS, "user_id", "{$user_id}", 'user_id, user_name, user_lastname, user_email, user_thumb');
                $user_task = $Read->getResult()[0];

                $UserThumb = "../uploads/{$user_task['user_thumb']}";
                $user_task['user_thumb'] = (file_exists($UserThumb) && !is_dir($UserThumb) ? "uploads/{$user_task['user_thumb']}" : 'admin/_img/no_avatar.jpg');

                $TicketStatus = ($support_status == 1 ? "<span class='status bar_red radius'>Em Aberto</span>" : ($support_status == 2 ? "<span class='status bar_blue radius'>Respondido</span>" : ($support_status == 3 ? "<span class='status bar_green radius'>Concluído</span>" : '')));
                ?>
                <article class="ead_support_response" id="<?= $support_id; ?>">
                    <div class="ead_support_response_avatar">
                        <img class="rounded" src="<?= BASE; ?>/tim.php?src=<?= $user_task['user_thumb']; ?>&w=<?= round(AVATAR_W / 2); ?>&h=<?= round(AVATAR_H / 2); ?>" alt="<?= $user_task['user_name']; ?>" title="<?= $user_task['user_lastname']; ?>"/>
                    </div><div class="ead_support_response_content">
                        <header class="ead_support_response_content_header">
                            <h1>Ticket de <a target="_blank" href="dashboard.php?wc=teach/students_gerent&id=<?= $user_task['user_id']; ?>" title="<?= "{$user_task['user_name']} {$user_task['user_lastname']}"; ?>"><?= "{$user_task['user_name']} {$user_task['user_lastname']}"; ?></a> dia <?= date('d/m/Y H\hi', strtotime($support_open)); ?> <span class="j_ead_support_status"><?= $TicketStatus; ?></span></h1>
                            <p>Curso <a target="_blank" title="Ver curso <?= $course_name; ?>" href="<?= BASE; ?>/campus/curso/<?= $course_name; ?>"><?= $course_title; ?></a> <span class="icon-arrow-right icon-notext"></span> Aula <a target="_blank" title="Ver na aula <?= $class_title; ?>" href="<?= BASE; ?>/campus/curso/<?= $course_name; ?>/<?= $class_name; ?>#<?= $support_id; ?>"><?= $class_title; ?></a></p>
                        </header>

                        <div class="htmlchars response_chars"><?= $support_content; ?></div>

                        <div class="ead_support_response_actions">
                            <?php if ($_SESSION['userLogin']['user_level'] >= EAD_TASK_SUPPORT_LEVEL_DELETE): ?>
                                <span class="btn btn_red icon-cross j_ead_support_action" data-action='ead_support_delete' id="<?= $support_id; ?>">Apagar</span>
                            <?php endif; ?>
                            <span class="btn btn_blue icon-pencil2 center j_ead_support_action" data-action='ead_support_edit' id="<?= $support_id; ?>">Editar</span>
                            <?php if ($support_status != 2): ?>
                                <span class="btn btn_blue icon-checkmark j_ead_support_action ead_support_finish" data-action='ead_support_set_answered' id="<?= $support_id; ?>">Marcar como Respondido</span>
                            <?php endif; ?>

                            <?php if ($support_published != 1): ?>
                                <span class="ead_support_publish"><span class="btn btn_yellow icon-eye-blocked j_ead_support_action jead_support_publish" data-action='ead_support_publish' id="<?= $support_id; ?>">Publicar</span></span>
                            <?php else: ?>
                                <span class="ead_support_publish"><span class="btn btn_green icon-checkmark j_ead_support_action" data-action='ead_support_unpublish' id="<?= $support_id; ?>">Publicado</span></span>
                            <?php endif; ?>
                        </div>

                        <div class="j_content">
                            <?php
                            if ($Reply):
                                foreach ($Reply as $ResponseReply):
                                    $Read->LinkResult(DB_USERS, "user_id", "{$ResponseReply['user_id']}", 'user_id, user_name, user_lastname, user_email, user_thumb');
                                    $user_reply = $Read->getResult()[0];

                                    $UserThumb = "../uploads/{$user_reply['user_thumb']}";
                                    $user_reply['user_thumb'] = (file_exists($UserThumb) && !is_dir($UserThumb) ? "uploads/{$user_reply['user_thumb']}" : 'admin/_img/no_avatar.jpg');

                                    echo "<article class='ead_support_response ead_support_response_reply reply' id='{$ResponseReply['response_id']}'>
                                    <div class='ead_support_response_avatar'>
                                        <img class='rounded' src='" . BASE . "/tim.php?src={$user_reply['user_thumb']}&w=" . round(AVATAR_W / 2) . "&h=" . round(AVATAR_H / 2) . "' alt='{$user_reply['user_name']}' title='{$user_reply['user_lastname']}'/>
                                    </div><div class='ead_support_response_content'>
                                        <header class='ead_support_response_content_header'>
                                            <h1>Ticket de <a target='_blank' href='dashboard.php?wc=teach/students_gerent&id={$user_reply['user_id']}' title='{$user_reply['user_name']} {$user_reply['user_lastname']}'>{$user_reply['user_name']} {$user_reply['user_lastname']}</a> dia " . date('d/m/Y H\hi', strtotime($ResponseReply['response_open'])) . "</h1>
                                        </header>
                                        <div class='htmlchars reply_chars'>{$ResponseReply['response_content']}</div>
                                        <div class='ead_support_response_actions'>
                                            <span rel='ead_support_response_reply' class='j_delete_action icon-cross btn btn_red' id='{$ResponseReply['response_id']}'>Apagar</span>
                                            <span rel='ead_support_response_reply' callback='Courses' callback_action='ead_support_reply_delete' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='{$ResponseReply['response_id']}'>Deletar Resposta?</span>
                                            <span class='btn btn_blue icon-pencil2 center j_ead_support_action' data-action='ead_support_reply_edit' id='{$ResponseReply['response_id']}'>Editar</span>
                                        </div>
                                    </div>       
                                    <div class='ead_support_response_edit_modal reply'>
                                        <form name='class_add' action='' method='post' enctype='multipart/form-data'>
                                            <p class='title icon-pencil2'>Atualizar Resposta de {$user_reply['user_name']} {$user_reply['user_lastname']}</p>
                                            <span class='btn btn_red icon-cross icon-notext ead_support_response_edit_modal_close j_ead_support_action_close'></span>
                                            <input type='hidden' name='callback' value='Courses'/>
                                            <input type='hidden' name='callback_action' value='ead_support_reply_edit_confirm'/>
                                            <input type='hidden' name='response_id' value='{$ResponseReply['response_id']}'/>
                                            <label class='label'>
                                                <textarea class='work_mce_basic' style='font-size: 1em;' name='response_content' rows='3'>" . htmlspecialchars($ResponseReply['response_content']) . "</textarea>
                                            </label>
                                            <div class='wc_actions' style='margin-top: 15px;'>
                                                <button class='btn btn_blue icon-pencil2'>ATUALIZAR RESPOSTA</button>
                                                <img class='form_load none' style='margin-left: 10px;' alt='Enviando Requisição!' title='Enviando Requisição!' src='_img/load.gif'/>
                                            </div>
                                        </form>
                                    </div>
                                </article>";
                                endforeach;
                            endif;
                            ?>
                        </div>

                        <?php
                        if ($support_review):
                            $ReviewPositive = '<span class="icon-star-full icon-notext font_green"></span>';
                            $ReviewNegative = '<span class="icon-star-empty icon-notext font_red"></span>';
                            $ReviewTicket = ($support_review ? str_repeat($ReviewPositive, $support_review) . str_repeat($ReviewNegative, 5 - $support_review) : '');
                            ?>
                            <footer class="ead_support_response_review">
                                <h1 class="icon-star-half">Avaliação: <?= $ReviewTicket; ?></h1>
                                <?= ($support_comment ? "<p>" . nl2br($support_comment) . "</p>" : ''); ?>
                            </footer>
                        <?php endif; ?>
                    </div>

                    <!--FORM EDIT RESPONSE-->
                    <div class="ead_support_response_edit_modal response">
                        <form name="class_add" action="" method="post" enctype="multipart/form-data">
                            <p class="title icon-pencil2">Atualizar Pergunta de <?= "{$user_task['user_name']} {$user_task['user_lastname']}"; ?></p>
                            <span class="btn btn_red icon-cross icon-notext ead_support_response_edit_modal_close j_ead_support_action_close"></span>

                            <input type="hidden" name="callback" value="Courses"/>
                            <input type="hidden" name="callback_action" value="ead_support_edit_confirm"/>
                            <input type="hidden" name="support_id" value="<?= $support_id; ?>"/>

                            <label class="label">
                                <textarea class="work_mce_basic" style="font-size: 1em;" name="support_content" rows="3"><?= htmlspecialchars($support_content); ?></textarea>
                            </label>

                            <div class="wc_actions" style="margin-top: 15px;">
                                <button class="btn btn_blue icon-pencil2">ATUALIZAR PERGUNTA</button>
                                <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                            </div>
                        </form>
                    </div>


                    <!--FORM DELETE RESPONSE-->
                    <div class="ead_support_response_edit_modal remove">
                        <form name="class_add" action="" method="post" enctype="multipart/form-data">
                            <p class="title icon-warning">Enviar notificação a <?= "{$user_task['user_name']} {$user_task['user_lastname']}"; ?>: (Opcional)</p>
                            <span class="btn btn_red icon-cross icon-notext ead_support_response_edit_modal_close j_ead_support_action_close"></span>

                            <input type="hidden" name="callback" value="Courses"/>
                            <input type="hidden" name="callback_action" value="ead_support_delete_confirm"/>
                            <input type="hidden" name="support_id" value="<?= $support_id; ?>"/>

                            <label class="label">
                                <textarea class="work_mce_basic" style="font-size: 1em;" name="mail_body" rows="3"></textarea>
                            </label>

                            <div class="wc_actions" style="margin-top: 15px;">
                                <button class="btn btn_red icon-cross">NOTIFICAR E EXCLUIR</button>
                                <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                            </div>
                        </form>
                    </div>

                </article>
            </div>
        
            <form name="class_add" class="ead_support_response_form" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="callback" value="Courses"/>
                <input type="hidden" name="callback_action" value="ead_support_add"/>
                <input type="hidden" name="support_id" value="<?= $support_id; ?>"/>

                <label class="label">
                    <textarea class="work_mce_basic" style="font-size: 1em;" name="response_content" rows="3">Olá <?= $user_task['user_name']; ?>, </textarea>
                </label>

                <div class="wc_actions" style="margin-top: 25px;">
                    <button style="padding: 10px 25px; margin-right: 10px;" class="btn btn_blue icon-bubble click_answer">RESPONDER</button>
                    <?php
                    if (!EAD_TASK_SUPPORT_REPLY_PUBLISH):
                        echo "<button style='padding: 10px 25px; margin-right: 10px;' class='btn btn_green icon-eye j_ead_support_action' data-action='ead_support_publish_redirect' id='$support_id'>PUBLICAR E CONTINUAR</button>";
                    endif;
                    ?>
                    <a class="btn btn_yellow icon-arrow-right" id="support_next" style="padding: 10px 25px;" href="dashboard.php?wc=teach/support_response&course_id=<?= (!empty($getData['course_id']) ? $getData['course_id'] : ''); ?>&module_id=<?= (!empty($getData['module_id']) ? $getData['module_id'] : ''); ?>&class_id=<?= (!empty($getData['class_id']) ? $getData['class_id'] : ''); ?>&user_id=<?= (!empty($getData['user_id']) ? $getData['user_id'] : ''); ?>&support_status=<?= (!empty($getData['support_status']) ? $getData['support_status'] : ''); ?>" title = "Próximo Ticket Pendente">CONTINUAR</a>
                    <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                </div>
            </form>
        <?php
        endif;
        ?>
    </section>
</div>