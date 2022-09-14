<?php
$AdminLevel = LEVEL_WC_REPORTS;
if (!APP_EAD || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

$Search = filter_input_array(INPUT_POST);

//GET DATES
$StartDate = (!empty($_SESSION['wc_report_date'][0]) ? $_SESSION['wc_report_date'][0] : date("Y-m-01"));
$EndDate = (!empty($_SESSION['wc_report_date'][1]) ? $_SESSION['wc_report_date'][1] : date("Y-m-d"));

//DEFAULT REPORT
$DateStart = new DateTime($StartDate);
$DateEnd = new DateTime(date("Y-m-d", strtotime($EndDate . "+1day")));
$DateInt = new DateInterval("P1D");
$DateInterval = new DatePeriod($DateStart, $DateInt, $DateEnd);
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-user-plus">Relatório de Alunos</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/report">Relatórios</a>
            <span class="crumb">/</span>
            Relatório de Alunos
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Recarregar Relatórios" href="dashboard.php?wc=teach/report_students" class="btn btn_blue icon-spinner11 icon-notext"></a>
    </div>
</header>
<div class="dashboard_content">
    <article class="box box100">
        <div class="panel">
            <div class="wc_ead_chart_control">
                <div class="wc_ead_chart_range">
                    <form name="class_add" action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="callback" value="Reports"/>
                        <input type="hidden" name="callback_action" value="get_report"/>
                        <input type="hidden" name="report_back" value="teach/report_students"/>

                        <label class="wc_ead_chart_range_picker">
                            <span>DE:</span><input readonly="readonly" value="<?= date("d/m/Y", strtotime($StartDate)); ?>" name="start_date" type="text" data-language="pt-BR" class="jwc_datepicker_start"/>
                        </label><label class="wc_ead_chart_range_picker">
                            <span>ATÉ:</span><input readonly="readonly" value="<?= date("d/m/Y", strtotime($EndDate)); ?>" name="end_date" type="text" data-language="pt-BR" class="jwc_datepicker_end"/>
                        </label><button class="btn icon-spinner11 icon-notext"></button><img class="form_load" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                    </form>
                </div><div class="wc_ead_chart_change">
                    <span class="icon icon-stats-bars icon-notext jwc_chart_change jwc_area_chart btn btn_blue btn_green"></span>
                    <span class="icon icon-stats-bars2 icon-notext jwc_chart_change jwc_column_chart btn btn_blue"></span>
                    <span class="icon icon-stats-dots icon-notext jwc_chart_change jwc_line_chart btn btn_blue"></span>
                </div>
            </div>
            <div id="jwc_chart_container"></div>


            <?php
            //GET TOTALS
            $Read->FullRead("SELECT count(user_id) as TotalUsers FROM " . DB_USERS . " WHERE date(user_registration) >= :start AND date(user_registration) <= :end", "start={$StartDate}&end={$EndDate}");
            $TotalUsers = str_pad($Read->getResult()[0]['TotalUsers'], 3, 0, 0);

            $Read->FullRead("SELECT count(enrollment_id) as TotalEnroll FROM " . DB_EAD_ENROLLMENTS . " WHERE date(enrollment_start) >= :start AND date(enrollment_start) <= :end AND enrollment_bonus IS NULL", "start={$StartDate}&end={$EndDate}");
            $TotalEnroll = str_pad($Read->getResult()[0]['TotalEnroll'], 3, 0, 0);

            $TotalConversion = ($TotalUsers >= 1 ? round(($TotalEnroll * 100) / $TotalUsers) : "0");
            ?>
            <div class="wc_ead_reports_boxes">
                <div class="box box33 wc_ead_reports_total">
                    <div class="box_content">
                        <p class="icon-user-plus"><?= $TotalUsers; ?></p>
                        <span>Todos os Cadastros</span>
                    </div>
                </div><div class="box box33 wc_ead_reports_total">
                    <div class="box_content">
                        <p class="icon-lab"><?= $TotalEnroll; ?></p>
                        <span>Todas as Matrículas</span>
                    </div>
                </div><div class="box box33 wc_ead_reports_total">
                    <div class="box_content">
                        <p class="icon-filter"><?= $TotalConversion; ?>%</p>
                        <span>Taxa de Conversão</span>
                    </div>
                </div>
            </div>

            <footer class="wc_ead_reports">
                <?php
                $getPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
                $Page = ($getPage ? $getPage : 1);
                $Pager = new Pager("dashboard.php?wc=teach/report_students&page=", "<", ">", 3);
                $Pager->ExePager($Page, 12);
                $Read->FullRead("SELECT Year(user_registration) as UserYear, Month(user_registration) as UserMonth, count(user_id) as TotalUser FROM " . DB_USERS . " GROUP BY DATE_FORMAT(user_registration,'%Y-%m') ORDER BY user_registration DESC LIMIT :limit OFFSET :offset", "limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
                if (!$Read->getResult()):
                    $Pager->ReturnPage();
                else:
                    foreach ($Read->getResult() as $UserReport):
                        //ENROLLMENTS
                        $Read->FullRead("SELECT count(enrollment_id) AS Enroll FROM " . DB_EAD_ENROLLMENTS . " WHERE Year(enrollment_start) = :year AND Month(enrollment_start) = :month AND enrollment_bonus IS NULL", "year={$UserReport['UserYear']}&month={$UserReport['UserMonth']}");
                        $UserEnrollments = $Read->getResult()[0]['Enroll'];
                        ?>
                        <article class="wc_ead_reports_single">
                            <h1 class="row icon-calendar">
                                <?= str_pad($UserReport['UserMonth'], 2, 0, 0) . "/{$UserReport['UserYear']}"; ?>
                            </h1><p class="row icon-user-plus">
                                <?= str_pad($UserReport['TotalUser'], 3, 0, 0); ?> cadastros
                            </p><p class="row icon-lab">
                                <?= str_pad($UserEnrollments, 3, 0, 0); ?> matrículas
                            </p><p class="row icon-filter">
                                <?= ($UserReport['TotalUser'] && $UserEnrollments ? round(($UserEnrollments * 100) / $UserReport['TotalUser']) : "0"); ?>% Conversão
                            </p>
                        </article>
                        <?php
                    endforeach;
                endif;
                ?>
            </footer>
        </div>
        <?php
        $Pager->ExePaginator(DB_USERS, "GROUP BY DATE_FORMAT(user_registration,'%Y-%m')");
        echo $Pager->getPaginator();
        ?>
    </article>
</div>

<?php
$getDayChart = array();
$getUserChart = array();
$getEnrollChart = array();
foreach ($DateInterval as $setDayChart):
    //GET DAYS
    $getDayChart[] = "'" . $setDayChart->format('d/m/Y') . "'";

    //GET DAY FOR READ
    $ReadDay = $setDayChart->format('Y-m-d');

    //GET USERS
    $Read->FullRead("SELECT count(user_id) as UserChart FROM " . DB_USERS . " WHERE date(user_registration) = :date", "date={$ReadDay}");
    $getUserChart[] = $Read->getResult()[0]['UserChart'];

    //GET ENROLLMENTS
    $Read->FullRead("SELECT count(enrollment_id) as EnrollChart FROM " . DB_EAD_ENROLLMENTS . " WHERE date(enrollment_start) = :date AND enrollment_bonus IS NULL", "date={$ReadDay}");
    $getEnrollChart[] = $Read->getResult()[0]['EnrollChart'];
endforeach;

$DaysChart = implode(", ", $getDayChart);
$UserChart = implode(", ", $getUserChart);
$EnrollChart = implode(", ", $getEnrollChart);

unset($_SESSION['wc_report_date']);
?>

<script>
    $(function () {
        //DATEPICKER CONFIG
        var wc_datepicker_start = $('.jwc_datepicker_start').datepicker({autoClose: true, maxDate: new Date()}).data('datepicker');
        var wc_datepicker_end = $('.jwc_datepicker_end').datepicker({autoClose: true, maxDate: new Date()}).data('datepicker');

        $('.jwc_datepicker_end').click(function () {
            var DateString = $('.jwc_datepicker_start').val().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
            wc_datepicker_end.update('minDate', new Date(DateString[3], DateString[2] - 1, DateString[1]));
            if (!$(this).val()) {
                $(this).val("<?= date("d/m/Y", strtotime($EndDate)); ?>");
            }
        });

        $('.jwc_datepicker_start').click(function () {
            var DateString = $('.jwc_datepicker_end').val().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
            wc_datepicker_start.update('maxDate', new Date(DateString[3], DateString[2] - 1, DateString[1]));
            if (!$(this).val()) {
                $(this).val("<?= date("d/m/Y", strtotime($StartDate)); ?>");
            }
        });

        //CHART CONFIG
        var wc_chart = Highcharts.chart('jwc_chart_container', {
            chart: {
                type: 'area',
                spacingBottom: 0,
                spacingTop: 5,
                spacingLeft: 0,
                spacingRight: 20
            },
            title: {
                text: null
            },
            subtitle: {
                text: null
            },
            yAxis: {
                allowDecimals: false,
                title: {
                    text: 'Registros'
                }
            },
            tooltip: {
                useHTML: true,
                shadow: false,
                headerFormat: '<p class="al_center">{point.key}</p><p class="al_center" style="font-size: 2em">{point.y}</p>',
                pointFormat: '<p class="al_center">{series.name}</p><p class="al_center"></p>',
                backgroundColor: '#000',
                borderWidth: 0,
                padding: 20,
                style: {
                    padding: 20,
                    color: '#fff'
                }
            },
            xAxis: {
                categories: [<?= $DaysChart; ?>],
                minTickInterval: 7
            },
            series: [
                {
                    name: 'Cadastros',
                    data: [<?= $UserChart; ?>],
                    color: '#0E96E5 ',
                    lineColor: '#006699'
                },
                {
                    name: 'Matrículas',
                    data: [<?= $EnrollChart; ?>],
                    color: '#00B494',
                    lineColor: '#008068'
                }
            ]
        });

        //CHART CHANGE
        $('.jwc_chart_change').click(function () {
            $('.jwc_chart_change').removeClass('btn_green');
            $(this).addClass('btn_green');
        });

        $('.jwc_area_chart').click(function () {
            wc_chart.update({
                chart: {
                    type: 'area'
                }
            });
        });

        $('.jwc_column_chart').click(function () {
            wc_chart.update({
                chart: {
                    type: 'column'
                }
            });
        });

        $('.jwc_line_chart').click(function () {
            wc_chart.update({
                chart: {
                    type: 'line'
                }
            });
        });
    });
</script>