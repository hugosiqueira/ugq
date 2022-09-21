<div class="main_sign">
    <span class="wc_goto goto_home">&#x0005E;</span>
    
    <article class="content">
        <header>
            <h1>Receba as Novidades:</h1>
            <p>Deixe seu nome e seu e-mail e fique por dentro!</p>
        </header>
        <form name="signin" action="" method="post">
            <label>
                <span>Nome:</span>
                <input type="text" name="user_name" placeholder="Informe seu Nome:"/>
            </label>
            <label>
                <span>E-mail:</span>
                <input type="text" name="user_email" placeholder="Informe seu E-mail:"/>
            </label>

            <label>
                <button class="btn btn_green">Cadastre-se!</button>
            </label>
        </form>
        <div class="clear"></div>
    </article>
</div>

<div class="main_footer">
    <footer class="content">
        <div class="left">
            &copy; <?= date('Y'); ?> - <?= SITE_NAME; ?><br>
            Orgulhosamente desenvolvido com Work Control!<br>
            Todos os direitos Reservados!
        </div>

        <div class="right">
            Fone: <?= SITE_ADDR_PHONE_A; ?><br>
            E-mail: <?= SITE_ADDR_EMAIL; ?>
        </div>
        <div class="clear"></div>
    </footer>
</div>