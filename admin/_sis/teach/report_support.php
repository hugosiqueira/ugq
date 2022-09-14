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
        <h1 class="icon-bubbles4">Relatório de Suporte</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=teach/report">Relatórios</a>
            <span class="crumb">/</span>
            Relatório de Suporte
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Recarregar Relatórios" href="dashboard.php?wc=teach/report_support" class="btn btn_blue icon-spinner11 icon-notext"></a>
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
                        <input type="hidden" name="report_back" value="teach/report_support"/>

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
            $Read->FullRead("SELECT count(support_id) as TotalSupport FROM " . DB_EAD_SUPPORT . " WHERE date(support_open) >= :start AND date(support_open) <= :end", "start={$StartDate}&end={$EndDate}");
            $TotalSupport = str_pad($Read->getResult()[0]['TotalSupport'], 3, 0, 0);

            //GET CONCLUSIONS
            $Read->FullRead("SELECT count(support_id) as TotalReviewd FROM " . DB_EAD_SUPPORT . " WHERE support_review IS NOT NULL AND date(support_open) >= :start AND date(support_open) <= :end", "start={$StartDate}&end={$EndDate}");
            $TotalReviewd = str_pad($Read->getResult()[0]['TotalReviewd'], 3, 0, 0);

            //GET SLA
            $Read->FullRead("SELECT count(support_id) as TotalSLASupport, SUM(support_review) as TotalSLAReviews FROM " . DB_EAD_SUPPORT . " WHERE support_review IS NOT NULL AND date(support_open) >= :start AND date(support_open) <= :end", "start={$StartDate}&end={$EndDate}");
            $TotalSLASupport = $Read->getResult()[0]['TotalSLASupport'];
            $TotalSLAReviews = $Read->getResult()[0]['TotalSLAReviews'];

            $SupportSLA = ($TotalSLASupport >= 1 ? ($TotalSLAReviews * 100) / $TotalSLASupport : "0");
            $SupportPoints = number_format($SupportSLA / 100, 1, '.', '');
            ?>
            <div class="wc_ead_reports_boxes">
                <div class="box box33 wc_ead_reports_total">
                    <div class="box_content">
                        <p class="icon-bubble2"><?= $TotalSupport; ?></p>
                        <span>Total de Tickets</span>
                    </div>
                </div><div class="box box33 wc_ead_reports_total">
                    <div class="box_content">
                        <p class="icon-checkmark"><?= $TotalReviewd; ?></p>
                        <span>Tickets Avaliados</span>
                    </div>
                </div><div class="box box33 wc_ead_reports_total">
                    <div class="box_content">
                        <p class="icon-star-full"><?= $SupportPoints; ?></p>
                        <span>Média de Avaliações</span>
                    </div>
                </div>
            </div>

            <footer class="wc_ead_reports">
                <?php
                $getPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
                $Page = ($getPage ? $getPage : 1);
                $Pager = new Pager("dashboard.php?wc=teach/report_support&page=", "<", ">", 3);
                $Pager->ExePager($Page, 12);
                $Read->FullRead("SELECT count(support_id) as SupportAll, count(support_review = 1) as SupportReview, SUM(support_review) as TotalReview, Year(support_open) as SupportYear, Month(support_open) as SupportMonth FROM " . DB_EAD_SUPPORT . " GROUP BY DATE_FORMAT(support_open,'%Y-%m') ORDER BY support_open DESC LIMIT :limit OFFSET :offset", "limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
                if (!$Read->getResult()):
                    $Pager->ReturnPage();
                else:
                    foreach ($Read->getResult() as $SupportReport):
                        $SupportReSLA = ($SupportReport['SupportReview'] >= 1 ? ($SupportReport['TotalReview'] * 100) / $SupportReport['SupportReview'] : "0");
                        $SupportRePoints = number_format($SupportReSLA / 100, 1, '.', '');
                        ?>
                        <article class="wc_ead_reports_single">
                            <h1 class="row icon-calendar">
                                <?= str_pad($SupportReport['SupportMonth'], 2, 0, 0) . "/{$SupportReport['SupportYear']}"; ?>
                            </h1><p class="row icon-bubble2">
                                <?= str_pad($SupportReport['SupportAll'], 3, 0, 0); ?> Tickets
                            </p><p class="row icon-checkmark">
                                <?= str_pad($SupportReport['SupportReview'], 3, 0, 0); ?> Avaliações
                            </p><p class="row icon-star-full">
                                <?= $SupportRePoints; ?> Média
                            </p>
                        </article>
                        <?php
                    endforeach;
                endif;
                ?>
            </footer>
        </div>
        <?php
        $Pager->ExePaginator(DB_EAD_SUPPORT, "GROUP BY DATE_FORMAT(support_open,'%Y-%m')");
        echo $Pager->getPaginator();
        ?>
    </article>
</div>

<?php
$getDayChart = array();
$getSupportChart = array();
$getResponseChart = array();
foreach ($DateInterval as $setDayChart):
    //GET DAYS
    $getDayChart[] = "'" . $setDayChart->format('d/m/Y') . "'";

    //GET DAY FOR READ
    $ReadDay = $setDayChart->format('Y-m-d');

    //GET OPEN
    $Read->FullRead("SELECT count(support_id) as TotalOpen FROM " . DB_EAD_SUPPORT . " WHERE date(support_open) = :date", "date={$ReadDay}");
    $getSupportChart[] = $Read->getResult()[0]['TotalOpen'];

    //GET CONCLUSIONS
    $Read->FullRead("SELECT count(support_id) as TotalReply FROM " . DB_EAD_SUPPORT . " WHERE support_status > 1 AND date(support_open) = :date", "date={$ReadDay}");
    $getResponseChart[] = $Read->getResult()[0]['TotalReply'];

    //GET CONCLUSIONS
    $Read->FullRead("SELECT count(support_id) as TotalReviewd FROM " . DB_EAD_SUPPORT . " WHERE support_review IS NOT NULL AND date(support_open) = :date", "date={$ReadDay}");
    $getReviewdChart[] = $Read->getResult()[0]['TotalReviewd'];
endforeach;

$DaysChart = implode(", ", $getDayChart);
$SupportChart = implode(", ", $getSupportChart);
$ResponseChart = implode(", ", $getResponseChart);
$ReviewdChart = implode(", ", $getReviewdChart);

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
                    name: 'Total de Tickets',
                    data: [<?= $SupportChart; ?>],
                    color: '#FF9326',
                    lineColor: '#B25900'
                },
                {
                    name: 'Tickets Resolvidos',
                    data: [<?= $ResponseChart; ?>],
                    color: '#0E96E5',
                    lineColor: '#006699'
                },
                {
                    name: 'Total de Avaliados',
                    data: [<?= $ReviewdChart; ?>],
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