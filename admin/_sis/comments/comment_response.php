<?php
$AdminLevel = LEVEL_WC_COMMENTS;
if (!APP_COMMENTS || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

$Comment = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($Comment):
    $Comment = "WHERE id = {$Comment}";
else:
    $Comment = "WHERE status = 2";
endif;

$SupportStatus = [2 => 'Em Aberto', 1 => 'Respondido', 3 => 'Concluído'];
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-bubbles2">Responder Comentário</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=comments/home">Comentário</a>
            <span class="crumb">/</span>
            Responder
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Recarregar Comentários" href="dashboard.php?wc=comments/home" class="btn btn_blue icon-spinner11 icon-notext"></a>
        <a title="Recarregar Comentários" href="dashboard.php?wc=comments/comment_response" class="btn btn_green icon-play3">Play</a>
    </div>
</header>

<div class="dashboard_content">
    <section class="box box100">
        <div class="panel" style="padding: 30px;">
            <?php
            $Read->ExeRead(DB_COMMENTS, "{$Comment} ORDER BY created ASC LIMIT 1");
            if (!$Read->getResult()):
                echo "<div class='trigger trigger_success trigger_none al_center icon-heart font_medium'>Não existem mais comentários em aberto {$_SESSION['userLogin']['user_name']} :)</div><div class='clear'></div>";
            else:
                $Comm = $Read->getResult()[0];
                extract($Read->getResult()[0]);

                //SOURCE COMMENT
                if ($Comm['post_id']):
                    $Read->FullRead("SELECT post_name, post_title FROM " . DB_POSTS . " WHERE post_id = :id", "id={$Comm['post_id']}");
                    $Link = "artigo/{$Read->getResult()[0]['post_name']}";
                    $Title = $Read->getResult()[0]['post_title'];
                elseif ($Comm['pdt_id']):
                    $Read->FullRead("SELECT pdt_name, pdt_title FROM " . DB_PDT . " WHERE pdt_id = :id", "id={$Comm['pdt_id']}");
                    $Link = "produto/{$Read->getResult()[0]['pdt_name']}";
                    $Title = $Read->getResult()[0]['pdt_title'];
                elseif ($Comm['page_id']):
                    $Read->FullRead("SELECT page_name, page_title FROM " . DB_PAGES . " WHERE page_id = :id", "id={$Comm['page_id']}");
                    $Link = "{$Read->getResult()[0]['page_name']}";
                    $Title = $Read->getResult()[0]['page_title'];
                endif;

                //USER
                $Read->LinkResult(DB_USERS, "user_id", "{$user_id}", 'user_id, user_name, user_lastname, user_email, user_thumb');
                $user_task = $Read->getResult()[0];
                $UserThumb = "../uploads/{$user_task['user_thumb']}";
                $user_task['user_thumb'] = (file_exists($UserThumb) && !is_dir($UserThumb) ? "uploads/{$user_task['user_thumb']}" : 'admin/_img/no_avatar.jpg');

                //STATUS
                $TicketStatus = ($status == 2 ? "<span class='status bar_red radius'>Em Aberto</span>" : ($status == 1 ? "<span class='status bar_blue radius'>Respondido</span>" : ($status == 3 ? "<span class='status bar_green radius'>Concluído</span>" : '')));

                //LIKES
                $Read->FullRead("SELECT user_id, user_name, user_lastname FROM " . DB_USERS . " WHERE user_id IN(SELECT user_id FROM " . DB_COMMENTS_LIKES . " WHERE comm_id = :comm)", "comm={$Comm['id']}");
                if ($Read->getResult()):
                    $getLikes = array();
                    foreach ($Read->getResult() as $UserLike):

                        if (APP_USERS == 1):
                            $getLikes[] = "<a target='_blank' title='Ver Usuário' href='dashboard.php?wc=users/create&id={$UserLike['user_id']}'>{$UserLike['user_name']} {$UserLike['user_lastname']}</a>";
                        elseif (APP_EAD == 1):
                            $getLikes[] = "<a target='_blank' title='Ver Usuário' href='dashboard.php?wc=teach/students_gerent&id={$UserLike['user_id']}'>{$UserLike['user_name']} {$UserLike['user_lastname']}</a>";
                        else:
                            $getLikes[] = "<a target='_blank' title='Ver Usuário' href='javascript:void(0)'>{$UserLike['user_name']} {$UserLike['user_lastname']}</a>";
                        endif;
                    endforeach;
                    $Likes = implode(', ', $getLikes);
                else:
                    $Likes = '<span class="na">N/A</span>';
                endif;
                ?>
                <article class="ead_support_response" id="<?= $id; ?>" style="margin-bottom: 20px;">
                    <div class="ead_support_response_avatar">
                        <img class="rounded" src="<?= BASE; ?>/tim.php?src=<?= $user_task['user_thumb']; ?>&w=<?= round(AVATAR_W / 2); ?>&h=<?= round(AVATAR_H / 2); ?>" alt="<?= $user_task['user_name']; ?>" title="<?= $user_task['user_lastname']; ?>"/>
                    </div><div class="ead_support_response_content">
                        <header class="ead_support_response_content_header">
                            <h1>Comentário de <a target="_blank" <h1>Comentário de <a target="_blank" href="dashboard.php?wc=<?=(APP_EAD == 1 ? 'teach/students_gerent&id=' : 'users/create&id=');?><?= $user_task['user_id']; ?>" title="<?= "{$user_task['user_name']} {$user_task['user_lastname']}"; ?>"><?= "{$user_task['user_name']} {$user_task['user_lastname']}"; ?></a> dia <?= date('d/m/Y H\hi', strtotime($created)); ?> <span class="j_comment_status"><?= $TicketStatus; ?></span></h1>
                            <p>Em: <a target="_blank" title="Ver <?= $Title; ?>" href="<?= BASE; ?>/<?= $Link; ?>#<?= $id; ?>"><?= $Title; ?></a></p>
                        </header>

                        <div class="htmlchars response_chars"><?= $comment; ?></div>

                        <div class="ead_support_response_actions">
                            <span rel='ead_support_response' class='j_delete_action icon-cross btn btn_red' id='<?= $id; ?>'>Apagar</span>
                            <span rel='ead_support_response' callback='Comments' callback_action='remove_comment' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none;' id='<?= $id; ?>'>Deletar Resposta?</span>
                            <span class="btn btn_blue icon-pencil2 j_comment_action" data-action='comment_edit' id="<?= $id; ?>">Editar</span>

                            <?php
                            if ($status == 2):
                                ?>
                                <span class="btn btn_green icon-checkmark j_comment_action ead_support_finish" data-action='comment_completed' id="<?= $id; ?>">Concluir</span>
                                <?php
                            endif;

                            //ACTIONS COMMENTS
                            $Read->FullRead("SELECT id FROM " . DB_COMMENTS_LIKES . " WHERE user_id = :user AND comm_id = :comm", "user={$_SESSION['userLogin']['user_id']}&comm={$Comm['id']}");
                            if (!$Read->getResult()):
                                ?>
                                <a style="color: #fff; font-weight: bold;" href='#<?= $Comm['id']; ?>' class='btn btn_blue wc_comment_action icon-heart' id="<?= $Comm['id']; ?>" rel='<?= $Comm['id']; ?>' action='like' href='Gostei do Comentário' title='Gostei do Comentário'>GOSTEI :)</a>
                                <?php
                            endif;
                            ?>

                            <div class='comm_likes icon-heart' id='<?= $Comm['id']; ?>'><span><?= $Likes; ?></span></div>
                        </div>

                        <div class="j_content">
                            <?php
                            //COUNT REPLIES
                            $Read->ExeRead(DB_COMMENTS, "WHERE alias_id = :id ORDER BY created ASC", "id={$id}");
                            $Reply = $Read->getResult();

                            if ($Reply):
                                foreach ($Reply as $ResponseReply):

                                    //VAR USER
                                    $Read->LinkResult(DB_USERS, "user_id", "{$ResponseReply['user_id']}", 'user_id, user_name, user_lastname, user_email, user_thumb');
                                    $user_reply = $Read->getResult()[0];
                                    $UserThumb = "../uploads/{$user_reply['user_thumb']}";
                                    $user_reply['user_thumb'] = (file_exists($UserThumb) && !is_dir($UserThumb) ? "uploads/{$user_reply['user_thumb']}" : 'admin/_img/no_avatar.jpg');

                                    echo "<article class='ead_support_response ead_support_response_reply reply' id='{$ResponseReply['id']}'>
                                                <div class='ead_support_response_avatar'>
                                                    <img class='rounded' src='" . BASE . "/tim.php?src={$user_reply['user_thumb']}&w=" . round(AVATAR_W / 2) . "&h=" . round(AVATAR_H / 2) . "' alt='{$user_reply['user_name']}' title='{$user_reply['user_lastname']}'/>
                                                </div><div class='ead_support_response_content'>
                                                    <header class='ead_support_response_content_header'>
                                                        <h1>Resposta de <a target='_blank' href='dashboard.php?wc=".(APP_EAD == 1 ? 'teach/students_gerent&id=' : 'users/create&id=')."{$user_reply['user_id']}' title='{$user_reply['user_name']} {$user_reply['user_lastname']}'>{$user_reply['user_name']} {$user_reply['user_lastname']}</a> dia " . date('d/m/Y H\hi', strtotime($ResponseReply['created'])) . "</h1>
                                                    </header>
                                                    <div class='htmlchars reply_chars'>{$ResponseReply['comment']}</div>
                                                    <div class='ead_support_response_actions'>
                                                        <span rel='ead_support_response_reply' class='j_delete_action icon-cross btn btn_red' id='{$ResponseReply['id']}'>Apagar</span>
                                                        <span rel='ead_support_response_reply' callback='Comments' callback_action='remove' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none;' id='{$ResponseReply['id']}'>Deletar Resposta?</span>
                                                        <span class='btn btn_blue icon-pencil2 center j_ead_support_action' data-action='ead_support_reply_edit' id='{$ResponseReply['id']}'>Editar</span>
                                                    </div>
                                                </div>       
                                                <div class='ead_support_response_edit_modal reply'>
                                                    <form name='class_adda' action='' method='post' enctype='multipart/form-data'>
                                                        <p class='title icon-pencil2'>Atualizar Resposta de {$user_reply['user_name']} {$user_reply['user_lastname']}</p>
                                                        <span class='btn btn_red icon-cross icon-notext ead_support_response_edit_modal_close j_comment_action_close'></span>
                                                        <input type='hidden' name='callback' value='Comments'/>
                                                        <input type='hidden' name='callback_action' value='edit_response'/>
                                                        <input type='hidden' name='id' value='{$ResponseReply['id']}'/>
                                                        <input type='hidden' name='user_id' value='{$ResponseReply['user_id']}'/>
                                                        <input type='hidden' name='type' value='reply'/>
                                                        <label class='label'>
                                                            <textarea class='work_mce_basic' style='font-size: 1em;' name='comment' rows='3'>" . htmlspecialchars($ResponseReply['comment']) . "</textarea>
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
                        if ($rank):
                            $ReviewPositive = '<span class="icon-star-full icon-notext font_green"></span>';
                            $ReviewNegative = '<span class="icon-star-empty icon-notext font_red"></span>';
                            ?>
                            <footer class="ead_support_response_review">
                                <h1 class="icon-star-half">Avaliação: <?= $rank; ?></h1>
                            </footer>
                        <?php endif; ?>
                    </div>

                    <!--FORM EDIT RESPONSE-->
                    <div class="ead_support_response_edit_modal response">
                        <form name="class_edit" action="" method="post" enctype="multipart/form-data">
                            <p class="title icon-pencil2">Atualizar Pergunta de <?= "{$user_task['user_name']} {$user_task['user_lastname']}"; ?></p>
                            <span class="btn btn_red icon-cross icon-notext ead_support_response_edit_modal_close j_ead_support_action_close"></span>

                            <input type="hidden" name="callback" value="Comments"/>
                            <input type="hidden" name="callback_action" value="edit_response"/>
                            <input type='hidden' name='id' value='<?= $Comm['id']; ?>'/>
                            <input type='hidden' name='user_id' value='<?= $Comm['user_id']; ?>'/>
                            <input type='hidden' name='type' value='comment'/>

                            <label class="label">
                                <textarea class="work_mce_basic" style="font-size: 1em;" name="comment" rows="3"><?= htmlspecialchars($comment); ?></textarea>
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

                            <input type="hidden" name="callback" value="Comments"/>
                            <input type="hidden" name="callback_action" value="remove"/>
                            <input type="hidden" name="del_id" value="<?= $id; ?>"/>

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

                <form name="class_add" class="ead_support_response_form" action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="callback" value="Comments"/>
                    <input type="hidden" name="callback_action" value="response"/>
                    <input type="hidden" name="alias_id" value="<?= $id; ?>"/>
                    <input type='hidden' name='user_id' value='<?= $Comm['user_id']; ?>'/>

                    <label class="label">
                        <textarea class="work_mce_basic" style="font-size: 1em;" name="comment" rows="3"></textarea>
                    </label>

                    <div class="wc_actions" style="margin-top: 25px;">
                        <button style="padding: 10px 25px; margin-right: 10px;" class="btn btn_green icon-bubble">RESPONDER</button>
                        <a class="btn btn_yellow icon-arrow-right" style="padding: 10px 25px;" href="dashboard.php?wc=comments/comment_response" title="Próximo Comentário Pendente">PRÓXIMO COMENTÁRIO</a>
                        <img class="form_load none" style="margin-left: 10px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                    </div>
                </form>
            </div>
        <?php
        endif;
        ?>
    </section>
</div>
<script src="_js/wccomments.js"></script>