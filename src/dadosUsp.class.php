<?php

class dadosUsp
{

    /* Classe que dado um patrimonio, busca na base da usp e faz cache da informação.
     * ele retorna um objeto redbean que é lido do cache
     * 
     */

//construtor da classe
    public function dadosUsp()
    {
    }

    public function fetchNumpat($numpat)
    {
        global $c;
        $args = $c['numpat'];
        $args['postfields'] = 'numpat=' . $numpat . '&saida=1'; // pede saida em xml
        $args['captcha'] = false; //sem captcha
        return dadosUsp::curlPost($args);
    }

    public static function fetchNumpats($codpes, $captcha_string)
    {
        global $c;
        $args = $c['numpats'];
        $args['postfields'] = 'codpes=' . $codpes . '&chars=' . $captcha_string; // pede saida em xml
        $args['captcha'] = true; //com captcha
        return dadosUsp::curlPost($args);
    }

    // imprime na tela a imagem do captcha para listar os dados usp por responsavel
    public function getCaptchaImg()
    {
        global $c;
        $args = $c['numpats'];
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

    public function show_data($xml) //just for testing
    {
        $data = dadosUsp::xml2array($xml);
        $ret = '';
        foreach ($data as $field => $val) {
            $ret .= '<br>' . $field . ': ' . $val;
        }
        return $ret;
    }

    public function cachePessoas($find)
    {
        if (!is_array($find)) die('cachepessoas not array');
        //print_r($find);exit;
        $pessoa = R::find('cachedadosusppessoas', ' codpes = ? ', array($find['codpes']));
        if (count($pessoa) === 0) {
            $pessoa = R::dispense('cachedadosusppessoas');
            $pessoa->codpes = $find['codpes'];
            $pessoa->nompes = $find['nompes'];
            R::store($pessoa);
        } else {
        }
        return true;
    }

    /* Query USP database and fill cache accordly.
     * Return numpat data
     */
    public static function cacheBens($numpat)
    {
        global $c;

        $pat = R::find('cachedadosuspbens', 'numpat=?', [$numpat]);

        if (count($pat) > 1) die('Multiple records'); // die for inconsistency on database

        if (count($pat) < 1) { // if not in database load a new one
            if ($c['dbg']) echo 'No record. Loading ...';
            try {
                $xml_new = utf8_encode(dadosUsp::fetchNumpat($numpat));
            } catch (Exception $e) {
                // as excessões podem ser tratadas aqui.
                // caso já esteja no cache simplesmente não atualiza
                //print_r($e);
                $xml_new = '';

                //die('não leu dados usp: ' . $e);
            }

            //echo 'xmlnew '.$xml_new;exit;

            // se a busca na base usp retornar um html de erro na verdade nao é xml
            if (strpos($xml_new, '<!DOCTYPE HTML') === 0) {
                $xml_new = '';
            }

            if ($xml_new) {
                $pat = R::dispense('cachedadosuspbens');
                $pat->numpat = $numpat;
                $pat->timestamp = R::isoDateTime();
                $pat->xml = $xml_new;
                $data_new = dadosUsp::xml2array(utf8_decode($xml_new));
                $pat->codpes = $data_new['Codpes'];
                $pat->nompes = $data_new['Nompes'];
                $pat->sglcendsp = $data_new['Sglcendsp'];
                $pat->nomsgpitmmat = $data_new['Nomsgpitmmat'];
                $pat->stabem = $data_new['Stabem'];
                $pat->codlocusp = empty($data_new['Codlocusp']) ? 0 : $data_new['Codlocusp'];
                R::store($pat);

                $data_status = 'novo registro';
            } else {
                return false;
            }

        } else { // if in database update accordly
            // check cache age
            $pat = array_pop($pat);
            //print_r($pat);
            $cache_age = time() - strtotime($pat->timestamp);
            if ($cache_age < $c['cache_age']) {
                if ($c['dbg']) echo 'cache age = ' . $cache_age . ' seconds. Nothing to do.';
                $data_status = 'usando cache';
            } else {
                if ($c['dbg']) echo 'cache expired. ';
                try {
                    $xml_new = utf8_encode(dadosUsp::fetchNumpat($numpat));
                } catch (Exception $e) {
                    die('não leu dados usp: ' . $e);
                }

                if ($pat['xml'] == $xml_new) { // se dados são identicos
                    $pat->timestamp = R::isoDateTime(); // atualiza somente o timestamp
                    if ($c['dbg']) echo 'no data change. timestamp renewed at ' . $pat->timestamp;
                    $data_status = 'cache renovado';
                } else {
                    // dados são diferentes, temos de tratar
                    if ($c['dbg']) {
                        echo 'data changed. Showing the difference:';
                        $data = dadosUsp::xml2array($pat['xml']);
                        $data_new = dadosUsp::xml2array($xml_new);
                        foreach ($data as $field => $val) {
                            echo '<br>' . $field . ': ' . $val;
                            if ($val != $data_new[$field]) {
                                echo ' - ' . $data_new[$field] . ' (new)';
                            }
                        }
                    }
                    $pat->xml = $xml_new;
                    $data_status = 'cache alterado';
                }
                R::store($pat);
                if ($c['dbg']) echo '<br>Changes stored.';
            }
        }
        $pat['data_status'] = $data_status;
        dadosUsp::cachePessoas(array('codpes' => $pat->codpes, 'nompes' => $pat->nompes));
        return $pat;
    }

}

?>
