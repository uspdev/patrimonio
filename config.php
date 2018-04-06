<?php

$c = array();
$c['dbg'] = false; // debug is false

$c['cache_age'] = 30; //cache age to fetch new data in seconds
$c['numpat'] = array(
    'CURLOPT_URL' => "https://uspdigital.usp.br/mercurioweb/PatrimonioMostrar",
    'CURLOPT_REFERER' => "https://uspdigital.usp.br/mercurioweb/ainumpatimonio.jsp?codmnu=248",
    'naoexiste_str' => "Não existe Patrimônio nas condições especificadas!"
);

$c['numpats'] = array(
    'CURLOPT_URL' => "https://uspdigital.usp.br/mercurioweb/PatrimonioResponsavelListar",
    'CURLOPT_REFERER' => "https://uspdigital.usp.br/mercurioweb/ainumpatimonio.jsp?codmnu=247",

    'captcha_url' => 'https://uspdigital.usp.br/mercurioweb/CriarImagemTuring',
    //'capcha_file' => sys_get_temp_dir() . '/' . session_id() . '.capcha_img',

    'CURLOPT_COOKIEFILE' => sys_get_temp_dir() . '/' . session_id() . '.cookie', //lembra dos cookies que guardamos qndo digitamos o captcha?
    'CURLOPT_COOKIEJAR' => sys_get_temp_dir() . '/' . session_id() . '.cookie'
);