# Patrimonio USP

Esta classe busca informações sobre bens patrimoniais que estão publicas na USP.

### Requsitos

Esta classe depende de:

    php-xml
    php-curl

### Instalação

    composer require uspdev/patrimonio

### Uso

A classe dadosUsp está deprecada em favor da classe Patrimonio, porém ela vai ser mantida até podermos remover.
    

    $p = new Patrimonio;
    $meu_monitor = $p->fetchNumpat('008.041864');
    print_r($meu_monitor);
    
    $ativo = $p->ativo('008.041864');
    echo $ativo ? 'sim' : 'nao';
    
Tem exemplos na pasta sample
