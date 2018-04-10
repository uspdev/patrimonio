<?php
include '../src/dadosUsp.class.php';
use Uspdev\dadosUsp;

$numpat = '008041864'; // ok
//$numpat = '018010931'; // transferido
//$numpat = '018042545'; // baixado
$numpat = '018042545'; // nao existe

$patrimonio = new dadosUsp;

echo $numpat . ': ';
if ($stabem = $patrimonio->stabem($numpat)) {
    echo $stabem . PHP_EOL;
    if ($stabem) {
        $bem_xml = $patrimonio->fetchNumpat($numpat);
        $bem_array = $patrimonio->xml2array($bem_xml);
        $bem_html = $patrimonio->showData($bem_xml);
        print_r($bem_array);
    }
} else {
    echo 'número inválido';
}
unset($patrimonio);
?>

