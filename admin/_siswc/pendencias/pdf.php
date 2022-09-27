<?php
ob_end_clean();
require_once __DIR__ . '/../../../_cdn/vendor/autoload.php';

$html = '
<html><head>
<style>
table {
	font-family: sans-serif;
	border: 0.7mm solid black;
	border-collapse: collapse;
   
}

td {
	border: 0.3mm solid black;
	vertical-align: middle;
    padding:10px;
    font-size: 1em;
}

</style>
</head>
<body>
';

if (empty($Read)):
    $Read = new Read;
endif;

$S = filter_input(INPUT_GET, "s", FILTER_DEFAULT);
$O = filter_input(INPUT_GET, "opt", FILTER_DEFAULT);
$D = filter_input(INPUT_GET, "d", FILTER_DEFAULT);
$OR = filter_input(INPUT_GET, "or", FILTER_DEFAULT);

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


       
$Read->FullRead("SELECT *, ". DB_PENDENCY.".id as pendency_id FROM ". DB_PENDENCY." LEFT JOIN ". DB_TYPE_PENDENCY ." ON ".DB_TYPE_PENDENCY.".id = fgk_type_pendency LEFT JOIN ". DB_DEPARTMENT ." ON ".DB_DEPARTMENT.".id = fgk_department WHERE 1=1  $WhereString $WhereOpt $WhereDepartment ORDER BY date_limit ASC");
    if ($Read->getResult()):
        foreach ($Read->getResult() as $sector):
            $sector = $sector['department'];
        endforeach;
        $htmlP = $html."
<h4 style='text-align:center'>Pêndências com a UGQ em ".date('d/m/Y')." a ".date('d/m/Y', strtotime(' +15 days'))."- Setor: ".$sector."</h4>
    <table>
        <tr>
            <td width='20%'><strong>Categoria</strong></th>
            <td width='10%'><strong>Devolver até:</strong></th>
            <td width='10%'><strong>Situação</strong></th>
            <td width='20%'><strong>Responsável</strong></th>
            <td width='40%'><strong>Descrição da Pendência</strong></th>
        </tr>";

$html = $html."
<h4 style='text-align:center'>Pêndências com a UGQ</h4>
    <table>
        <tr>
            <td width='15%'><strong>Setor</strong></th>
            <td width='20%'><strong>Categoria</strong></th>
            <td width='7.5%'><strong>Entregue em:</strong></th>
            <td width='7.5%'><strong>Devolver até:</strong></th>
            <td width='5%'><strong>Situação</strong></th>
            <td width='15%'><strong>Responsável</strong></th>
            <td width='30%'><strong>Descrição da Pendência</strong></th>
        </tr>";

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
                    <td> {$department}</td>
                    <td> {$type_pendency}</td>
					<td> {$date_delivery}</td>
					<td> {$date_limit}</td>
					<td> {$status}</td>
					<td> {$colaboradores}</td>
					<td>{$description}</td>
                    </tr>
                    
                ";
            $htmlP = $htmlP."
           
                    <tr>
                    <td> {$type_pendency}</td>
					<td> {$date_limit}</td>
					<td> {$status}</td>
					<td> {$colaboradores}</td>
					<td>{$description}</td>
                    </tr>
                    
                ";

        endforeach;
       
    endif;
    $html = $html."
        </table>
    </body>
    </html>";
    $htmlP = $htmlP."
    </table>
</body>
</html>";

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'orientation' => $OR
]);

$mpdf->SetDisplayMode('fullpage');
if($OR==='P' || $OR ==='p'):
    $mpdf->WriteHTML($htmlP);
else:
    $mpdf->WriteHTML($html);
endif;
$mpdf->Output();
exit;






/*

$mpdf = new \Mpdf\Mpdf(['debug' => true]);

$AdminLevel = LEVEL_UGQ_PENDENCY;
if (!APP_PENDENCY || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

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

$html = "

<div class='dashboard_content'>
<article class='project_dashboard box box100'>
    <table  style='border: 1px solid #880000; font-family: Mono; font-size: 7pt; '>
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
                    <td> {$department}</td>
                    <td> {$type_pendency}</td>
					<td> {$date_delivery}</td>
					<td> {$date_limit}</td>
					<td> {$status}</td>
					<td> {$colaboradores}</td>
					<td>{$description}</td>
                    </tr>
                    
                ";
        endforeach;
       
    endif;
    $html = $html."
            </tbody>
        </table>
    </article>";

$mpdf->WriteHTML($html);
$mpdf->SetDisplayMode('fullpage');
$mpdf->Output();
*/