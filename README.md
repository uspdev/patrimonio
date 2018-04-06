# Patrimonio USP

Esta classe busca informções sobre bens patrimoniais que estão publicas na USP.

### Requisitos

Utiliza redbeanphp como ORM

Precisa ter uma conexão já feita com banco de dados.

dados de configuração estão em arquivo separado.

ele faz um cache dos dados assim não precisa consultar toda vez. O timeout é 30s, configurável.

### Instalação

    composer require uspdev/patrimonio

### Uso

    $patrimonio = new dadosUsp;
    $meu_monitor = $patrimonio->fetchNumpat('008.041864');
    print_r($meu_monitor);
