<?php

namespace Uspdev;

class dadosUsp
{
    /* Classe que:
     * dado um número de patrimonio, busca na base da usp e mostra suas informações
     * dado um número USP, retorna a lista de patrimônios associados a essa pessoa
     */

    private $c = array();

    public function __construct()
    {
        $this->c['numpat'] = array(
            'CURLOPT_URL' => "https://uspdigital.usp.br/mercurioweb/PatrimonioMostrar",
            'CURLOPT_REFERER' => "https://uspdigital.usp.br/mercurioweb/ainumpatimonio.jsp?codmnu=248",
            'naoexiste_str' => "Não existe Patrimônio nas condições especificadas!"
        );
        $this->c['numpats'] = array(
            'CURLOPT_URL' => "https://uspdigital.usp.br/mercurioweb/PatrimonioResponsavelListar",
            'CURLOPT_REFERER' => "https://uspdigital.usp.br/mercurioweb/ainumpatimonio.jsp?codmnu=247",
            'captcha_url' => 'https://uspdigital.usp.br/mercurioweb/CriarImagemTuring',
            'CURLOPT_COOKIEFILE' => sys_get_temp_dir() . '/' . session_id() . '.cookie', //lembra dos cookies que guardamos quando digitamos o captcha?
            'CURLOPT_COOKIEJAR' => sys_get_temp_dir() . '/' . session_id() . '.cookie'
        );
    }

    public function fetchNumpat($numpat)
    {
        $args = $this->c['numpat'];
        $args['postfields'] = 'numpat=' . $numpat . '&saida=1'; // pede saida em xml
        $args['captcha'] = false; //sem captcha
        return utf8_encode($this->curlPost($args));
    }

    /*
     * dado o número usp e o captcha este método retorna os
     * números de patrimônios encontrados na forma de array
     */
    public function fetchNumpats($codpes, $captcha_string)
    {
        // primeiro tem de pegar o captcha com getCaptchaImg()
        $args = $this->c['numpats'];
        $args['postfields'] = 'codpes=' . $codpes . '&chars=' . $captcha_string; // pede saida em xml
        $args['captcha'] = true; //com captcha
        $ret = dadosUsp::curlPost($args);
        preg_match_all('/\d{3}.\d{6}/', $ret, $matches);
        return $matches[0];
    }

    /*
     * Imprime na tela a imagem do captcha para listar os dados usp por responsavel
     */
    public function getCaptchaImg()
    {
        $args = $this->c['numpats'];
        $options = array(
            CURLOPT_URL => $args['captcha_url'], //url que produz a imagem do captcha.
            CURLOPT_COOKIEFILE => $args['CURLOPT_COOKIEFILE'],
            CURLOPT_COOKIEJAR => $args['CURLOPT_COOKIEJAR'],
            CURLOPT_FOLLOWLOCATION => 1, //não sei, mas funciona :D
            CURLOPT_RETURNTRANSFER => 1, //retorna o conteúdo.
            CURLOPT_BINARYTRANSFER => 1, //essa tranferencia é binária.
            CURLOPT_HEADER => 0, //não imprime o header.
        );

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $captcha_img = curl_exec($ch);
        curl_close($ch);

        header("content-Type: image/png");
        echo $captcha_img;
        return true;
    }

    private function curlPost(array $args)
    {
        $options = array(
            CURLOPT_URL => $args['CURLOPT_URL'],
            CURLOPT_REFERER => $args['CURLOPT_REFERER'],
            CURLOPT_POSTFIELDS => $args['postfields'],
            CURLOPT_VERBOSE => 1,
            CURLOPT_AUTOREFERER => false,
            // Turn off the server and peer verification (TrustManager Concept).
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_SSL_VERIFYHOST => FALSE,

            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)",
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1
        );

        if ($args['captcha'] == true) {
            $options[CURLOPT_COOKIEFILE] = $args['CURLOPT_COOKIEFILE'];
            $options[CURLOPT_COOKIEJAR] = $args['CURLOPT_COOKIEJAR'];
        }
        $ch = curl_init();
        curl_reset($ch);
        curl_setopt_array($ch, $options);
        $httpResponse = curl_exec($ch);
        curl_close($ch);

        // vamos apagar o cookie do fs
        //if (file_exists($args['CURLOPT_COOKIEFILE']))
        //   unlink($args['CURLOPT_COOKIEFILE']);

        if (!$httpResponse) {
            // todo: lançar exceções aqui é bom? teria de tratar mais alto nivel eu nao gerar excessão?
            //throw new Exception("Error! : " . $httpResponse);
        }

        if (isset($args['naoexiste_str'])) {
            //print_r($args);
            if (strpos($httpResponse, utf8_decode($args['naoexiste_str'])) !== false) {
                //throw new Exception ($args['naoexiste_str']);
            }
        }
        return $httpResponse;
    }

    public static function xml2array($xmlToParse)
    {
        // o utf8_decode corrigiu problemas de acentuação no json exportado
        $e = str_replace("&", "e", utf8_decode($xmlToParse));
        //$e = str_replace("&", "e", $xmlToParse);

        $object = simplexml_load_string($e);
        $array = @json_decode(@json_encode($object), 1);
        return array_filter($array);
    }

    public static function showData($xml) // mostra formatado em html (para testes)
    {
        $data = dadosUsp::xml2array($xml);
        $ret = '';
        foreach ($data as $field => $val) {
            $ret .= '<br>' . $field . ': ' . $val;
        }
        return $ret;
    }
}

?>
