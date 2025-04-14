<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AbastecimentoShow extends Component
{
    use \Livewire\WithPagination;

    public $codfilial;
    public $codfornec;
    public $codprod;
    public $codsec;
    public $codcategoria;
    public $codsubcategoria;

    public $abastecimentos = [];

    #[\Livewire\Attributes\Computed]
    public function paginator()
    {
        return new \Illuminate\Pagination\LengthAwarePaginator(items: range(1, 50), total: 100, perPage: 10, currentPage: 1);
    }

    #[\Livewire\Attributes\Computed]
    public function stats()
    {
        return [
            [
                'title' => 'Total revenue',
                'value' => '$38,393.12',
                'trend' => '16.2%',
                'trendUp' => true
            ],
            [
                'title' => 'Total transactions',
                'value' => '428',
                'trend' => '12.4%',
                'trendUp' => false
            ],
            [
                'title' => 'Total customers',
                'value' => '376',
                'trend' => '12.6%',
                'trendUp' => true
            ],
            [
                'title' => 'Average order value',
                'value' => '$87.12',
                'trend' => '13.7%',
                'trendUp' => true
            ]
        ];
    }

    #[\Livewire\Attributes\Computed]
    public function rows()
    {
        return collect([
            [
                'id' => 1,
                'date' => '2025-01-01',
                'status_color' => 'green',
                'status' => 'green',
                'customer' => 'Lucas',
                'purchase' => 100,
                'amount' => 200,
            ], [
                'id' => 1,
                'date' => '2025-01-01',
                'status_color' => 'green',
                'status' => 'green',
                'customer' => 'Lucas',
                'purchase' => 100,
                'amount' => 200,
            ], [
                'id' => 1,
                'date' => '2025-01-01',
                'status_color' => 'green',
                'status' => 'green',
                'customer' => 'Lucas',
                'purchase' => 100,
                'amount' => 200,
            ], [
                'id' => 1,
                'date' => '2025-01-01',
                'status_color' => 'green',
                'status' => 'green',
                'customer' => 'Lucas',
                'purchase' => 100,
                'amount' => 200,
            ], [
                'id' => 1,
                'date' => '2025-01-01',
                'status_color' => 'green',
                'status' => 'green',
                'customer' => 'Lucas',
                'purchase' => 100,
                'amount' => 200,
            ],
        ]);
    }
    public function mount()
    {
        $this->codfilial = request()->query('codfilial');
        $this->codfornec = request()->query('codfornec');
        $this->codprod = request()->query('codprod');
        $this->codsec = request()->query('codsec');
        $this->codcategoria = request()->query('codcategoria');
        $this->codsubcategoria = request()->query('codsubcategoria');

        $this->buscarAbastecimentos();
    }

    public function buscarAbastecimentos()
    {
        $sqlfilial = $this->codfilial ? "AND E.CODFILIAL = $this->codfilial" : '';
        $codfornec = $this->codfornec ? "AND P.CODFORNEC = $this->codfornec" : '';
        $sqlcodprod = $this->codprod ? "AND P.CODPROD = $this->codprod" : '';
        $sqlcodsec = $this->codsec ? "AND P.CODSEC = $this->codsec" : '';
        $sqlcodcategoria = $this->codcategoria ? "AND P.CODCATEGORIA = $this->codcategoria" : '';
        $sqlcodsubcategoria = $this->codsubcategoria ? "AND P.CODSUBCATEGORIA = $this->codsubcategoria" : '';

        $this->abastecimentos = DB::connection('oracle')->select("
            WITH PEDIDOS
                AS (  SELECT NVL (SUM (PCPEDI.QT), 0) QTPROD,
                             MIN (PCPEDC.DATA) DTPEDIDO,
                             COUNT (DISTINCT PCPEDC.NUMPED) QTPEDIDOS,
                             PCCLIENT.CODCLIINT CODFILIAL,
                             PCPEDI.CODPROD
                        FROM PCPEDC, PCPEDI, PCCLIENT
                       WHERE     PCPEDI.NUMPED = PCPEDC.NUMPED
                             AND PCPEDC.CODCLI = PCCLIENT.CODCLI
                             AND PCPEDC.CODFILIAL = 2
                             AND PCPEDC.CONDVENDA NOT IN (4, 7, 14)
                             AND PCPEDC.POSICAO IN ('L',
                                                    'M',
                                                    'B',
                                                    'P')
                    GROUP BY PCPEDI.CODPROD, PCCLIENT.CODCLIINT),
             TRANSITO
                AS (  SELECT PCMOV.CODPROD,
                             PCCLIENT.CODCLIINT codfilial ,
                             (PKG_ESTOQUE.ESTOQUE_DISPONIVEL (PCEST.CODPROD,
                                                              PCEST.CODFILIAL,
                                                              'C'))
                                QTESTGER,
                             NVL (PCEST.QTGIRODIA, 0) QTGIRODIA,
                             PCEST.CUSTOULTENT,
                             SUM (PCMOV.QT) QT,
                             ROUND (SUM (PCMOV.QT / PCPRODUT.QTUNITCX)) AS QTCAIXA
                        FROM PCNFSAID,
                             PCMOV,
                             PCNFENT,
                             PCCLIENT,
                             PCPRODUT,
                             PCEST
                       WHERE     PCMOV.NUMTRANSVENDA = PCNFSAID.NUMTRANSVENDA
                             AND NVL (PCNFSAID.NOTADUPLIQUESVC, 'N') = 'N'
                             AND PCNFSAID.CONDVENDA IN (9, 10)
                             ---AND PCNFSAID.CODFILIAL = '2'
                             AND (   (PCNFSAID.ESPECIE <> 'NE')
                                  OR (    (PCNFSAID.ESPECIE = 'NE')
                                      AND (PCNFSAID.TIPOEMISSAO <> 1)))
                             AND PCNFSAID.DTCANCEL IS NULL
                             AND PCNFSAID.CODCLI = PCCLIENT.CODCLI
                             AND PCMOV.CODPROD = PCPRODUT.CODPROD
                             AND PCEST.CODPROD = PCMOV.CODPROD
                             ---   AND PCMOV.CODPROD = :CODPROD
                             ---   AND PCCLIENT.CODCLIINT = :CODFILIAL
                             AND PCEST.CODFILIAL = PCCLIENT.CODCLIINT
                             AND PCNFSAID.NUMTRANSVENDA = PCNFENT.NUMTRANSVENDAORIG(+)
                             AND (PCNFENT.VLTOTAL(+) > 0)
                             AND NOT EXISTS
                                    (SELECT 1
                                       FROM PCESTCOM
                                      WHERE     PCESTCOM.NUMTRANSVENDA =
                                                   PCNFSAID.NUMTRANSVENDA
                                            AND PCESTCOM.NUMTRANSENT >=
                                                   (SELECT MIN (NUMTRANSENT)
                                                      FROM PCESTCOM)
                                            AND NVL (PCESTCOM.VLDEVOLUCAO, 0) > 0)
                             AND PCNFENT.NUMTRANSVENDAORIG IS NULL
                             AND PCNFSAID.DTSAIDA > (SYSDATE - 30)
                    GROUP BY PCMOV.CODPROD,
                             PCCLIENT.CODCLIINT,
                             (PKG_ESTOQUE.ESTOQUE_DISPONIVEL (PCEST.CODPROD,
                                                              PCEST.CODFILIAL,
                                                              'C')),
                             PCEST.QTGIRODIA,
                             PCEST.CUSTOULTENT)
        SELECT TO_CHAR (E.CODFILIAL, '00') CODFILIAL,
               P.CODPROD,
               P.DESCRICAO || ' ' ||P.EMBALAGEM AS DESCRICAO,
               E.QTESTGER,
               E2.QTESTGER ESTOQUE_CD,
               (E2.QTESTGER - E2.QTRESERV) DISPONIVEL_CD,
               TRUNC (E2.CUSTOCONT, 2) CUSTOCONT,
               NVL (E.QTPEDIDA, 0) QTPEDIDA,
               NVL (P.CLASSEVENDA, 'D') CLASSEVENDA,
               NVL (P.CLASSEESTOQUE, 'C') CLASSEESTOQUE,
               CASE
                  WHEN E.QTESTGER <= 0 THEN 'FALTA'
                  WHEN E.QTESTGER < (NVL (E.QTGIRODIA, 0) * 3) THEN 'RUPTURA'
                  WHEN E.QTESTGER <= NVL (F.ESTOQUEMIN, 15) THEN 'MINIMO'
                  WHEN E.QTESTGER <= NVL (F.ESTOQUEMAX, 60) THEN 'IDEAL'
                  ELSE 'EXCESSO'
               END
                  SITUACAO,
               TRUNC (
                  DECODE (NVL (E.QTGIRODIA, 0),
                          0, 0,
                          (E.QTESTGER / NVL (E.QTGIRODIA, 0))))
                  DIAS,
               E2.DTULTENT,
               T.QT QT_TRANSITO,
               QTPROD QT_PRODUTO,
               DTPEDIDO,
               QTPEDIDOS,
               F.FORALINHA,
               E.QTGIRODIA,
               E.DTULTSAIDA,
               F.ESTOQUEMIN,
               F.ESTOQUEMAX,
               F.CODCOMPRADOR,
               CASE
                  WHEN E.QTESTGER <= 0
                  THEN
                     -100
                  WHEN E.QTESTGER = (F.ESTOQUEMIN - 1)
                  THEN
                     -1
                  WHEN E.QTESTGER <= F.ESTOQUEMIN
                  THEN
                     ROUND (
                          ( (E.QTESTGER - F.ESTOQUEMIN) / NULLIF (F.ESTOQUEMIN, 0))
                        * 100)
                  WHEN E.QTESTGER >= F.ESTOQUEMAX
                  THEN
                     100
                  ELSE
                     ROUND (
                          (  (E.QTESTGER - F.ESTOQUEMIN)
                           / NULLIF ( (F.ESTOQUEMAX - F.ESTOQUEMIN), 0))
                        * 100)
               END
                  AS PERC_EST
          FROM PCEST E2,
               PCEST E
               LEFT JOIN PEDIDOS D
                  ON E.CODFILIAL = D.CODFILIAL AND D.CODPROD = E.CODPROD
               LEFT JOIN TRANSITO T
                  ON E.CODFILIAL = T.CODFILIAL AND T.CODPROD = E.CODPROD,
               PCPRODUT P,
               PCPRODFILIAL F
         WHERE     E.CODPROD = E2.CODPROD
               AND P.CODPROD = E.CODPROD
               AND F.CODFILIAL = E.CODFILIAL
               
               AND F.CODPROD = E.CODPROD
               AND E2.CODFILIAL = 2
               AND E.CODFILIAL <> 2
               $sqlfilial
               $codfornec
               $sqlcodprod
               $sqlcodsec
               $sqlcodcategoria
               $sqlcodsubcategoria
               AND E2.QTESTGER > 1
               AND E.CODFILIAL NOT IN (1,
                                       11,
                                       13,
                                       50,
                                       51,
                                       52,
                                       53)
               AND P.TIPOMERC <> 'MC'
        ");
    }

    public function render()
    {
        return view('livewire.abastecimento-show');
    }
}
