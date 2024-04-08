<?php

if (!$WorkControlDefineConf):
    /*
     * URL DO SISTEMA
     */
    if($_SERVER['HTTP_HOST'] == 'localhost'):
        define('BASE', 'https://localhost/'); //Url raiz do site no localhost
    else:
        define('BASE', '#'); //Url raiz do site no servidor
    endif;
    define('THEME', 'wc_default'); //template do site
endif;

//DINAMYC THEME
if (!empty($_SESSION['WC_THEME'])):
    define('THEME', $_SESSION['WC_THEME']); //template do site
endif;

/*
 * PATCH CONFIG
 */
define('INCLUDE_PATH', BASE . '/themes/' . THEME); //Geral de inclusão (Não alterar)
define('REQUIRE_PATH', 'themes/' . THEME); //Geral de inclusão (Não alterar)

if (!$WorkControlDefineConf):
    /*
     * ADMIN CONFIG
     */
    define('ADMIN_NAME', 'Unidade de Garantia da Qualidade');  //Nome do painel de controle (Work Control)
    define('ADMIN_DESC', 'Sistema para gestão do Controle da Qualidade no Hospital São Paulo'); //Descrição do painel de controle (Work Control)
    define('ADMIN_MODE', 1); //1 = website / 2 = e-commerce / 3 = Imobi / 4 = EAD
    define('ADMIN_WC_CUSTOM', 1); //Habilita menu e telas customizadas
    define('ADMIN_MAINTENANCE', 1); //Manutenção
    define('ADMIN_VERSION', '1.0');

    /*
     * E-MAIL SERVER
     * Consulte estes dados com o serviço de hospedagem
     */
    define('MAIL_HOST', 'smtp.gmail.com'); //Servidor de e-mail
    define('MAIL_PORT', '587'); //Porta de envio
    define('MAIL_USER', 'hugo.siqueira@huhsp.org.br'); //E-mail de envio
    define('MAIL_SMTP', 'hugo.siqueira@huhsp.org.br'); //E-mail autenticador do envio (Geralmente igual ao MAIL_USER, exceto em serviços como AmazonSES, sendgrid...)
    define('MAIL_PASS', '50899200'); //Senha do e-mail de envio
    define('MAIL_SENDER', 'Hugo Siqueira'); //Nome do remetente de e-mail
    define('MAIL_MODE', 'tls'); //Encriptação para envio de e-mail [0 não parametrizar / tls / ssl] (Padrão = tls)
    define('MAIL_TESTER', 'hugoleonardos@gmail.com'); //E-mail de testes (DEV)

    /*
     * MEDIA CONFIG
     */
    define('IMAGE_W', 1600); //Tamanho da imagem (WIDTH)
    define('IMAGE_H', 800); //Tamanho da imagem (HEIGHT)
    define('THUMB_W', 800); //Tamanho da miniatura (WIDTH) PDTS
    define('THUMB_H', 1000); //Tamanho da minuatura (HEIGHT) PDTS
    define('AVATAR_W', 500); //Tamanho da miniatura (WIDTH) USERS
    define('AVATAR_H', 500); //Tamanho da minuatura (HEIGHT) USERS
    define('SLIDE_W', 1920); //Tamanho da miniatura (WIDTH) SLIDE
    define('SLIDE_H', 600); //Tamanho da minuatura (HEIGHT) SLIDE

    /*
     * APP CONFIG
     * Habilitar ou desabilitar modos do sistema
     */
    define('APP_POSTS', 0); //Posts
    define('APP_POSTS_AMP', 0); //AMP para Posts
    define('APP_POSTS_INSTANT_ARTICLE', 0); //Instante Article FB
    define('APP_EAD', 0); //Plataforma EAD
    define('APP_SEARCH', 0); //Relatório de Pesquisas
    define('APP_PAGES', 0); //Páginas
    define('APP_COMMENTS', 0); //Comentários
    define('APP_SLIDE', 0); //Slide Em Destaque
    define('APP_USERS', 1); //Usuários
	define('APP_PENDENCY',1); // Pendências

    /*
     * LEVEL CONFIG
     * Configura permissões do painel de controle!
     */
    define('LEVEL_WC_POSTS', 6);
    define('LEVEL_WC_COMMENTS', 6);
    define('LEVEL_WC_PAGES', 6);
    define('LEVEL_WC_SLIDES', 6);
    define('LEVEL_WC_IMOBI', 6);
    define('LEVEL_WC_PRODUCTS', 6);
    define('LEVEL_WC_PRODUCTS_ORDERS', 6);
    define('LEVEL_WC_EAD_COURSES', 6);
    define('LEVEL_WC_EAD_STUDENTS', 6);
    define('LEVEL_WC_EAD_SUPPORT', 6);
    define('LEVEL_WC_EAD_ORDERS', 6);
    define('LEVEL_WC_REPORTS', 6);
    define('LEVEL_WC_USERS', 6);
    define('LEVEL_WC_CONFIG_MASTER', 10);
    define('LEVEL_WC_CONFIG_API', 10);
    define('LEVEL_WC_CONFIG_CODES', 10);
	define('LEVEL_UGQ_PENDENCY', 10);

    /*
     * FB SEGMENT
     * Configura ultra segmentação de público no facebook
     * !!!! IMPORTANTE :: Para utilizar ultra segmentação de produtos e imóveis
     * é precisso antes configurar os catálogos de produtos respectivamente!
     */
    define('SEGMENT_FB_PIXEL_ID', 0); //Id do pixel de rastreamento
    define('SEGMENT_WC_USER', 0); //Enviar dados do login de usuário?
    define('SEGMENT_WC_BLOG', 0); //Ultra segmentar páginas do BLOG?
    define('SEGMENT_WC_ECOMMERCE', 0); //Ultra segmentar páginas do E-COMMERCE?
    define('SEGMENT_WC_IMOBI', 0); //Ultra segmentar páginas do imobi?
    define('SEGMENT_WC_EAD', 0); //Ultra segmentar páginas do EAD?
    define('SEGMENT_GL_ANALYTICS_UA', ''); //ID do Google Analytics (UA-00000000-0)
    define('SEGMENT_FB_PAGE_ID', ''); //ID do Facebook Pages (Obrigatório para POST - Instant Article)
    define('SEGMENT_GL_ADWORDS_ID', ''); //ID do pixel do Adwords (todo o site)
    define('SEGMENT_GL_ADWORDS_LABEL', ''); //Label do pixel do Adwords (todo o site)


    /*
     * APP LINKS
     * Habilitar ou desabilitar campos de links alternativos
     */
    define('APP_LINK_POSTS', 1); //Posts
    define('APP_LINK_PAGES', 1); //Páginas

    /*
     * ACCOUNT CONFIG
     */
    define('ACC_MANAGER', 1); //Conta de usuários (UI)
    define('ACC_TAG', 'Minha Conta!'); //null para OL {NAME} ou texto (Minha Conta, Meu Cadastro, etc)

    /*
     * COMMENT CONFIG
     */
    define('COMMENT_MODERATE', 1); //Todos os NOVOS comentários ficam ocultos até serem aprovados
    define('COMMENT_ON_POSTS', 1); //Aplica comentários aos posts
    define('COMMENT_ON_PAGES', 1); //Aplica comentários as páginas
    define('COMMENT_ON_PRODUCTS', 1); //Aplica comentários aos produtos
    define('COMMENT_SEND_EMAIL', 1); //Envia e-mails transicionais para usuários sobre comentários
    define('COMMENT_ORDER', 'DESC'); //Ordem de exibição dos comentários (ASC ou DESC)
    define('COMMENT_RESPONSE_ORDER', 'ASC'); //Ordem de exibição das respostas (ASC ou DESC)

   
    /*
     * CONFIGURAÇÕES DO EAD
     */
    define('EAD_REGISTER', 0); //Permitir cadastro na plataforma?
    define('EAD_HOTMART_EMAIL', 0); //Email de produtor hotmart!
    define('EAD_HOTMART_TOKEN', 0); //Token da API do hotmart!
    define('EAD_HOTMART_NEGATIVATE', 0); //Id de produtos na hotmart que NÃO serão entregues!
    define('EAD_HOTMART_LOG', 0); //Gerar Log de vendas?
    define('EAD_TASK_SUPPORT_DEFAULT', 1); //Por padrão habilitar suporte em todas as aulas?
    define('EAD_TASK_SUPPORT_EMAIL', "suporte@seusite.com.br"); //Enviar alertas de novos tickets para?
    define('EAD_TASK_SUPPORT_MODERATE', 0); //Tickets devem ser aprovados por um admin?
    define('EAD_TASK_SUPPORT_STUDENT_RESPONSE', 0); //Alunos podem responder o suporte?
    define('EAD_TASK_SUPPORT_PENDING_REVIEW', 0); //Tickets Pendentes de Avaliação.
    define('EAD_TASK_SUPPORT_REPLY_PUBLISH', 0); //Tickets Pendentes de Avaliação.
    define('EAD_TASK_SUPPORT_LEVEL_DELETE', 10); //Level mínimo para poder deletar tickets
    define('EAD_STUDENT_CERTIFICATION', 1); //Você pretende emitir certificados?
    define('EAD_STUDENT_MULTIPLE_LOGIN', 1); //Permitir login multiplo?
    define('EAD_STUDENT_MULTIPLE_LOGIN_BLOCK', 0); //Minutos de bloqueio quando login multiplo!
    define('EAD_STUDENT_CLASS_PERCENT', 100); //Assitir EAD_CLASS_PERCENT% para concluir!
    define('EAD_STUDENT_CLASS_AUTO_CHECK', 0); //Marcar tarefas como concluídas automaticamente?
endif;
