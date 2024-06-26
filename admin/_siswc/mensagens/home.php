<?php
$AdminLevel = LEVEL_UGQ_MESSAGES;
if (!APP_MESSAGES || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

//AUTO DELETE USER TRASH
if (DB_AUTO_TRASH):
    $Delete = new Delete;
    $Delete->ExeDelete(DB_MESSAGES, "WHERE description = :d", "d=Nova Pendência");
endif;
if (empty($Read)):
    $Read = new Read;
endif;
if (empty($Read2)):
    $Read2 = new Read;
endif;

$S = filter_input(INPUT_GET, "s", FILTER_DEFAULT);
$O = filter_input(INPUT_GET, "opt", FILTER_DEFAULT);
$D = filter_input(INPUT_GET, "d", FILTER_DEFAULT);

$WhereString = (!empty($S) ? " AND description LIKE '%{$S}%' " : "");
$WhereOpt = ((!empty($O)) ? " AND fgk_type_pendency = $O" : "");
$WhereDepartment = '';
if(isset($D)):
    if(mb_strpos($D, ',')!== false):
       $array = explode(',', $D);
       $i=0;
       $len = count($array);
       foreach($array as $dept):
        if($i==0):
            $WhereDepartment .=  " AND fgk_department = {$dept}" ;
        else:
            $WhereDepartment .=  " OR fgk_department = {$dept}" ;
        endif;
        $i++;
       endforeach;
    else:
        $WhereDepartment .=  " AND fgk_department = {$D}" ;
    endif;
endif;

(isset($_POST['d']) ? $R = implode(",", (array) $_POST['d']) : "");

$Search = filter_input_array(INPUT_POST);
if ($Search && (isset($Search['s']) || isset($Search['opt']) || isset($Search['d']) )):
    $S = urlencode($Search['s']);
    $O = urlencode($Search['opt']);
	$D = urlencode($Search['d']);
    header("Location: dashboard.php?wc=pendencias/home&opt={$O}&s={$S}&d={$R}"); 
    exit;
endif;

?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
.icon-checkmark:before, .icon-pencil:before {
    margin-right: 0;
}
</style>

<header class="dashboard_header">
<a class='btn btn_blue icon-plus' href='dashboard.php?wc=pendencias/create' style="float:right;" title='Nova Pendência!'>Nova Pendência</a>
<a class='btn btn_red icon-file-pdf' target='_blank' href='<?="dashboard.php?wc=pendencias/pdf&or=l&opt={$O}&s={$S}&d={$D}";?>' style="float:right; margin-right:20px" title='Gerar PDF'>PDF Completo</a>
<a class='btn btn_red icon-file-pdf' target='_blank' href='<?="dashboard.php?wc=pendencias/pdf&or=p&opt={$O}&s={$S}&d={$D}";?>' style="float:right; margin-right:20px" title='Gerar PDF'>PDF Área Técnica</a>

    <div class="dashboard_header_title">

        <h1 class="icon-calendar">Pendências com a UGQ</h1>

        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            Pendências com a UGQ
        </p>

    </div>
    <div class="dashboard_header_search">
    <form name="searchPendency" action="" method="post" enctype="multipart/form-data" class="ajax_off">

            <input type="search" value="<?= $S; ?>" name="s" placeholder="Pesquisar:" style="width: 15%; margin-right: 3px;" />
			<select name="d[]" class="multiple" style="width: 40%; margin-right: 3px; padding: 5px" multiple="multiple">
				<?php
				$Read->FullRead("SELECT id, department FROM ugq_department WHERE is_active = :a ORDER BY department ASC;", "a=1");
				if ($Read->getResult()):
					foreach ($Read->getResult() as $ugq_department):
					    ($D === $ugq_department['id'] ? $select="selected=selected": $select=""); 
						echo "<option value={$ugq_department['id']} {$select}>{$ugq_department['department']}</option>";
					endforeach;
				endif;
				?>

            </select>
			<select name="opt" style="width: 15%; margin-right: 3px; padding: 5px">
                <option value="">Todas categorias</option>
				<?php
				$Read->FullRead("SELECT id, type_pendency FROM ugq_type_pendency  ORDER BY type_pendency ASC;");
				if ($Read->getResult()):
				
					foreach ($Read->getResult() as $ugq_type_pendency):
					($O === $ugq_type_pendency['id'] ? $select="selected=selected": $select=""); 
						echo "<option value={$ugq_type_pendency['id']} {$select}>{$ugq_type_pendency['type_pendency']}</option>";
					endforeach;
				endif;
				?>

            </select>
			
            <button class="btn btn_green icon icon-search icon-notext"></button>
        </form>
    </div>
</header>

<div class='dashboard_content'>
    <article class='project_dashboard box box100'>
        <table class='styled-table'>
            <thead>
                <th width='17%'>Setor</th>
                <th width='11%'>Categoria</th>
				<th width='6%'>Entrega</th>
				<th width='6%'>Devolução</th>
				<th width='8%'>Status</th>
				<th width='13%'>Responsável</th>
				<th width='30%'>Descrição</th>
                <th width='9%'>Ações</th>
            </thead>
            <tbody>
    <?php
    $getPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
    $Page = ($getPage ? $getPage : 1);
    $Pager = new Pager("dashboard.php?wc=pendencias/home&opt={$O}&s={$S}&d={$D}&page=", "<<", ">>", 5);
    $Pager->ExePager($Page, 150);
    $Read->FullRead("SELECT *, ". DB_MESSAGES.".id as pendency_id FROM ". DB_MESSAGES." LEFT JOIN ". DB_TYPE_MESSAGES ." ON ".DB_TYPE_MESSAGES.".id = fgk_type_pendency LEFT JOIN ". DB_DEPARTMENT ." ON ".DB_DEPARTMENT.".id = fgk_department WHERE 1=1  $WhereString $WhereOpt $WhereDepartment ORDER BY date_limit ASC LIMIT :limit OFFSET :offset", "limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");

    if (!$Read->getResult()):
        $Pager->ReturnPage();
        echo Erro("<span class='al_center icon-notification'>Ainda não existem pendências cadastradas com esses parâmetros, {$Admin['user_name']}. Comece agora mesmo cadastrando uma nova pendência!</span>", E_USER_NOTICE);
    else:
        foreach ($Read->getResult() as $Pendency):
            extract($Pendency);
			($status == 0 && ($date_limit < date('Y-m-d')) ? $status = "Atrasado" : ($status == 1 ? $status= "Concluído" : $status="No prazo"));
			$date_delivery = date('d/m/Y', strtotime($date_delivery));
			$date_limit = date('d/m/Y', strtotime($date_limit));
            if(strripos($responsible, ",")): 
                $colaboradores = "";
                $user = explode(",", $responsible); 
                foreach ($user as $users):
   
                    $separador = (end($user) == $users ? "" :", ");
                    $colaboradores=$colaboradores."".getfirstNameUser(intval($users))."".$separador;
                endforeach;
            else:
                $colaboradores=getfirstNameUser($responsible);   
            endif;
            echo "
           
                    <tr>
                    <td> {$department}</td>
                    <td> {$type_pendency}</td>
					<td> {$date_delivery}</td>
					<td> {$date_limit}</td>
					<td> {$status}</td>
					<td> {$colaboradores}</td>
					<td>{$description}</td>
                    <td> <a href='dashboard.php?wc=pendencias/create&id={$pendency_id}' class='btn btn_red'><span class='icon-pencil'></span></a><a href='dashboard.php?wc=pendencias/create&id={$pendency_id}' class='btn btn_green' style='margin-left:5px'><span class='icon-checkmark'></span></a></td>
                    </tr>
                    
                ";
        endforeach;
       
    endif;
    ?>
            </tbody>
        </table>
    </article>
    <?php
     $Pager->ExePaginator(DB_MESSAGES);
        echo $Pager->getPaginator();
    ?>
</div>
<script>
$(function() {
  $('.multiple').select2({
  placeholder: "Selecione um setor",
  allowClear: true
  });
  $(".styled-table").tablesorter({
    dateFormat : "ddmmyyyy", // set the default date format
    headers: {
      2: { sorter: "shortDate" },
      3: { sorter: "shortDate" } 
    }
  });
});
<?php
if (isset($D)):
    echo "$('.multiple').val([{$D}]);";
endif;
?>
</script>