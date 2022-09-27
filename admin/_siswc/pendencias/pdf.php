<?php

$AdminLevel = LEVEL_UGQ_PENDENCY;
if (!APP_PENDENCY || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;
require_once("../_cdn/vendor/autoload.php");
if (empty($Read)):
    $Read = new Read;
endif;

$S = filter_input(INPUT_GET, "s", FILTER_DEFAULT);
$O = filter_input(INPUT_GET, "opt", FILTER_DEFAULT);
$D = filter_input(INPUT_GET, "d", FILTER_DEFAULT);

$WhereString = (!empty($S) ? " AND description LIKE '%{$S}%' " : "");
$WhereOpt = ((!empty($O)) ? " AND fgk_type_pendency = $O" : "");
$WhereDepartment = ((!empty($D)) ? " AND fgk_department = {$D}" : "");

$Search = filter_input_array(INPUT_POST);
if ($Search && (isset($Search['s']) || isset($Search['opt']) || isset($Search['d']) )):
    $S = urlencode($Search['s']);
    $O = urlencode($Search['opt']);
	$D = urlencode($Search['d']);
    header("Location: dashboard.php?wc=pendencias/pdf&opt={$O}&s={$S}&d={$D}"); 
    exit;
endif;

$stylesheet = file_get_contents('../admin/_css/workcontrol.css');

$mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
$html = "<div class='dashboard_content'>
<article class='project_dashboard box box100'>
    <table class='styled-table'>
        <thead>
            <th width='17%'>Setor</th>
            <th width='10%'>Categoria</th>
            <th width='6%'>Entrega</th>
            <th width='6%'>Devolução</th>
            <th width='8%'>Status</th>
            <th width='17%'>Responsável</th>
            <th width='31%'>Descrição</th>
        </thead>
        <tbody>";
$Read->FullRead("SELECT *, ". DB_PENDENCY.".id as pendency_id FROM ". DB_PENDENCY." LEFT JOIN ". DB_TYPE_PENDENCY ." ON ".DB_TYPE_PENDENCY.".id = fgk_type_pendency LEFT JOIN ". DB_DEPARTMENT ." ON ".DB_DEPARTMENT.".id = fgk_department WHERE 1=1  $WhereString $WhereOpt $WhereDepartment ORDER BY date_limit ASC");
    if ($Read->getResult()):

        foreach ($Read->getResult() as $Pendency):
            extract($Pendency);
			($status == 0 && ($date_limit < date('Y-m-d')) ? $status= "Atrasado" : $status= "No prazo");
			$date_delivery = date('d/m/Y', strtotime($date_delivery));
			$date_limit = date('d/m/Y', strtotime($date_limit));
            if(strripos($responsible, ",")):
                
                $colaboradores = "";
                $user = explode(",", $responsible); 
                foreach ($user as $users):
                    $colaboradores=$colaboradores."".getNameUser(intval($users))." / ";
                endforeach;
            else:
                $colaboradores=getNameUser($responsible);   
            endif;
            $html = $html."
           
                    <tr>
                    <td> <a href='dashboard.php?wc=projetos/create&id={$pendency_id}'>{$department}</a></td>
                    <td> {$type_pendency}</td>
					<td> {$date_delivery}</td>
					<td> {$date_limit}</td>
					<td> {$status}</td>
					<td> {$colaboradores}</td>
					<td> <a href='dashboard.php?wc=projetos/create&id={$pendency_id}'>{$description}</a></td>
                    </tr>
                    
                ";
        endforeach;
       
    endif;
    $html = $html."
            </tbody>
        </table>
    </article>";
$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output();
