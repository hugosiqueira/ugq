<?php
$AdminLevel = LEVEL_UGQ_PENDENCY;
if (!APP_PENDENCY || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

//AUTO DELETE USER TRASH
if (DB_AUTO_TRASH):
    $Delete = new Delete;
    $Delete->ExeDelete(DB_PENDENCY, "WHERE description = :d", "d=Nova Pendência");
endif;
// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-pencil">Pendências com a UGQ</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            Pendências com a UGQ
        </p>
    </div>
    <div class="dashboard_header_search">
        <a class='btn btn_green icon-plus' href='dashboard.php?wc=pendencias/create' title='Nova Pendência!'>Nova Pendência</a>
    </div>
</header>

<div class="dashboard_content">
    <article class='project_dashboard box box100'>
        <table class='styled-table'>
            <thead>
                <th width='20%'>Setor</th>
                <th width='10%'>Categoria</th>
				<th width='10%'>Data de Entrega</th>
				<th width='10%'>Data para Devolução</th>
				<th width='10%'>Status</th>
				<th width='10%'>Responsável</th>
				<th width='27%'>Descrição</th>
                <th width='1%'></th>
                <th width='1%'></th>
            </thead>
            <tbody>
    <?php
    $getPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
    $Page = ($getPage ? $getPage : 1);
    $Pager = new Pager("dashboard.php?wc=pendencias/home&page=", "<<", ">>", 5);
    $Pager->ExePager($Page, 50);
    $Read->FullRead("SELECT *, ". DB_PENDENCY.".id as pendency_id FROM ". DB_PENDENCY." LEFT JOIN ". DB_TYPE_PENDENCY ." ON ".DB_TYPE_PENDENCY.".id = fgk_type_pendency LEFT JOIN ". DB_DEPARTMENT ." ON ".DB_DEPARTMENT.".id = fgk_department ORDER BY date_limit ASC LIMIT :limit OFFSET :offset", "limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
    if (!$Read->getResult()):
        $Pager->ReturnPage();
        echo Erro("<span class='al_center icon-notification'>Ainda não existem pendências cadastradas, {$Admin['user_name']}. Comece agora mesmo cadastrando uma nova pendência!</span>", E_USER_NOTICE);
    else:
        foreach ($Read->getResult() as $Pendency):
            extract($Pendency);
			($status == 0 && ($date_limit < date('Y-m-d')) ? $status= "Atrasado" : $status= "No prazo");
			$date_delivery = date('d/m/Y', strtotime($date_delivery));
			$date_limit = date('d/m/Y', strtotime($date_limit));
            echo "
                    
                    <tr>
                    <td>{$department}</td>
                    <td>{$type_pendency}</td>
					<td>{$date_delivery}</td>
					<td>{$date_limit}</td>
					<td>{$status}</td>
					<td>{$responsible}</td>
					<td>{$description}</td>
                    <td><a href='dashboard.php?wc=projetos/create&id={$pendency_id}' class=' btn btn_blue'> Editar</a></td>
                    <td><span callback='Project' callback_action='window_delete_project' data-id='{$pendency_id}'  class='j_ajaxModal btn btn_red'> Excluir</span></td>                  
                    </tr>
                    
                ";
        endforeach;
       
    endif;
    ?>
            </tbody>
        </table>
    </article>
    <?php
     $Pager->ExePaginator(DB_PENDENCY);
        echo $Pager->getPaginator();
    ?>
</div>
