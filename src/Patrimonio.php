<?php

namespace Uspdev;

include 'dadosUsp.class.php';

/*
 * Esta classe, que estende dadosUSP serve para ajustar o nome
 * que é mais condizente com a utilização
 */

class Patrimonio extends dadosUsp
{
    /*
     * Verifica se o numpat está ativo na base da USP
     * Caso não exista ou tenha outra situação (baixa, transferido, etc) retorna false
     */
    public function ativo($numpat)
    {
        if ($stabem = $this->stabem($numpat)) {
            if ($stabem == 'Ativo') {
                return true;
            }
        }
        return false;
    }
}