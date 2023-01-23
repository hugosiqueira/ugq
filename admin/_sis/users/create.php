<?php
$AdminLevel = LEVEL_WC_USERS;
if (!APP_USERS || empty($DashboardLogin) || empty($Admin) || $Admin['user_level'] < $AdminLevel):
    die('<div style="text-align: center; margin: 5% 0; color: #C54550; font-size: 1.6em; font-weight: 400; background: #fff; float: left; width: 100%; padding: 30px 0;"><b>ACESSO NEGADO:</b> Você não esta logado<br>ou não tem permissão para acessar essa página!</div>');
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;

// AUTO INSTANCE OBJECT READ
if (empty($Create)):
    $Create = new Create;
endif;

$UserId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($UserId):
    $Read->ExeRead(DB_USERS, "WHERE user_id = :id", "id={$UserId}");
    if ($Read->getResult()):
        $FormData = array_map('htmlspecialchars', $Read->getResult()[0]);
        extract($FormData);

        if ($user_level > $_SESSION['userLogin']['user_level']):
            $_SESSION['trigger_controll'] = "<b>OPPSS {$Admin['user_name']}</b>. Por questões de segurança, é restrito o acesso a usuário com nível de acesso maior que o seu!";
            header('Location: dashboard.php?wc=users/home');
            exit;
        endif;
    else:
        $_SESSION['trigger_controll'] = "<b>OPPSS {$Admin['user_name']}</b>, você tentou editar um usurio que não existe ou que foi removido recentemente!";
        header('Location: dashboard.php?wc=users/home');
        exit;
    endif;
else:
    $CreateUserDefault = [
        "user_registration" => date('Y-m-d H:i:s'),
        "user_level" => 1
    ];
    $Create->ExeCreate(DB_USERS, $CreateUserDefault);
    header("Location: dashboard.php?wc=users/create&id={$Create->getResult()}");
    exit;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-user-plus">Novo Colaborador</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <?= ADMIN_NAME; ?>
            <span class="crumb">/</span>
            <a title="<?= ADMIN_NAME; ?>" href="dashboard.php?wc=users/home">Colaboradores</a>
            <span class="crumb">/</span>
            Novo Colaborador
        </p>
    </div>

    <div class="dashboard_header_search" style="font-size: 0.875em; margin-top: 16px;" id="<?= $UserId; ?>">
        <span rel='dashboard_header_search' class='j_delete_action icon-warning btn btn_red' id='<?= $UserId; ?>'>Deletar Colaborador!</span>
        <span rel='dashboard_header_search' callback='Users' callback_action='delete' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='<?= $UserId; ?>'>EXCLUIR AGORA!</span>
    </div>
</header>

<div class="dashboard_content dashboard_users">
    <div class="box box70">
        <article class="wc_tab_target wc_active" id="profile">

            <div class="panel_header default">
                <h2 class="icon-user-plus">COLABORADOR <?= strtoupper($user_name); ?></h2>
            </div>

            <div class="panel">
                <form class="auto_save" class="j_tab_home tab_create" name="user_manager" action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="callback" value="Users"/>
                    <input type="hidden" name="callback_action" value="manager"/>
                    <input type="hidden" name="user_id" value="<?= $UserId; ?>"/>
                    <label class="label">
                        <span class="legend">Nome Completo:</span>
                        <input value="<?= $user_name; ?>" type="text" name="user_name" placeholder="Nome Completo:" required />
                    </label>
                    <label class="label">
                        <span class="legend">Foto (<?= AVATAR_W; ?>x<?= AVATAR_H; ?>px, JPG ou PNG):</span>
                        <input type="file" name="user_thumb" class="wc_loadimage" />
                    </label>
                    <div class="clear"></div>
                    <h3 class="students_gerent_subtitle icon-user-tie m_botton">Dados Institucionais:</h3>
					<div class="label_50">
                        <label class="label">
                            <span class="legend">Registro Funcional - Unifesp/HSP:</span>
                            <input value="<?= $user_rf; ?>" type="text" name="user_rf"  placeholder="RF:" />
                        </label>

                        <label class="label">
                            <span class="legend">Siape:</span>
                            <input value="<?= $user_siape; ?>" type="text" name="user_siape" " placeholder="Siape:" />
                        </label>
                    </div>
					<div class="label_33">
						
						<label class="label">
							<span class="legend">Data de Nascimento:</span>
							<input value="<?= $user_datebirth; ?>" type="date" name="user_datebirth"  placeholder="Data de nascimento:" />
						</label>

                        <label class="label">
                            <span class="legend">RG:</span>
                            <input value="<?= $user_rg; ?>" type="text" name="user_rg"  placeholder="RG:" />
                        </label>
					
                        <label class="label">
                            <span class="legend">CPF:</span>
                            <input value="<?= $user_document; ?>" type="text" name="user_document" class="formCpf" placeholder="CPF:" />
                        </label>
                        
                    </div>
					<div class="label_50">
						
						<label class="label">
							<span class="legend">Escolaridade:</span>
							<select name="user_scholarity" required>
								<option selected disabled value="">Selecione a escolaridade:</option>
								<?php
								$Read->FullRead("SELECT * FROM ugq_scholarity ORDER BY scholarity ASC;");
								if ($Read->getResult()):
									foreach ($Read->getResult() as $ugq_scholarity):
									($user_scholarity === $ugq_scholarity['id'] ? $select="selected=selected": $select=""); 
										echo "<option value={$ugq_scholarity['id']} {$select}>{$ugq_scholarity['scholarity']}</option>";
									endforeach;
								endif;
								?>
							</select>
						</label>
						<label class="label">
							<span class="legend">Instituição de Ensino:</span>
							<input value="<?= $user_university; ?>" type="text" name="user_university" placeholder="Instituição de Ensino" />
						</label>
					</div>
					<div class="label_50">
						<label class="label">
							<span class="legend">Curso da Graduação/Técnico:</span>
							<input value="<?= $user_graduation; ?>" type="text" name="user_graduation" placeholder="Curso da Graduação/Técnico" />
						</label>
						<label class="label">
							<span class="legend">Ano que se formou:</span>
							<input value="<?= $user_year_graduation; ?>" type="number" name="user_year_graduation" placeholder="Ano que se formou" />
						</label>
					</div>
					<div class="label_50">
						<label class="label">
							<span class="legend">Conselho:</span>
							<input value="<?= $user_council; ?>" type="text" name="user_council" placeholder="Conselho da profissão" />
						</label>
						<label class="label">
							<span class="legend">E-mail Pessoal:</span>
							<input value="<?= $user_email; ?>" type="email" name="user_email" placeholder="E-mail:" />
						</label>
					</div>

                    <div class="label_50">
                        <label class="label">
                            <span class="legend">Telefone:</span>
                            <input value="<?= $user_telephone; ?>" class="formPhone" type="text" name="user_telephone" placeholder="(55) 5555.5555" />
                        </label>

                        <label class="label">
                            <span class="legend">Celular:</span>
                            <input value="<?= $user_cell; ?>" class="formPhone" type="text" name="user_cell" placeholder="(55) 5555.5555" />
                        </label>
                    </div>

                   
					<div class="clear"></div>
                    <h3 class="students_gerent_subtitle icon-user-tie m_botton">DADOS PROFISSIONAIS:</h3>
					
					<div class="label_50">
                         <label class="label">
                            <span class="legend">Data de Admisssão:</span>
                            <input value="<?= $user_admission; ?>" type="date" name="user_admission"  placeholder="Data de admissão:" />
                        </label>

                        
                        <label class="label">
                            <span class="legend">Instituição:</span>
							<select name="user_employer" required>
								<option selected disabled value="">Selecione a Instituição:</option>
								<option value="UNIFESP" <?= ($user_employer == "UNIFESP" ? 'selected="selected"' : ''); ?>>UNIFESP</option>
								<option value="SPDM" <?= ($user_employer == "SPDM" ? 'selected="selected"' : ''); ?>>SPDM</option>
							</select>
                        </label>
                    </div>
                    <div class="label_50">
                        <label class="label">
                            <span class="legend">Carga Horária:</span>
							<select name="user_hour">
								<option>Selecione a carga horária</option>
								<option value=30 <?=($user_hour == 30 ? "selected=selected" : "");?>>30h</option>
								<option value=40 <?=($user_hour == 40 ? "selected=selected" : "");?>>40h</option>
								<option value=12 <?=($user_hour == 12 ? "selected=selected" : "");?>>12x36h</option>
							</select>
                        </label>

                        <label class="label">
                            <span class="legend">Horário de Trabalho:</span>
                            <input value="<?= $user_time; ?>" type="text" class="formHour" name="user_time" " placeholder="Horário de Trabalho:" />
                        </label>

                        <label class="label">
                            <span class="legend">Cargo:</span>
							<select name="user_role" required>
							<?php
							$Read->FullRead("SELECT id, role FROM ugq_roles ORDER BY role ASC;");
							if ($Read->getResult()):
							
								foreach ($Read->getResult() as $ugq_role):
								($user_role === $ugq_role['id'] ? $select="selected=selected": $select=""); 
									echo "<option value={$ugq_role['id']} {$select} >{$ugq_role['role']}</option>";
								endforeach;
							endif;
							?>
							</select>
                        </label>
                        <label class="label">
                            <span class="legend">Último Periódico:</span>
                            <input value="<?= $user_periodico; ?>" type="date" name="user_periodico" " placeholder="Data do último exame periódico:" />
                        </label>
                    </div>
					<div class="label_50">
                        <label class="label">
                            <span class="legend">Setor:</span>
							<select name="user_department" required>
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
                            <span class="legend">Sub-setor:</span>
                            <select name="user_section">
							<?php
							$Read->FullRead("SELECT id, section FROM ugq_section ORDER BY section ASC;");
							if ($Read->getResult()):
								foreach ($Read->getResult() as $ugq_section):
								($user_section === $ugq_section['id'] ? $select="selected=selected": $select=""); 
									echo "<option value={$ugq_section['id']} {$select} >{$ugq_section['section']}</option>";
								endforeach;
							endif;
							?>
							</select>
                        </label>
                    </div>

                    <div class="label_50">
                        <label class="label">
                            <span class="legend">É supervisor do setor?</span>
                            <select name="user_supervisor">
								<option value=0 <?=($user_supervisor == 0 ? "selected=selected" : "");?>>Não</option>
								<option value=1 <?=($user_supervisor == 1 ? "selected=selected" : "");?>>Sim</option>
							</select>
                        </label>

                        <label class="label">
                            <span class="legend">Faz parte da equipe da qualidade?</span>
                            <select name="user_quality">
								<option value=0 <?=($user_quality == 0 ? "selected=selected" : "");?>>Não</option>
								<option value=1 <?=($user_quality == 1 ? "selected=selected" : "");?>>Sim</option>
							</select>
                        </label>
                    </div>
					<div class="label_50">
						<label class="label">
							<span class="legend">E-mail Institucional:</span>
							<input value="<?= $user_inst_email; ?>" type="email" name="user_inst_email" placeholder="E-mail: @huhsp.org.br"/>
						</label>

						<label class="label">
							<span class="legend">Senha:</span>
							<input value="" type="password" name="user_password" placeholder="Senha:" />
						</label>
					</div>
					<div class="label_50">
						<label class="label">
							<span class="legend">Situação:</span>
							<select name="user_status" required>
								<option selected disabled value="">Selecione a situação do colaborador:</option>
							<?php
							$Read->FullRead("SELECT id, status FROM ugq_user_status ORDER BY status ASC;");
							if ($Read->getResult()):
								foreach ($Read->getResult() as $ugq_status):
								($user_status === $ugq_status['id'] ? $select="selected=selected": $select=""); 
									echo "<option value={$ugq_status['id']} {$select} >{$ugq_status['status']}</option>";
								endforeach;
							endif;
							?>
							</select>
						</label>

                    <?php if ($user_level < 10 || $_SESSION['userLogin']['user_level'] == 10): ?>
                        
                            <label class="label">
                                <span class="legend">Nível de acesso:</span>
                                <select name="user_level" required>
                                    <option selected disabled value="">Selecione o nível de acesso:</option>
                                    <?php
                                    $NivelDeAcesso = getWcLevel();
                                    foreach ($NivelDeAcesso as $Nivel => $Desc):
                                        if ($Nivel <= $_SESSION['userLogin']['user_level']):
                                            echo "<option";
                                            if ($Nivel == $user_level):
                                                echo " selected='selected'";
                                            endif;
                                            echo " value='{$Nivel}'>{$Desc}</option>";
                                        endif;
                                    endforeach;
                                    ?>
                                </select>
                            </label>

                            
                        
                    <?php endif; ?>
					</div>
                    <div class="clear"></div>

                    <img class="form_load none fl_right" style="margin-left: 10px; margin-top: 2px;" alt="Enviando Requisição!" title="Enviando Requisição!" src="_img/load.gif"/>
                    <button name="public" value="1" class="btn btn_green fl_right icon-share" style="margin-left: 5px;">Atualizar Usuário!</button>
                    <div class="clear"></div>
                </form>
            </div>
        </article>



        <article class="box box100 wc_tab_target" id="address" style="padding: 0; margin: 0; display: none;">
            <div class="panel_header default">
                <span>
                    <a href="dashboard.php?wc=users/address&user=<?= $user_id; ?>" class="btn btn_green icon-plus a" title="Novo Endereço">Cadastrar Novo</a>
                </span>
                <h2>Endereços </h2>
            </div>
            <div class="panel">
                <?php
                //DELETE TRASH ADDR
                if (DB_AUTO_TRASH):
                    $Delete = new Delete;
                    $Delete->ExeDelete(DB_USERS_ADDR, "WHERE user_id = :id AND addr_street IS NULL AND addr_zipcode IS NULL", "id={$user_id}");
                endif;

                $Read->ExeRead(DB_USERS_ADDR, "WHERE user_id = :user ORDER BY addr_key DESC, addr_name ASC", "user={$user_id}");
                if (!$Read->getResult()):
                    echo "<div class='trigger trigger_info trigger_none al_center'>{$user_name} ainda não possui endereços de entrega cadastrados!</span></div><div class='clear'></div>";
                else:
                    foreach ($Read->getResult() as $Addr):
                        $Addr['addr_complement'] = ($Addr['addr_complement'] ? " - {$Addr['addr_complement']}" : null);
                        $Primary = ($Addr['addr_key'] ? ' - Principal' : null);
                        echo "<div class='single_user_addr' id='{$Addr['addr_id']}'>
                            <h1 class='icon-location'>{$Addr['addr_name']}{$Primary}</h1>
                            <p>{$Addr['addr_street']}, {$Addr['addr_number']}{$Addr['addr_complement']}</p>
                            <p>B. {$Addr['addr_district']}, {$Addr['addr_city']}/{$Addr['addr_state']}, {$Addr['addr_country']}</p>
                            <p>CEP: {$Addr['addr_zipcode']}</p>

                            <div class='single_user_addr_actions'>
                                <a title='Editar endereço' href='dashboard.php?wc=users/address&id={$Addr['addr_id']}' class='post_single_center icon-notext icon-truck btn btn_blue'></a>
                                <span rel='single_user_addr' class='j_delete_action icon-notext icon-cancel-circle btn btn_red' id='{$Addr['addr_id']}'></span>
                                <span rel='single_user_addr' callback='Users' callback_action='addr_delete' class='j_delete_action_confirm icon-warning btn btn_yellow' style='display: none' id='{$Addr['addr_id']}'>Deletar Endereço?</span>
                            </div>
                        </div>";
                    endforeach;
                endif;
                ?>
                <div class="clear"></div>
            </div>
        </article>
    </div>

    <div class="box box30">
        <?php
        $Image = (file_exists("../uploads/{$user_thumb}") && !is_dir("../uploads/{$user_thumb}") ? "uploads/{$user_thumb}" : 'admin/_img/no_avatar.jpg');
        ?>
        <img class="user_thumb" style="width: 100%;" src="../tim.php?src=<?= $Image; ?>&w=400&h=400" alt="" title=""/>
        
        <div class="panel">
            <div class="box_conf_menu">
                <a class='conf_menu wc_tab wc_active' href='#profile'>Perfil</a>
               
                <a class='conf_menu wc_tab' href='#address'>Endereços</a>
            </div>
        </div>
    </div>
</div>