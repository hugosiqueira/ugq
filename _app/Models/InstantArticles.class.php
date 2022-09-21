<?php

/**
 * InstantArticles.class [ HELPER ]
 * Versão 1.0
 *
 * @copyright (c) 2016, Whallysson Avelino - (whallyssonallain@gmail.com)
 */
class InstantArticles {

    private $Head;
    private $Body;
    private $Foot;
    private $Slider;
    private $Related;
    private $Content;
    private $Cods;

    /** PARAMETERS */
    private $Canonical;
    private $Title;
    private $Author;
    private $Descricao;
    private $Capa;
    private $DataPub;
    private $DataMod;
    private $Conteudo;
    private $Slides;
    private $ArticleRel;
    private $CodAnalytics;
    private $SiteName;

    /** REPLACE */
	private $ReplaceTag = array(
        'pre' => 'p',
        '<br>' => ''
    );

    public function ArtCreate(array $Content) {
        // Inicia os parametros
        $this->Parameters($Content);

        // Head
        $this->Body = $this->Header();

        //Analytics
        $this->Body .= $this->Analytics();

        // Header
        $this->Body .= "<header>";
        $this->Body .= "<figure>";
        $this->Body .= "<img src='{$this->Capa}' />";
        $this->Body .= "</figure>";

        $this->Body .= "<h1>{$this->Title}</h1>";
        $this->Body .= "<h2>{$this->Descricao}</h2>";

        // Autor do artigo
        $this->Body .= "<address>{$this->Author}</address>";
        $this->Body .= "<time class='op-published' dateTime='" . date('D, d M Y H:i:s O', strtotime($this->DataPub)) . "'>" . date('d/m/Y H\hi', strtotime($this->DataPub)) . "</time>";
        $this->Body .= "<time class='op-modified' dateTime='" . date('D, d M Y H:i:s O', strtotime($this->DataMod)) . "'>" . date('d/m/Y H\hi', strtotime($this->DataMod)) . "</time>";
        $this->Body .= "</header>";

        // Corpo do artigo
        $this->Body .= $this->Replaces($this->Conteudo);

        // Slides
        $this->Body .= $this->Slide();

        // Footer
        $this->Body .= $this->Footer();

        return (string) "<![CDATA[{$this->Body}]]>";
    }

    /*
     * ***************************************
     * **********  PRIVATE METHODS  **********
     * ***************************************
     */

    private function Parameters(array $Parameters) {
        // Inicia/Limpa
        $this->Clean();

        $Pa = $Parameters;

        $this->Canonical = (!empty($Pa['canonical']) ? $Pa['canonical'] : null);
        $this->Title = (!empty($Pa['title']) ? $Pa['title'] : null);
        $this->Author = (!empty($Pa['author']) ? $Pa['author'] : null);
        $this->Descricao = (!empty($Pa['desc']) ? $Pa['desc'] : null);
        $this->Capa = (!empty($Pa['capa']) ? $Pa['capa'] : null);

        $this->DataPub = (!empty($Pa['data_pub']) ? $Pa['data_pub'] : null);
        $this->DataMod = (!empty($Pa['data_mod']) ? $Pa['data_mod'] : null);
        $this->Conteudo = (!empty($Pa['conteudo']) ? $Pa['conteudo'] : null);
        $this->CodAnalytics = (!empty($Pa['analytics']) ? $Pa['analytics'] : null);
        $this->Slides = (!empty($Pa['slides']) ? $Pa['slides'] : null);
        $this->ArticleRel = (!empty($Pa['artigos_rel']) ? $Pa['artigos_rel'] : null);
        $this->SiteName = (!empty($Pa['site_name']) ? $Pa['site_name'] : null);
    }

    private function Clean() {
        $this->Head = null;
        $this->Body = null;
        $this->Foot = null;
        $this->Slider = null;
        $this->Related = null;
        $this->Content = null;
        $this->Cods = null;
        $this->Title = null;
        $this->Author = null;
        $this->Descricao = null;
        $this->Capa = null;
        $this->Canonical = null;
        $this->DataPub = null;
        $this->DataMod = null;
        $this->Conteudo = null;
        $this->Slides = null;
        $this->ArticleRel = null;
        $this->CodAnalytics = null;
        $this->SiteName = null;
    }

    private function Header() {
        $this->Head = '<!doctype html>';
        $this->Head .= '<html lang="pt-br" prefix="op:http://media.facebook.com/op#">';
        $this->Head .= '<head>';
        $this->Head .= '<meta charset="utf-8">';
        $this->Head .= "<link rel='canonical' href='{$this->Canonical}' />";
        $this->Head .= "<title>{$this->Title}</title>";
        $this->Head .= "<meta property='og:title' content='{$this->Title}' />";
        $this->Head .= "<meta property='og:description' content='{$this->Descricao}' />";
        $this->Head .= "<meta property='og:image' content='{$this->Capa}' />";
        $this->Head .= "<meta property='op:markup_version' content='v1.0' />";
        $this->Head .= "<meta property='fb:use_automatic_ad_placement' content='true' />";
        $this->Head .= "</head>";
        $this->Head .= "<body>";
        $this->Head .= "<article>";

        return $this->Head;
    }

    private function Footer() {
        $this->Foot = "<footer>";

        // Artigos relacionados
        if (!empty($this->ArticleRel)):
            $this->Foot .= "<ul class='op-related-articles'>";
            foreach ($this->ArticleRel as $Art):
                $this->Foot .= "<li><a href='{$Art}'></a></li>";
            endforeach;
            $this->Foot .= "</ul>";
        endif;

        // Copyright detalhes para seu artigo
        $this->Foot .= "<small>© {$this->SiteName}</small>";
        $this->Foot .= "</footer>";
        $this->Foot .= "</article>";
        $this->Foot .= "</body>";
        $this->Foot .= "</html>";

        return $this->Foot;
    }

    private function Analytics() {
        $this->Cods = null;
        $Cod = str_replace(array('<script>', '</script>'), '', trim($this->CodAnalytics));
        $Cod = str_replace(array('     ', '    ', '   ', '  '), ' ', trim($Cod));
        if (!empty($Cod)):
            $this->Cods .= '<figure class="op-tracker">';
            $this->Cods .= '<iframe>';
            $this->Cods .= str_replace(array("\r\n", "\r", "\n"), '', trim($Cod));
            $this->Cods .= '</iframe>';
            $this->Cods .= '</figure>';
        endif;

        return $this->Cods;
    }

    private function Slide() {
        $this->Slider = null;
        if (!empty($this->Slides) && count($this->Slides) > 1):
            $this->Slider .= "<figure class='op-slideshow'>";
            foreach ($this->Slides as $Img):
                $this->Slider .= "<figure>";
                $this->Slider .= "<img src='{$Img}' />";
                $this->Slider .= '</figure>';
            endforeach;
            $this->Slider .= "</figure>";
        endif;

        return $this->Slider;
    }

    // Replaces
    private function Replaces($Content) {
        $this->Content = (string) $Content;
        
		// imagem
		$this->Content = preg_replace('/<img.*src=[\'"](.*?)[\'"].*>/i', '<figure><img src="$1" /></figure>', $this->Content);
		
		// Video (iframe)
		$this->Content = preg_replace('/<iframe.*?src=[\'"](.*?)[\'"].*?<\/iframe>/si', '<figure class="op-interactive"><iframe class="no-margin" width="560" height="315" src="$1"></iframe></figure>', $this->Content);
		
		// H
		$this->Content = preg_replace('/h3|h4|h5|h6/i', 'h2', $this->Content);
		
		// Resto
		$this->Content = preg_replace('/<p><br><\/p>|<p><\/p>|<p> <\/p>|<p>&nbsp;<\/p>|<p lingdex="2"><br><\/p>|<p lingdex="3"><br><\/p>|<p lingdex="4"><br><\/p>|<p lingdex="5"><br><\/p>|<p lingdex="6"><br><\/p>|<p lingdex="7"><br><\/p>|<p lingdex="8"><br><\/p>|<p lingdex="9"><br><\/p>|<p lingdex="10"><br><\/p>|<p lingdex="11"><br><\/p>|<p lingdex="12"><br><\/p>|<p lingdex="13"><br><\/p>/', '', $this->Content);
		
		
        $this->Content = str_replace(array_keys($this->ReplaceTag), array_values($this->ReplaceTag), $this->Content);

        return $this->Content;
    }

}
