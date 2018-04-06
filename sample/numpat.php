<?php
include '../src/dadosUsp.class.php';
use Uspdev\dadosUsp;

if (!empty($_POST)) {
    $numpat = $_POST['numpat'];
    $patrimonio = new dadosUsp;
    $bem_xml = $patrimonio->fetchNumpat($numpat);
    $bem_array = $patrimonio->xml2array($bem_xml);
    $bem_html = $patrimonio->showData($bem_xml);
}

?><!DOCTYPE html>
<html lang="pt_BR">
<head>
    <meta charset="utf-8">
</head>
<body>
<form method="post" target="">
    Digite um número de patrimônio (somente números): <br/>
    <input type="text" name="numpat" value="008.041864">
    <input type="submit" value="OK">
</form>

<div>
    <?php echo $bem_html; ?>
</div>
</body>
</html>
