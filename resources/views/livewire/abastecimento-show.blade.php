<div class="space-y-6">
    <flux:card>
        <flux:table id="table" class="display compact" style="width:100%">
            <flux:table.columns>
                <flux:table.column>Filial</flux:table.column>
                <flux:table.column>Codigo</flux:table.column>
                <flux:table.column>Descrição</flux:table.column>
                <flux:table.column>Estoque LJ</flux:table.column>
                <flux:table.column>Estoque CD</flux:table.column>
                <flux:table.column>Disponivel CD</flux:table.column>
                <flux:table.column>Custo</flux:table.column>
                <flux:table.column>Qt Pedida</flux:table.column>
                <flux:table.column>Classe Venda</flux:table.column>
                <flux:table.column>Situação</flux:table.column>
                <flux:table.column>Dias</flux:table.column>
                <flux:table.column>Ultima Entrada</flux:table.column>
                <flux:table.column>Qt Transito</flux:table.column>
                <flux:table.column>Qt Produto</flux:table.column>
                <flux:table.column>Dt Pedido</flux:table.column>
                <flux:table.column>Qt Pedidos</flux:table.column>
                <flux:table.column>Fora Linha</flux:table.column>
                <flux:table.column>Giro Dia</flux:table.column>
                <flux:table.column>Ultima Saida</flux:table.column>
                <flux:table.column>Estoque Min</flux:table.column>
                <flux:table.column>Estoque Max</flux:table.column>
                <flux:table.column>Comprador</flux:table.column>
                <flux:table.column>Perc Estoque</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($abastecimentos as $abastecimento)
                    <flux:table.row>
                        <flux:table.cell>{{ $abastecimento->codfilial }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->codprod }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->descricao }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->qtestger }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->estoque_cd }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->disponivel_cd }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->custocont }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->qtpedida }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->classevenda }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->situacao }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->dias }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->dtultent ? \Carbon\Carbon::parse($abastecimento->dtultent)->format('d/m/Y') : '' }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->qt_transito }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->qt_produto }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->dtpedido ? \Carbon\Carbon::parse($abastecimento->dtpedido)->format('d/m/Y') : '' }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->qtpedidos }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->foralinha }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->qtgirodia }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->dtultsaida ? \Carbon\Carbon::parse($abastecimento->dtultsaida)->format('d/m/Y') : '' }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->estoquemin }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->estoquemax }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->codcomprador }}</flux:table.cell>
                        <flux:table.cell>{{ $abastecimento->perc_est }}</flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>

@assets
<script src="{{ asset('datatables/datatables.min.js') }}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js"></script>

<link href="{{ asset('datatables/datatables.min.css') }}" rel="stylesheet">
@endassets

@script
<script>
    $(document).ready(function () {
        const table = $('#table').DataTable({
            language: {
                url: '{!! asset('datatables/pt-BR.json') !!}'
            },
            lengthMenu: [
                [15, 25, 50, 100, -1],
                [15, 25, 50, 100, 'All']
            ],
            layout: {
                topStart: 'buttons',
                bottomStart: 'pageLength',
            },
            colReorder: true,
            scrollX: true,
            buttons: [
                'colvis',
                'searchBuilder',
                {
                    extend: 'excelHtml5',
                    title: {!! json_encode($titulo ?? 'Relatório') !!},
                    filename: {!! json_encode($titulo ?? 'Relatório') !!},
                    createEmptyCells: true,
                    customizeData: function (data) {
                        for (var i = 0; i < data.body.length; i++) {
                            for (var j = 0; j < data.body[i].length; j++) {
                                // Verifica se no DataTables o campo é maior que 16 dígitos e é um número para converter em string
                                if (!isNaN(data.body[i][j]) && data.body[i][j].length > 16) {
                                    data.body[i][j] = '\u200C' + data.body[i][j];
                                }

                                // Verifica se o valor numérico começa com '.' e adiciona o zero
                                if (typeof data.body[i][j] === 'string' && data.body[i][j].trim().startsWith('.')) {
                                    data.body[i][j] = '0' + data.body[i][j];
                                }
                            }
                        }
                    },
                    customize: function (xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        // Estilos para a primeira linha: centralizado, negrito, com bordas
                        sheet.querySelectorAll('row').forEach((row, rowIndex) => {
                            if (rowIndex === 0) { // Primeira linha
                                row.querySelectorAll('c').forEach((el) => {
                                    el.setAttribute('s', '51'); // Estilo 51: centralizado e negrito com bordas
                                });
                            } else if (rowIndex === 1) { // Segunda linha
                                row.querySelectorAll('c').forEach((el) => {
                                    el.setAttribute('s', '27'); // Estilo 27: negrito com bordas
                                });
                            } else if (rowIndex >= 2) { // Terceira linha em diante
                                row.querySelectorAll('c').forEach((el) => {
                                    el.setAttribute('s', '25'); // Estilo 25: bordas
                                });
                            }
                        });
                    },
                    exportOptions: {
                        columns: ':visible',
                    }
                },
                {
                    extend: 'print',
                    title: {!! json_encode($titulo ?? 'Relatório') !!}, // Customiza o título
                    messageTop: function () {
                        // Centraliza a data no topo do documento de impressão
                        return '<h2 style="text-align: center; margin-bottom: 30px;">' + {!! json_encode($data ?? '') !!} + '</h2>';
                    },
                    customize: function (win) {
                        $(win.document.body).find('h1').css('text-align', 'center');
                    },
                    exportOptions: {
                        columns: ':visible',
                    }
                },
                {
                    extend: 'pdfHtml5',
                    title: {!! json_encode($titulo ?? 'Relatório') !!},
                    filename: {!! json_encode($titulo ?? 'Relatório') !!},
                    orientation: 'landscape',
                    pageSize: 'A3',
                    customize: function (doc) {
                        // Encontra o título e ajusta sua margem inferior
                        doc.content[0].margin = [0, 0, 0, 5]; // Ajuste a margem inferior do título

                        // Centraliza a mensagem no topo do PDF
                        doc.content.splice(1, 0, {
                            margin: [0, 0, 0, 12],
                            alignment: 'center',
                            fontSize: 12, // Aumente esse valor para uma fonte maior
                            text: {!! json_encode($data ?? '') !!},
                        });
                    },
                    exportOptions: {
                        columns: ':visible' // Somente colunas visíveis serão exportadas
                    },
                },
                {
                    extend: 'csvHtml5',
                    title: {!! json_encode($titulo ?? 'Relatório') !!},
                    filename: {!! json_encode($titulo ?? 'Relatório') !!},
                    exportOptions: {
                        columns: ':visible',
                    }
                }
            ],
            /*Verifica se o campo da tabela possui R$ para formatalo como numero*/
            createdRow: function (row, data, dataIndex) {
                $('td', row).each(function (index) {
                    var cellData = data[index];

                    // Verifica se o valor é uma string numérica que começa com '.'
                    if (typeof cellData === 'string' && cellData.trim().startsWith('.')) {
                        // Adiciona o zero antes do ponto
                        cellData = '0' + cellData;
                    }

                    // Verifica se o valor é numérico e atualiza a célula com o valor correto
                    if (!isNaN(cellData)) {
                        $(this).text(cellData);
                    }

                    if (typeof cellData === 'string' && cellData.includes('R$')) {
                        var numericValue = parseFloat(cellData.replace(/[^\d.-]/g, ''));
                        $(this).text('R$ ' + numeral(numericValue).format('0,0.00').replace(/,/g, 'X').replace(/\./g, ',').replace(/X/g, '.'));
                    }
                });
            }
        });
    });
</script>

@endscript
