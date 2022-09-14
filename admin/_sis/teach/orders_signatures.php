<?php
$AdminLevel = LEVEL_WC_EAD_ORDERS;
if (!APP_EAD || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

$OrderClause = filter_input(INPUT_GET, 's', FILTER_DEFAULT);
$WhereCourse = null;
if ($OrderClause):
    $WhereCourse = "AND (order_id = '{$OrderClause}' OR order_transaction = '{$OrderClause}')";
elseif (!empty($OrdersEadStatus)):
    $WhereCourse = "AND order_status = '{$OrdersEadStatus}'";
endif;

$Search = filter_input_array(INPUT_POST);
if ($Search && ($Search['s'] || isset($Search['status']))):
    $S = urlencode($Search['s']);
    $Status = (!empty($Search['status']) ? $Search['status'] : '');
    header("Location: dashboard.php?wc=teach/orders_signatures&status={$Status}&s={$S}");
    exit;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-rss">Assinaturas</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <?= ($OrdersEadStatus ? "Assinaturas com status " . getWcHotmartStatus($OrdersEadStatus) : 'Assinaturas'); ?>
        </p>
    </div>

    <div class = "dashboard_header_search">
        <form style="width: 80%; display: inline-block" name="searchOrders" action="" method="post" enctype="multipart/form-data" class="ajax_off">
            <input type="text" name="s" placeholder="ID:" style="width: 25%; margin-right: 3px;"/>
            <select name="status" style="width: 55%; margin-right: 3px; padding: 5px 10px">
                <option value="">Todos</option>
                <?php
                foreach (getWcHotmartStatus() as $Key => $Status):
                    echo "<option " . ($OrdersEadStatus == $Key ? "selected='selected'" : '') . " value='{$Key}'>{$Status}</option>";
                endforeach;
                ?>
            </select>
            <button class="btn btn_blue icon icon-search icon-notext"></button>
        </form>
        <a title = "Recarregar Pedidos" href = "dashboard.php?wc=teach/orders_signatures&status=<?= $OrdersEadStatus; ?>" class="btn btn_green icon-spinner11 icon-notext"></a>
    </div>
</header>

<div class="dashboard_content">
    
    <div class="panel_header default">
        <h2 class="icon_rss">Gerencie suas assinaturas</h2>
    </div>
    <div class="panel">
        <?php
        $getPage = (filter_input(INPUT_GET, 'page'));
        $Page = ($getPage ? $getPage : 1);
        $Pager = new Pager("dashboard.php?wc=teach/orders_signatures&status={$OrdersEadStatus}&page=", "<", ">", 3);
        $Pager->ExePager($Page, 15);

        $Read->ExeRead(DB_EAD_ORDERS, "WHERE order_signature IS NOT NULL {$WhereCourse} ORDER BY order_purchase_date DESC LIMIT :limit OFFSET :offset", "limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
        if (!$Read->getResult()):
            $Pager->ReturnPage();
            echo "<div class='trigger trigger_info trigger_none icon-info al_center'>Ainda não existem assinaturas!</div><div class='clear'></div>";
        else:
            echo "<div class='student_gerent_orders_detail'>
                <div class='student_gerent_orders_detail_content'>
                    <div class='j_order_detail'></div>
                    <p class='close'><span class='icon icon-cross icon-notext btn btn_red order_close j_student_order_close student_gerent_orders_detail_content_close'></span></p>
                </div></div>";

            foreach ($Read->getResult() as $EadOrder):
                extract($EadOrder);
                $order_currency = ($order_currency ? $order_currency : "BRL");

                $Read->LinkResult(DB_USERS, "user_id", $user_id, 'user_id, user_name, user_lastname, user_email');
                $OrderUser = $Read->getResult()[0];

                $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = :course", "course={$course_id}");
                $CourseTitle = ($Read->getResult() ? "Curso {$Read->getResult()[0]['course_title']}" : "Produto #{$order_product_id} na Hotmart");
                ?><article class="wc_ead_orders_order">
                    <h1 class="row icon-cart">
                        #<a title="Gerenciar Pedido" href="dashboard.php?wc=teach/orders_gerent&id=<?= $order_id; ?>" class="a"><?= str_pad($order_id, 5, 0, 0); ?></a>
                        <span>(<?= $order_transaction; ?>)</span>
                    </h1><p class="row icon-user-plus">
                        <a title="Gerenciar Aluno" href="dashboard.php?wc=teach/students_gerent&id=<?= $OrderUser['user_id']; ?>" class="a"><?= "{$OrderUser['user_name']} {$OrderUser['user_lastname']}"; ?></a>
                        <span><?= $OrderUser['user_email']; ?></span>
                    </p><p class="row row_pay icon-calendar">
                        <?= date('d/m/Y H\hi', strtotime($order_purchase_date)); ?>
                        <span><?= ($CourseTitle ? $CourseTitle : "Produto {$order_product_id} na Hotmart"); ?></span>
                    </p><p class="row row_pay icon-coin-dollar">
                        <?= number_format($order_price, '2', ',', '.'); ?>&nbsp;&nbsp;(<?= $order_currency; ?>)&nbsp;&nbsp;&nbsp;<img style="display: inline-block; vertical-align: top;" width="18" src="<?= BASE; ?>/_cdn/bootcss/images/pay_<?= $order_payment_type; ?>.png" alt="" title=""/>
                        <span>Comissão de $ <?= number_format($order_cms_vendor, '2', ',', '.'); ?>&nbsp;&nbsp;(<?= $order_currency; ?>)</span>
                    </p><p class="row">
                        <span class="btn btn_<?= getWcHotmartStatusClass($order_status); ?> j_student_order_open" id="<?= $order_id; ?>"><?= getWcHotmartStatus($order_status); ?>
                        </span><a title="Gerenciar Pedido" href="dashboard.php?wc=teach/orders_gerent&id=<?= $order_id; ?>" class="btn btn_blue icon-pencil2 icon-notext"></a>
                    </p>
                </article><?php
            endforeach;

            $Pager->ExePaginator(DB_EAD_ORDERS, "WHERE order_signature IS NOT NULL {$WhereCourse}");
            echo $Pager->getPaginator();
            echo "<div class='clear'></div>";
        endif;
        ?>
    </div>
</div>