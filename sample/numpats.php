<?php
include '../src/dadosUsp.class.php';

use Uspdev\dadosUsp;

if (!empty($_POST)) {
    $patrimonio = new dadosUsp;
    $numpats = $patrimonio->fetchNumpats($_POST['codpes'], $_POST['captcha']);
    if (empty($numpats)) {
        $numpats[0] = 'Não retornou dados. O captcha ou nro USP estão certos?';
    }
}

?><!DOCTYPE html>
<html lang="pt_BR">
<head>
    <meta charset="utf-8">
</head>
<body>
<form method="post" target="">
    Digite o número USP: <br/>
    <input type="text" name="codpes" value=""><br />
    <br />

    Digite o conteúdo da imagem:
    <br/>
    <input type="text" name="captcha"><br />
    <img src="captcha.php"> <br />
    <input type="submit" value="OK">
</form>

<?php if (!empty($numpats)) { ?>
<div>
    Foram encotrados <?php echo count($numpats) ?> patrimonios. <br />
    <pre>
    <?php print_r($numpats); ?>
    </pre>
</div>

<?php } ?>
</body>
</html>
