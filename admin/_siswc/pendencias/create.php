<?php
$AdminLevel = LEVEL_UGQ_PENDENCY;
if (!APP_PENDENCY || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

// AUTO INSTANCE OBJECT CREATE
if (empty($Create)):
    $Create = new Create;
endif;

$PendencyId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($PendencyId):
    $Read->FullRead("SELECT *, ". DB_PENDENCY.".pendency_id FROM ". DB_PENDENCY." LEFT JOIN ". DB_TYPE_PENDENCY ." ON ".DB_TYPE_PENDENCY.".id = fgk_type_pendency LEFT JOIN ". DB_DEPARTMENT ." ON ".DB_DEPARTMENT.".id = fgk_department WHERE pendency_id = :id", "id={$PendencyId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);
    else:
        $_SESSION['trigger_controll'] = Erro("<b>OPPSS {$Admin['user_name']}</b>, você tentou editar uma pendência que não existe ou que foi removida recentemente!", E_USER_NOTICE);
        //header('Location: dashboard.php?wc=pendencias/home');
        exit;
    endif;
else:
    $PendencyCreate = ['created_at' => date('Y-m-d H:i:s'), 'status' => 0];
    $Create->ExeCreate(DB_PENDENCY, $PendencyCreate);
    header('Location: dashboard.php?wc=pendencias/create&id=' . $Create->getResult());
    exit;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-calendar"><?= $type_pendency ? $type_pendency : 'Nova Pendência'; ?></h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=pendencias/home">Pendências</a>
            <span class="crumb">/</span>
            Gerenciar Pendências
        </p>
    </div>

    <div class="dashboard_header_search">
        <a title="Deletar Pendência" href="#" class="wc_view btn btn_red icon-cancel">Deletar Pendência!</a>
    </div>
</header>


<div class="dashboard_content">

    <form class="auto_save" name="pendency_add" action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="callback" value="Pendencys"/>
        <input type="hidden" name="callback_action" value="manage"/>
        <input type="hidden" name="pendency_id" value="<?= $PendencyId; ?>"/>

        <div class="box box100">

            <div class="panel_header default">
                <h2 class="icon-calendar">Insira as informações da Pendência</h2>
            </div>

            <div class="panel">
                <div class="label_50">
                    <label class="label">
                        <span class="legend">Setor:</span>
                        <select name="fgk_department" id="department" required>
                        <option>Selecione o setor</option>
                        <?php
                        $Read->FullRead("SELECT id, department FROM ugq_department ORDER BY department ASC;");
                        if ($Read->getResult()):
                            foreach ($Read->getResult() as $ugq_department):
                            ($user_department === $ugq_department['id'] ? $select="selected=selected": $select=""); 
                                echo "<option value={$ugq_department['id']} {$select} >{$ugq_department['department']}</option>";
                            endforeach;
                        endif;
                        ?>
                        </select>
                    </label>

                    <label class="label">
                        <span class="legend">Categoria:</span>
                        <select name="user_section">
                            <option>Selecione a categoria</option>
                        <?php
                        $Read->FullRead("SELECT id, type_pendency FROM ugq_type_pendency ORDER BY type_pendency ASC;");
                        if ($Read->getResult()):
                            foreach ($Read->getResult() as $ugq_section):
                            ($user_section === $ugq_section['id'] ? $select="selected=selected": $select=""); 
                                echo "<option value={$ugq_section['id']} {$select} >{$ugq_section['type_pendency']}</option>";
                            endforeach;
                        endif;
                        ?>
                        </select>
                    </label>
                </div>
            <div class="label_50">

                <label class="label">
                    <span class="legend">Data da Entrega:</span>
                    <input value="<?= $date_delivery; ?>" type="date" name="date_delivery"   />

                </label>

                <label class="label">
                    <span class="legend">Entregue para:</span>
                    <select name="responsible">
                            <option>Selecione o responsável</option>
                        <?php
                        $Read->FullRead("SELECT id, type_pendency FROM ugq_type_pendency ORDER BY type_pendency ASC;");
                        if ($Read->getResult()):
                            foreach ($Read->getResult() as $ugq_section):
                            ($user_section === $ugq_section['id'] ? $select="selected=selected": $select=""); 
                                echo "<option value={$ugq_section['id']} {$select} >{$ugq_section['type_pendency']}</option>";
                            endforeach;
                        endif;
                        ?>
                        </select>
                </label>
            </div>
            <label class="label">
                <span class="legend">Descrição:</span>
                <textarea name="page_content" rows="10" placeholder="Descrição da Pendência:"><?= $description; ?></textarea>
            </div>
            <div class="clear"></div>
            
        </div>

        
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $('#department').change(function(){
            $('#users').load('users.php?department='+$('#department').val());
        });
    });
    </script>