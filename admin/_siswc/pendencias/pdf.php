<?php
ob_end_clean();
require_once __DIR__ . '/../../../_cdn/vendor/autoload.php';
$encoding = 'UTF-8';
$html = '
<html><head>
<style>

table {
	border: 0.4mm solid black;
	border-collapse: collapse;
   
}

td {
	border: 0.2mm solid black;
	vertical-align: middle;
    padding:10px 5px;  
}
tr:nth-child(even) {background: #e9e9e9}
tr:nth-child(odd) {background: #FFF}

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


       
$Read->FullRead("SELECT * FROM ". DB_PENDENCY." LEFT JOIN ". DB_TYPE_PENDENCY ." ON ".DB_TYPE_PENDENCY.".id = fgk_type_pendency LEFT JOIN ". DB_DEPARTMENT ." ON ".DB_DEPARTMENT.".id = fgk_department WHERE ".DB_PENDENCY.".status = 0  $WhereString $WhereOpt $WhereDepartment ORDER BY date_limit ASC");
    if ($Read->getResult()):
        foreach ($Read->getResult() as $sector):
            $sector = $sector['department'];
        endforeach;
        $htmlP = $html."
<h4 style='text-align:center'>Pendências com a UGQ em ".date('d/m/Y')." a ".date('d/m/Y', strtotime(' +15 days'))."- Setor: ".mb_strtoupper($sector, $enconding)."</h4>
    <table>
        <tr>
            <td width='30%'><strong>Categoria</strong></th>
            <td width='11%'><strong>Devolver até</strong></th>
            <td width='9%'><strong>Situação</strong></th>
            <td width='10%'><strong>Responsável</strong></th>
            <td width='40%'><strong>Descrição da Pendência</strong></th>
        </tr>";

$html = $html."
<h4 style='text-align:center'>Pendências com a UGQ em ".date('d/m/Y')." a ".date('d/m/Y', strtotime(' +15 days'))."</h4>
    <table>
        <tr>
            <td width='10%'><strong>Setor</strong></th>
            <td width='18%'><strong>Categoria</strong></th>
            <td width='7%'><strong>Entregue em:</strong></th>
            <td width='7%'><strong>Devolver até:</strong></th>
            <td width='5%'><strong>Situação</strong></th>
            <td width='12%'><strong>Responsável</strong></th>
            <td width='41%'><strong>Descrição da Pendência</strong></th>
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
   
                    $separador = (end($user) == $users ? "" :", ");
                    $colaboradores=$colaboradores."".getfirstNameUser(intval($users))."".$separador;
                endforeach;
            else:
                $colaboradores=getfirstNameUser($responsible);   
            endif;
            $html = $html."

                    <tr>
                    <td> ".mb_strtoupper($department, $encoding)."</td>
                    <td> ".mb_strtoupper($type_pendency, $encoding)."</td>
					<td> {$date_delivery}</td>
					<td> {$date_limit}</td>
					<td> ".mb_strtoupper($status, $encoding)."</td>
					<td> ".mb_strtoupper($colaboradores, $encoding)."</td>
					<td> ".mb_strtoupper($description, $encoding)."</td>
                    </tr>
                    
                ";
            $htmlP = $htmlP."
                    <tr>
                    <td> ".mb_strtoupper($type_pendency, $encoding)."</td>
					<td> {$date_limit}</td>
					<td> ".mb_strtoupper($status, $encoding)."</td>
					<td> ".mb_strtoupper($colaboradores, $encoding)."</td>
					<td> ".mb_strtoupper($description, $encoding)."</td>
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
    'default_font_size' => 9,
	'default_font' => 'dejavusans',
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
