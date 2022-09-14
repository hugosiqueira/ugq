<style>
    #optin { float: left; width: 100%; text-align: center; padding: 0 10%;}
    #optin *{box-sizing: border-box; -moz-box-sizing: border-box; -webkit-box-sizing: border-box;}
    #optin input {
        border-radius: 3px;
        -moz-border-radius: 3px;
        -webkit-border-radius: 3px;
    }
    #optin input[type="text"],
    #optin input[type="email"] {
        border: 1px solid #ccc;
        font-size: 15px;
        margin-bottom: 15px;
        padding: 8px 10px;
        width: 100%
    }
    #optin input.email { font-size: 1.2em; padding: 15px; background: #fff url(//www.upinside.com.br/uploads/form-img/email.png) no-repeat center right 10px }
    #optin button {
        background: #F2AA27;
        color: #fff;
        cursor: pointer;
        border: none;
        border-bottom: 4px solid #CF9019;
        font-size: 1.4em;
        font-weight: bold;
        padding: 20px;
        text-shadow: 1px 1px #000;
        text-transform: uppercase;
        width: 100%
    }
    #optin button:hover { border-bottom-color: #81590E; }
    .termos{font-size: 0.8em !important; margin: 20px 0 0 0; display: inline-block; padding-left: 25px; background: url(//www.upinside.com.br/uploads/form-img/privace.png) left center no-repeat; text-transform: uppercase; color: #fff;}
</style>
<meta charset="utf-8"/>

<div id="optin">
    <?php require REQUIRE_PATH . '/inc/activeform.php'; ?>
    <p class="termos">Suas informações estão 100% seguras!</p>
</div>