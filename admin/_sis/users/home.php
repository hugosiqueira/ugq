<?php
$AdminLevel = LEVEL_WC_USERS;
if (!APP_USERS || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

//AUTO DELETE USER TRASH
if (DB_AUTO_TRASH):
    $Delete = new Delete;
    $Delete->ExeDelete(DB_USERS, "WHERE user_name IS NULL AND user_email IS NULL and user_password IS NULL and user_level = :st", "st=1");
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

$S = filter_input(INPUT_GET, "s", FILTER_DEFAULT);
$O = filter_input(INPUT_GET, "opt", FILTER_DEFAULT);
$D = filter_input(INPUT_GET, "d", FILTER_DEFAULT);
$R = filter_input(INPUT_GET, "r", FILTER_DEFAULT);
$I = filter_input(INPUT_GET, "i", FILTER_DEFAULT);

$WhereString = (!empty($S) ? " AND (user_name LIKE '%{$S}%' OR user_lastname LIKE '%{$S}%' OR concat(user_name, ' ', user_lastname) LIKE '%{$S}%' OR user_email LIKE '%{$S}%' OR user_id LIKE '%{$S}%' OR user_document LIKE '%{$S}%' )" : "");
$WhereOpt = ((!empty($O) && $O == 'ativos') ? " AND user_status = 1" : ((!empty($O) && $O == 'inativos') ? " AND user_status >= 2 " : ""));
$WhereDepartment = ((!empty($D)) ? " AND user_department = {$D}" : "");
$WhereRole = ((!empty($R)) ? " AND user_role = {$R}" :  "");
$WhereInst = ((!empty($I)) ? " AND user_employer LIKE '%{$I}%'" :  "");


$Search = filter_input_array(INPUT_POST);
if ($Search && (isset($Search['s']) || isset($Search['opt']) || isset($Search['r']) || isset($Search['d']) || isset($Search['i']))):
    $S = urlencode($Search['s']);
    $O = urlencode($Search['opt']);
	$D = urlencode($Search['d']);
	$R = urlencode($Search['r']);
	$I = urlencode($Search['i']);
	
    header("Location: dashboard.php?wc=users/home&opt={$O}&s={$S}&d={$D}&r={$R}&i={$I}"); 
    exit;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-users">Colaboradores</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=home">Dashboard</a>
            <span class="crumb">/</span>
            Usuários
        </p>
    </div>

    <div class="dashboard_header_search">
        <form name="searchUsers" action="" method="post" enctype="multipart/form-data" class="ajax_off">
            <input type="search" value="<?= $S; ?>" name="s" placeholder="Pesquisar:" style="width: 20%; margin-right: 3px;" />
			<select name="d" style="width: 20%; margin-right: 3px; padding: 5px">
                <option value="">Todos Setores</option>
				<?php
				$Read->FullRead("SELECT id, department FROM ugq_department WHERE is_active = :a ORDER BY department ASC;", "a=1");
				if ($Read->getResult()):
					foreach ($Read->getResult() as $ugq_department):
					($D === $ugq_department['id'] ? $select="selected=selected": $select=""); 
						echo "<option value={$ugq_department['id']} {$select} >{$ugq_department['department']}</option>";
					endforeach;
				endif;
				?>

            </select>
			<select name="r" style="width: 20%; margin-right: 3px; padding: 5px">
                <option value="">Todos Cargos</option>
				<?php
				$Read->FullRead("SELECT id, role FROM ugq_roles WHERE is_active = :a ORDER BY role ASC;", "a=1");
				if ($Read->getResult()):
				
					foreach ($Read->getResult() as $ugq_role):
					($R === $ugq_role['id'] ? $select="selected=selected": $select=""); 
						echo "<option value={$ugq_role['id']} {$select} >{$ugq_role['role']}</option>";
					endforeach;
				endif;
				?>

            </select>
			<select name="i" style="width: 15%; margin-right: 3px; padding: 5px">
				<option value="">Selecione a Instituição:</option>
				<option value="UNIFESP" <?= ($I == "UNIFESP" ? 'selected="selected"' : ''); ?>>UNIFESP</option>
				<option value="SPDM" <?= ($I == "SPDM" ? 'selected="selected"' : ''); ?>>SPDM</option>
			</select>
            <select name="opt" style="width: 15%; margin-right: 3px; padding: 5px">
                <option value="">Todos Funcionários</option>
                <option <?= ($O == 'ativos' ? "selected='selected'" : ''); ?> value="ativos">Ativos</option>
                <option <?= ($O == 'inativos' ? "selected='selected'" : ''); ?> value="inativos">Inativos</option>
            </select>
            <button class="btn btn_green icon icon-search icon-notext"></button>
        </form>
    </div>
</header>

<div class="dashboard_content">
    <?php
    $getPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
    $Page = ($getPage ? $getPage : 1);
    $Pager = new Pager("dashboard.php?wc=users/home&opt={$O}&s={$S}&d={$D}&r={$R}&i={$I}&page=", "<<", ">>", 10);
    $Pager->ExePager($Page, 20);
	$Read->ExeRead(DB_USERS, "WHERE 1 = 1 $WhereString $WhereOpt $WhereDepartment $WhereRole $WhereInst ORDER BY user_name ASC ");
	$total = $Read->getRowCount();
    $Read->ExeRead(DB_USERS, "WHERE 1 = 1 $WhereString $WhereOpt $WhereDepartment $WhereRole $WhereInst ORDER BY user_name ASC LIMIT :limit OFFSET :offset", "limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
    
	if (!$Read->getResult()):
        $Pager->ReturnPage();
        echo Erro("<span class='al_center icon-notification'>Ainda não existem usuários cadastrados {$Admin['user_name']}. Comece agora mesmo cadastrando um novo usuário. Ou aguarde novos clientes!</span>", E_USER_NOTICE);
    else:
		
        foreach ($Read->getResult() as $Users):
            extract($Users);
            $user_name = ($user_name ? $user_name : 'Novo');
            //$user_lastname = ($user_lastname ? $user_lastname : 'Usuário');
            $UserThumb = "../uploads/{$user_thumb}";
            $user_thumb = (file_exists($UserThumb) && !is_dir($UserThumb) ? "uploads/{$user_thumb}" : 'admin/_img/no_avatar.jpg');
            echo "<article class='single_user box box25 al_center'>
                    <div class='box_content wc_normalize_height'>
                        <img alt='Este é {$user_name}' title='Este é {$user_name}' src='../tim.php?src={$user_thumb}&w=200&h=200'/>
                        <h1>{$user_name}</h1>
                        <p class='nivel'>" . getRole($user_role) . "<br>" . getDept($user_department) . "</p>
						
						<p class='info'>RF: $user_rf</p>
                        <p class='info icon-envelop'>{$user_email}</p>
                        <p class='info icon-calendar'>Último Periódico " . date('d/m/Y', strtotime($user_periodico)) . "</p>
                    </div>
                    <div class='single_user_actions'>
                        <a class='btn btn_green icon-user' href='dashboard.php?wc=users/create&id={$user_id}' title='Editar Colaborador!'>Editar Colaborador</a>
                    </div>
                </article>";
        endforeach;
        $Pager->ExePaginator(DB_USERS);
        echo $Pager->getPaginator();
		echo "<p style='text-align:center'>Total de colaboradores filtrados: {$total} colaboradores </p>";
    endif;
    ?>
</div>