<?php

declare(strict_types=1);

namespace Tramex\Home;

/**
 * A working exchange with four loads and the bids carriers have placed, so the
 * page shows the mechanism before anyone signs up.
 */
final class Sample
{
    public static function board(): Board
    {
        $board = new Board();
        $board->post(new Load('TR-401', 'Vale Verde Alimentos', 'Campinas, SP', 'Recife, PE', 12400, '07 September', 540000));
        $board->post(new Load('TR-402', 'Norte Frio', 'Manaus, AM', 'Belem, PA', 8600, '08 September', 210000));
        $board->post(new Load('TR-403', 'Oficina Bandeirante', 'Curitiba, PR', 'Montevideo, UY', 3100, '09 September', 480000));
        $board->post(new Load('TR-404', 'Casa do Vidro', 'Sao Paulo, SP', 'Salvador, BA', 5900, '10 September', 295000));

        $board->bid(new Bid('TR-401', 'Rodobrasil', 498000, 4));
        $board->bid(new Bid('TR-401', 'Trans Cerrado', 512000, 3));
        $board->bid(new Bid('TR-402', 'Amazonas Log', 198000, 2));
        $board->bid(new Bid('TR-403', 'Sul Carga', 462000, 6));
        $board->bid(new Bid('TR-403', 'Pampa Frete', 455000, 7));
        $board->bid(new Bid('TR-404', 'Bahia Rota', 281000, 3));
        $board->award('TR-403', 'Pampa Frete');

        return $board;
    }
}
