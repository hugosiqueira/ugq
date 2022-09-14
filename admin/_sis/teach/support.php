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
$SupportStatusClass = [1 => 'btn_red icon-bubble2', 2 => 'btn_blue icon-bubbles3', 3 => 'btn_green icon-bubbles'];
$SupportBarStatus = ['' => 'Todos os Tickets', 1 => 'Tickets Em Aberto', 2 => 'Tickets Respondidos', 3 => 'Tickets Concluídos'];

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
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-bubbles3">Suporte ao Aluno</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <?= !empty($SupportBarStatus[$SupportEadStatus]) ? $SupportBarStatus[$SupportEadStatus] : 'Todos os Tickets'; ?>
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Filtrar Tickets!" href="javascript:void(0)" class="btn btn_blue icon-notext icon-filter jwc_filters"></a>
        <a title="Recarregar Tickets!" href="dashboard.php?wc=teach/support&status=<?= $SupportEadStatus; ?>" class="btn btn_blue icon-spinner11 icon-notext"></a>
        <a title="Responder Tickets em Aberto!" href="dashboard.php?wc=teach/support_response&course_id=<?= (!empty($getData['course_id']) ? $getData['course_id'] : ""); ?>&module_id=<?= (!empty($getData['module_id']) ? $getData['module_id'] : ""); ?>&class_id=<?= (!empty($getData['class_id']) ? $getData['class_id'] : ""); ?>&user_id=<?= (!empty($getData['user_id']) ? $getData['user_id'] : ""); ?>&support_status=<?= (!empty($getData['support_status']) ? $getData['support_status'] : ""); ?>#response" class="btn btn_green icon-play3">Play</a>
    </div>
</header>

<div class="dashboard_content">
    <section class="box box100">

        <div class="wc_filters ds_none">
            <div class="panel_header default">
                <h2 class="icon-filter">Filtrar tickets a responder</h2>
            </div>

            <div class="panel m_botton">
                <form name="user_manager" action="" class="" method="post" enctype="multipart/form-data">

                    <div class="box box25">
                        <label class="label">
                            <span class="legend">Curso:</span>
                            <select name="course_id" class="jwc_combo" data-c='Courses' data-ca='module_filter'>
                                <option value="">Selecione um curso:</option>
                                <?php
                                $Read->FullRead("SELECT course_id, course_title FROM " . DB_EAD_COURSES . " WHERE course_status = :status ORDER BY course_order ASC", "status=1");
                                if (!$Read->getResult()):
                                    echo "<option value='' disabled='disabled'>Não há cursos ativos para suporte!</option>";
                                else:
                                    foreach ($Read->getResult() as $MODUPLOAD):
                                        echo "<option value='{$MODUPLOAD['course_id']}' " . ($getData['course_id'] == $MODUPLOAD['course_id'] ? "selected='selected'" : '') . ">{$MODUPLOAD['course_title']}</option>";
                                    endforeach;
                                endif;
                                ?>
                            </select>
                        </label>
                    </div><div class="box box25">
                        <label class="label">
                            <span class="legend">Módulo:</span>
                            <select name="module_id" class="jwc_combo_target_module jwc_combo" data-c='Courses' data-ca='class_select'>
                                <option value="">Selecione um módulo</option>
                                <?php
                                if (!empty($getData['module_id'])):
                                    $Read->ExeRead(DB_EAD_MODULES, "WHERE course_id = :course", "course={$getData['course_id']}");
                                    if ($Read->getResult()):
                                        foreach ($Read->getResult() as $Module):
                                            echo "<option value='{$Module['module_id']}' " . ($getData['module_id'] == $Module['module_id'] ? "selected='selected'" : '') . ">{$Module['module_title']}</option>";
                                        endforeach;
                                    endif;
                                endif;
                                ?>
                            </select>
                        </label>
                    </div><div class="box box25">
                        <label class="label">
                            <span class="legend">Aula:</span>
                            <select name="class_id" class="jwc_combo_target">
                                <option value="">Selecione uma aula</option>
                                <?php
                                if (!empty($getData['class_id'])):
                                    $Read->ExeRead(DB_EAD_CLASSES, "WHERE module_id = :module", "module={$getData['module_id']}");
                                    if ($Read->getResult()):
                                        foreach ($Read->getResult() as $Class):
                                            echo "<option value='{$Class['class_id']}' " . ($getData['class_id'] == $Class['class_id'] ? "selected='selected'" : '') . ">{$Class['class_title']}</option>";
                                        endforeach;
                                    endif;
                                endif;
                                ?>
                            </select>
                        </label>
                    </div><div class="box box25">
                        <label class="label">
                            <span class="legend">Status:</span>
                            <select name="support_status">
                                <option value="">Selecione um status</option>
                                <option value="1">Aberto</option>
                                <option value="2">Respondido</option>
                                <option value="3">Concluído</option>
                            </select>
                        </label>
                    </div>

                    <div class="box box100 al_right">
                        <button type="submit" class="btn btn_green icon-filter support_response_filter_list">Filtrar Resultados!</button>
                        <button type="submit" class="btn btn_blue icon-embed2 support_response_filter">Play no Rock!</button>
                        <img class="form_load none" style="margin-left: 10px; margin-top: -2px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                    </div>
                </form> 
            </div>
        </div>

        <div class="panel_header default">
            <h2 class="icon-bubbles3">#Responda seus tickets</h2>
        </div>

        <div class="panel">

            <?php
            $getPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            $Page = ($getPage ? $getPage : 1);
            $Pager = new Pager("dashboard.php?wc=teach/support&course_id=" . (!empty($getData['course_id']) ? $getData['course_id'] : '') . "&module_id=" . (!empty($getData['module_id']) ? $getData['module_id'] : '') . "&class_id=" . (!empty($getData['class_id']) ? $getData['class_id'] : '') . "&user_id=" . (!empty($getData['user_id']) ? $getData['user_id'] : '') . "&support_status=" . (!empty($getData['support_status']) ? $getData['support_status'] : '') . "&page=", "<", ">", 3);
            $Pager->ExePager($Page, 15);
            $Read->ExeRead(DB_EAD_SUPPORT, "WHERE 1 = 1 {$Where} ORDER BY support_reply DESC, support_open DESC LIMIT :limit OFFSET :offset", "{$ParseStr}&limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
            if (!$Read->getResult()):
                $Pager->ReturnPage();
                echo "<div class='trigger trigger_info trigger_none icon-info al_center'>Não existem tickets " . (!empty($SupportBarStatus[$SupportEadStatus]) ? "em {$SupportBarStatus[$SupportEadStatus]}" : "em Todos os Tickets") . "!</div><div class='clear'></div>";
            else:
                foreach ($Read->getResult() as $Tickets):
                    extract($Tickets);

                    $Read->LinkResult(DB_EAD_CLASSES, "class_id", $class_id, 'class_name, class_title');
                    $class_name = $Read->getResult()[0]['class_name'];
                    $class_title = $Read->getResult()[0]['class_title'];

                    $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = (SELECT course_id FROM " . DB_EAD_MODULES . " WHERE module_id = (SELECT module_id FROM " . DB_EAD_CLASSES . " WHERE class_id = :class))", "class={$class_id}");
                    $course_title = $Read->getResult()[0]['course_title'];

                    $Read->ExeRead(DB_EAD_SUPPORT_REPLY, "WHERE support_id = :support", "support={$support_id}");
                    $Reply = $Read->getResult();
                    $ReplyCount = ($Reply ? $Read->getRowCount() : '0');

                    $Read->ExeRead(DB_EAD_MODULES, "WHERE module_id = (SELECT c.module_id FROM " . DB_EAD_CLASSES . " c WHERE c.class_id = :class)", "class={$class_id}");
                    $Module = $Read->getResult()[0];

                    $Read->LinkResult(DB_USERS, "user_id", "{$user_id}", 'user_id, user_name, user_lastname, user_email');
                    $user_task = $Read->getResult()[0];

                    $ReviewPositive = '<span class="icon-star-full icon-notext font_green review"></span>';
                    $ReviewNegative = '<span class="icon-star-empty icon-notext font_red review"></span>';
                    $ReviewTicket = ($support_review ? str_repeat($ReviewPositive, $support_review) . str_repeat($ReviewNegative, 5 - $support_review) : '');
                    ?>
                    <article class="ead_support_single">
                        <h1 class="row">
                            <a class="a" href='dashboard.php?wc=teach/support&course_id=<?= $class_id; ?>' title='Ver Fórum na Aula'>#<?= str_pad($support_id, 4, 0, 0); ?> - <?= $course_title; ?></a>
                            <span class="icon-play2"><?= $class_title; ?></span>
                        </h1><p class="row icon-user-plus">
                            Por <a class="a" href="dashboard.php?wc=teach/support&user_id=<?= $user_task['user_id']; ?>" title="<?= "{$user_task['user_name']} {$user_task['user_lastname']}"; ?>"><?= "{$user_task['user_name']} {$user_task['user_lastname']}"; ?></a>
                            <span><?= $user_task['user_email']; ?></span>
                        </p><p class="row icon-bubble2">
                            <?= $ReplyCount; ?> Resposta<?= $ReplyCount > 1 ? 's' : ''; ?> <?= ($ReviewTicket ? " - {$ReviewTicket}" : ''); ?>
                        </p><p class="row icon-hour-glass">
                            <?= date('d/m/Y H\hi', strtotime(($support_reply ? $support_reply : $support_open))); ?>
                        </p><p class="row btn_support">
                            <a title="Responder Ticket" href="dashboard.php?wc=teach/support_response&course_id=<?= (!empty($getData['course_id']) ? $getData['course_id'] : ""); ?>&module_id=<?= (!empty($getData['module_id']) ? $getData['module_id'] : ""); ?>&class_id=<?= (!empty($getData['class_id']) ? $getData['class_id'] : ""); ?>&user_id=<?= (!empty($getData['user_id']) ? $getData['user_id'] : ""); ?>&support_status=<?= (!empty($getData['support_status']) ? $getData['support_status'] : ""); ?>&support_id=<?= $support_id; ?>" class="btn <?= $SupportStatusClass[$support_status]; ?>"><?= $SupportStatus[$support_status]; ?></a>
                        </p>
                    </article>
                    <?php
                endforeach;

            endif;
            ?>
        </div>
        <?php
        $Pager->ExePaginator(DB_EAD_SUPPORT, "WHERE 1 = 1 {$Where}", "{$ParseStr}");
        echo $Pager->getPaginator();
        ?>
    </section>
</div>