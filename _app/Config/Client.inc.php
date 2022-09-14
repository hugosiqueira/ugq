<?php

if (!$WorkControlDefineConf):
    /*
     * SITE CONFIG
     */
    define('SITE_NAME', 'Nome do Site'); //Nome do site do cliente
    define('SITE_SUBNAME', 'Slogan do site'); //Nome do site do cliente
    define('SITE_DESC', 'Descrição do objetivo do site'); //Descrição do site do cliente

    define('SITE_FONT_NAME', 'Open Sans'); //Tipografia do site (https://www.google.com/fonts)
    define('SITE_FONT_WHIGHT', '300,400,600,700,800'); //Tipografia do site (https://www.google.com/fonts)

    /*
     * SHIP CONFIG
     * DADOS DO SEU CLIENTE/DONO DO SITE
     */
    define('SITE_ADDR_NAME', 'Nome do Remetente'); //Nome de remetente
    define('SITE_ADDR_RS', 'Razão Social'); //Razão Social
    define('SITE_ADDR_EMAIL', 'hugo@agenciabee.com'); //E-mail de contato
    define('SITE_ADDR_SITE', 'agenciabee.com'); //URL descrita
    define('SITE_ADDR_CNPJ', '00.000.000/0000-00'); //CNPJ da empresa
    define('SITE_ADDR_IE', '000/0000000'); //Inscrição estadual da empresa
    define('SITE_ADDR_PHONE_A', '(11) 9999-9999'); //Telefone 1
    define('SITE_ADDR_PHONE_B', '(11) 99999-9999'); //Telefone 2
    define('SITE_ADDR_ADDR', 'Rua, numero, complemento'); //ENDEREÇO: rua, número (complemento)
    define('SITE_ADDR_CITY', 'Cidade'); //ENDEREÇO: cidade
    define('SITE_ADDR_DISTRICT', 'Bairro'); //ENDEREÇO: bairro
    define('SITE_ADDR_UF', 'SP'); //ENDEREÇO: UF do estado
    define('SITE_ADDR_ZIP', '00000-000'); //ENDEREÇO: CEP
    define('SITE_ADDR_COUNTRY', 'Brasil'); //ENDEREÇO: País


    /**
     * Social Config
     */
    define('SITE_SOCIAL_NAME', 'Hugo Siqueira');

    /*
     * Facebook
     */
    define('SITE_SOCIAL_FB', 1);
    define('SITE_SOCIAL_FB_APP', 0); //Opcional APP do facebook
    define('SITE_SOCIAL_FB_AUTHOR', 'hugosiqueira'); //https://www.facebook.com/?????
    define('SITE_SOCIAL_FB_PAGE', 'hugosiqueira'); //https://www.facebook.com/?????
    /*
     * Twitter
     */
    define('SITE_SOCIAL_TWITTER', 'hugosiqueira'); //https://www.twitter.com/?????
    /*
     * YouTube
     */
    define('SITE_SOCIAL_YOUTUBE', 'hugosiqueira'); //https://www.youtube.com/user/?????
    /*
     * Instagram
     */
    define('SITE_SOCIAL_INSTAGRAM', 'hugosiqueira'); //https://www.instagram.com/?????

endif;