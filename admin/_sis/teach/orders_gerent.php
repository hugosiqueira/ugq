<?php
$AdminLevel = LEVEL_WC_EAD_ORDERS;
if (!APP_EAD || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

$OrderId = filter_input(INPUT_GET, 'id');
if (!$OrderId):
    $_SESSION['trigger_controll'] = "<b>OPPSS {$Admin['user_name']}</b>, você tentou gerenciar um pedido que não existe ou que foi removido recentemente!";
    header('Location: dashboard.php?wc=teach/orders');
    exit;
else:
    $Read->ExeRead(DB_EAD_ORDERS, "WHERE order_id = :order", "order={$OrderId}");
    if (!$Read->getResult()):
        $_SESSION['trigger_controll'] = "<b>OPPSS {$Admin['user_name']}</b>, você tentou gerenciar um pedido que não existe ou que foi removido recentemente!";
        header('Location: dashboard.php?wc=teach/orders');
        exit;
    endif;

    extract($Read->getResult()[0]);

    if ($order_sck == 'admin_free'):
        $Read->FullRead("SELECT user_name, user_lastname FROM " . DB_USERS . " WHERE user_id = :user", "user={$order_src}");
        $order_src = ($Read->getResult() ? "{$Read->getResult()[0]['user_name']} {$Read->getResult()[0]['user_lastname']}" : str_pad($order_sck, 4, 0, 0));
    endif;

    $Read->LinkResult(DB_USERS, "user_id", $user_id, 'user_id, user_name, user_lastname');
    extract($Read->getResult()[0]);

    $Read->LinkResult(DB_EAD_COURSES, "course_id", $course_id, 'course_title');
    $course_title = ($Read->getResult() ? $Read->getResult()[0]['course_title'] : null);
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-cart">Pedido #<?= str_pad($OrderId, 5, 0, 0); ?> <span style="font-size: 0.5em; display: inline-block; vertical-align: middle; margin-top: -5px; margin-left: 8px;" class="radius bar_<?= getWcHotmartStatusClass($order_status); ?>"><?= getWcHotmartStatus($order_status); ?></span></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            Gerenciar Pedido
        </p>
    </div>

    <div class="dashboard_header_search" id="<?= $OrderId; ?>">
        <a title="Ver Pedidos" href="dashboard.php?wc=teach/orders" class="btn btn_blue icon-cart">Ver Pedidos</a>
    </div>
</header>

<div class="dashboard_content">
    <article class="box box30">

        <div class="panel_header info">
            <h2 class="icon-cart">Detalhes do pedido:</h2>
        </div>

        <div class="panel wc_ead_order_detail">
            <p><b class="icon-user-plus">Aluno:</b><span><a class="a" href="dashboard.php?wc=teach/students_gerent&id=<?= $user_id; ?>" title="Ver Aluno"><?= "{$user_name} {$user_lastname}"; ?></a></span></p>
            <p><b class="icon-fire">Transação:</b><span><?= $order_transaction; ?></span></p>
            <p><?= ($course_title ? "<b class='icon-lab'>Curso:</b><span><a title='Ver Curso' href='dashboard.php?wc=teach/courses_gerent&id={$course_id}'>{$course_title}</a></span>" : "<b class='icon-gift'>Produto:</b></span>{$order_product_id} na Hotmart</span>"); ?></p>
            <p><b class="icon-calendar">D/ Pedido:</b><span><?= date('d/m/Y H\hi', strtotime($order_purchase_date)); ?></span></p>
            <p><b class="icon-hour-glass">Confirmação:</b><span><?= (!empty($order_confirmation_purchase_date) ? date('d/m/Y H\hi', strtotime($order_confirmation_purchase_date)) : 'Não confirmada'); ?></span></p>
            <p><b class="icon-clock">Garantia:</b><span><?= date('d/m/Y H\hi', strtotime($order_warranty_date)); ?></span></p>
            <p><b class="icon-price-tag">Oferta:</b><span><?= $order_off; ?></span></p>
            <p><b class="icon-link">Origem:</b><span><?= $order_sck; ?></span></p>
            <p><b class="icon-bookmark">Referência:</b><span><?= $order_src; ?></span></p>
            <p><b class="icon-coin-dollar">Valor:</b><span>$ <?= number_format($order_price, 2, ',', '.'); ?> (<?= ($order_currency ? $order_currency : 'BRL'); ?>)&nbsp;&nbsp;&nbsp;<img src="<?= BASE; ?>/_cdn/bootcss/images/pay_<?= $order_payment_type; ?>.png" width="22" alt="" title=""/></span></p>
            <p><b class="icon-coin-euro">Tarifas:</b><span>$ <?= number_format($order_cms_marketplace, 2, ',', '.'); ?> (<?= ($order_currency ? $order_currency : 'BRL'); ?>)</span></p>
            <p><b class="icon-users">Afiliado(s):</b><span><?= str_replace(";", ", ", $order_aff_name); ?></span></p>
            <p><b class="icon-coin-dollar">Comissão:</b><span>$ <?= ($order_cms_aff ? str_replace(";", ", $ ", str_replace('.', ',', $order_cms_aff)) : '0,00'); ?> (<?= ($order_currency ? $order_currency : 'BRL'); ?>)</span></p>
            <p><b class="icon-coin-dollar">Lucro:</b><span>$ <?= number_format($order_cms_vendor, 2, ',', '.'); ?> (<?= ($order_currency ? $order_currency : 'BRL'); ?>)</span></p>
        </div>
    </article><article class="box box70">

        <?php if (!empty($order_signature)): ?>
            <div class="panel wc_ead_order_single" style="margin-bottom: 10px;">
                <div class="wc_ead_order_single_bonus" style="background: #cce0f3;">
                    <h1 class="row icon-rss2">
                        Assinatura: <?= $order_signature; ?>
                        <span>Plano: <?= $order_signature_plan; ?></span>
                    </h1><p class="row icon-spinner10">
                        Período: <?= str_pad($order_signature_period, 2, 0, 0) ?> dias
                        <span>Recorrência do Plano: <?= str_pad($order_signature_recurrency, 2, 0, 0); ?></span>
                    </p><p class="row icon-hour-glass">
                        Vencimento:
                        <span>Dia <?= date('d/m/Y H\hi', strtotime("+{$order_signature_period}days")); ?></span>
                    </p><p class="row icon-spinner2">
                        Status: <?= mb_strtoupper($order_signature_status); ?> 
                        <span></span>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <?php
        $Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE enrollment_order = :order", "order={$order_id}");
        if ($Read->getResult()):
            $Enrollment = $Read->getResult()[0];
            $Read->LinkResult(DB_EAD_COURSES, "course_id", $Enrollment['course_id'], 'course_title');
            $CourseTitle = ($Read->getResult() ? "Curso: {$Read->getResult()[0]['course_title']}" : "Produto: {$order_product_id} na Hotmart");
            ?>
            <div class="panel wc_ead_order_single" style="margin-bottom: 20px;">
                <div class="wc_ead_order_single_enroll">
                    <h1 class="row icon-spinner4">
                        Matrícula #<?= str_pad($Enrollment['enrollment_id'], 5, 0, 0); ?>:
                        <span><?= $CourseTitle; ?></span>
                    </h1><p class="row icon-calendar">
                        Data de Inscrição:
                        <span><?= date("d/m/Y H\hi", strtotime($Enrollment['enrollment_start'])); ?></span>
                    </p><p class="row icon-clock">
                        Vencimento:
                        <span><?= (!empty($Enrollment['enrollment_end']) ? date("d/m/Y H\hi", strtotime($Enrollment['enrollment_end'])) : 'PARA SEMPRE'); ?></span>
                    </p><p class="row icon-hour-glass">
                        Último Acesso:
                        <span><?= (!empty($Enrollment['enrollment_access']) ? date("d/m/Y H\hi", strtotime($Enrollment['enrollment_access'])) : 'NUNCA ACESSOU'); ?></span>
                    </p>
                </div>
                <?php
                $Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE enrollment_bonus = :enrol ORDER BY enrollment_start ASC", "enrol={$Enrollment['enrollment_id']}");
                if ($Read->getResult()):
                    foreach ($Read->getResult() as $EnrollmentBonus):
                        $Read->LinkResult(DB_EAD_COURSES, "course_id", $EnrollmentBonus['course_id'], 'course_title');
                        $CourseBonusTitle = ($Read->getResult() ? "{$Read->getResult()[0]['course_title']}" : "{$order_product_id} na Hotmart");
                        ?>
                        <div class="wc_ead_order_single_bonus">
                            <h1 class="row icon-gift">
                                Matrícula #<?= str_pad($EnrollmentBonus['enrollment_id'], 5, 0, 0); ?>:
                                <span>Bônus: <?= $CourseBonusTitle; ?></span>
                            </h1><p class="row icon-calendar">
                                Data de Inscrição:
                                <span><?= date("d/m/Y H\hi", strtotime($EnrollmentBonus['enrollment_start'])); ?></span>
                            </p><p class="row icon-clock">
                                Vencimento:
                                <span><?= (!empty($EnrollmentBonus['enrollment_end']) ? date("d/m/Y H\hi", strtotime($EnrollmentBonus['enrollment_end'])) : 'PARA SEMPRE'); ?></span>
                            </p><p class="row icon-hour-glass">
                                Último Acesso:
                                <span><?= (!empty($EnrollmentBonus['enrollment_access']) ? date("d/m/Y H\hi", strtotime($EnrollmentBonus['enrollment_access'])) : 'NUNCA ACESSOU'); ?></span>
                            </p>
                        </div>
                        <?php
                    endforeach;
                endif;
                ?>
            </div>
            <?php
        else:
            $Read->LinkResult(DB_EAD_COURSES, "course_id", $course_id, 'course_title');
            $CourseTitle = ($Read->getResult() ? "Curso: {$Read->getResult()[0]['course_title']}" : "Produto: {$order_product_id} na Hotmart");
            ?>
            <div class="panel wc_ead_order_single" style="margin-bottom: 20px;">
                <div class="wc_ead_order_single_enroll">
                    <h1 class="row icon-spinner4">
                        Pedido #<?= str_pad($order_id, 5, 0, 0); ?>:
                        <span>Curso: <?= $CourseTitle; ?></span>
                    </h1><p class="row icon-calendar">
                        Data do pedido:
                        <span><?= date("d/m/Y H\hi", strtotime($order_purchase_date)); ?></span>
                    </p><p class="row icon-clock">
                        D/ Confirmação:
                        <span><?= (!empty($order_confirmation_purchase_date) ? date("d/m/Y H\hi", strtotime($order_confirmation_purchase_date)) : 'AGUARDANDO APROVAÇÃO'); ?></span>
                    </p><p class="row icon-hour-glass">
                        Estado do Pedido:
                        <span><?= ($order_delivered ? "PEDIDO ENTREGUE" : "AGUARDANDO APROVAÇÃO"); ?></span>
                    </p>
                </div>
            </div>
        <?php
        endif;
        ?>
        <div class="panel">
            <form name="order_gerent" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="callback" value="Courses"/>
                <input type="hidden" name="callback_action" value="ead_order_single_gerent"/>
                <input type="hidden" name="order_id" value="<?= $order_id; ?>"/>

                <?php if (!empty($Enrollment)): ?>
                    <label class="label">
                        <span class="legend">Id do Aluno:</span>
                        <input type="number" name="user_id" value="<?= $user_id; ?>" placeholder="Id do aluno:" required="required"/>
                        <p class="icon-warning font_yellow" style="margin: 15px 0; font-size: 0.8em;"><b>ATENÇÃO:</b> Ao mudar o aluno. Os cursos, bônus, e suportes serão movidos também!</p>
                    </label>

                    <label class="label">
                        <span class="legend">Curso:</span>
                        <select name="course_id">
                            <option value="">Selecionar um curso:</option>
                            <?php
                            $Read->FullRead("SELECT course_id, course_title FROM " . DB_EAD_COURSES . " ORDER BY course_title ASC");
                            if (!$Read->getResult()):
                                echo "<option disabled='disabled' value=''>Não existem cursos cadastrados!</option>";
                            else:
                                foreach ($Read->getResult() as $SelectCoursId):
                                    echo "<option " . ($course_id == $SelectCoursId['course_id'] ? "selected='selected'" : '') . " value='{$SelectCoursId['course_id']}'>{$SelectCoursId['course_title']}</option>";
                                endforeach;
                            endif;
                            ?>
                        </select>
                        <p class="icon-warning font_yellow" style="margin: 15px 0; font-size: 0.8em;"><b>ATENÇÃO:</b> Ao mudar o curso. Todos os bônus serão removidos para iniciarem novamente!</p>
                    </label>
                <?php endif; ?>

                <label class="label">
                    <span class="legend">Status:</span>
                    <select name="order_status">
                        <option value="">Selecionar um status:</option>
                        <?php
                        $UnBlockStatus = ['canceled', 'approved', 'chargeback', 'refunded', 'admin_free'];
                        foreach (getWcHotmartStatus() as $Status => $StrStatus):
                            echo "<option " . (!in_array($Status, $UnBlockStatus) ? "disabled='disabled'" : "") . " " . ($order_status == $Status ? "selected='selected'" : '') . " value='{$Status}'>{$StrStatus}</option>";
                        endforeach;
                        ?>
                    </select>
                    <p class="icon-warning font_yellow" style="margin: 15px 0 25px 0; padding-bottom: 10px; border-bottom: 1px dotted #eee; font-size: 0.8em;"><b>ATENÇÃO:</b> Aprovado libera o curso, <b>cancelado e devolvido remove o curso</b>, e chargeback bloqueia a conta!</p>
                </label>

                <div class="wc_actions" id="<?= $OrderId; ?>">
                    <span style="margin-right: 10px; font-weight: 500;" rel='wc_actions' class='j_delete_action icon-cross btn btn_red' id='<?= $OrderId; ?>'>DELETAR PEDIDO</span>
                    <span style="margin-right: 10px; display: none; font-weight: bold;" rel='wc_actions' callback='Courses' callback_action='ead_order_single_delete' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='<?= $OrderId; ?>'>EXCLUIR AGORA?</span>
                    <button name="public" value="1" class="btn btn_blue icon-pencil2">ATUALIZAR PEDIDO</button>
                    <img class="form_load none" style="margin-left: 10px; font-weight: 500;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                </div>
            </form>
        </div>
    </article>
</div>