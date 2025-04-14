<?php

namespace App\Livewire;

use App\Models\PcCategoria;
use App\Models\PcFornec;
use App\Models\PcLib;
use App\Models\PcProdut;
use App\Models\PcSecao;
use App\Models\PcSubCategoria;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Flux\Flux;

class Abastecimento extends Component
{
    public bool $disabled = false;
    public $filiais;
    #[Validate(['required', 'numeric'])]
    public $codfilial;
    public $search_fornecedor = '';
    public $codfornec;
    public $search_produto = '';
    public $codprod;
    public $search_secao = '';
    public $codsec;
    public $search_categoria = '';
    public $codcategoria;
    public $search_subcategoria = '';
    public $codsubcategoria;

    public function mount()
    {
        $this->filiais = PcLib::where('codfunc', /*auth()->user()->matricula*/ 5601)->where('codtabela', 1)->pluck('codigoa');
    }

    #[Computed]
    public function fornecedores()
    {
        return PcFornec::where('fornecedor', 'like', strtoupper($this->search_fornecedor))
            ->when(is_numeric($this->search_fornecedor), function ($query) {
                return $query->orWhere('codfornec', $this->search_fornecedor);
            })
            ->limit(50)
            ->orderBy('fornecedor')
            ->get();
    }

    #[Computed]
    public function produtos()
    {
        return PcProdut::where('descricao', 'like', strtoupper($this->search_produto))
            ->when(is_numeric($this->search_produto), function ($query) {
                return $query->orWhere('codprod', $this->search_produto);
            })
            ->limit(50)
            ->orderBy('descricao')
            ->get();
    }

    #[Computed]
    public function secoes()
    {
        return PcSecao::where('descricao', 'like', strtoupper($this->search_secao))
            ->when(is_numeric($this->search_secao), function ($query) {
                return $query->orWhere('codsec', $this->search_secao);
            })
            ->limit(50)
            ->orderBy('descricao')
            ->get();
    }

    #[Computed]
    public function categorias()
    {
        return PcCategoria::where('categoria', 'like', strtoupper($this->search_categoria))
            ->when(is_numeric($this->search_categoria), function ($query) {
                return $query->orWhere('codcategoria', $this->search_categoria);
            })
            ->limit(50)
            ->orderBy('categoria')
            ->get();
    }

    #[Computed]
    public function subcategorias()
    {
        return PcSubCategoria::where('subcategoria', 'like', strtoupper($this->search_subcategoria))
            ->when(is_numeric($this->search_subcategoria), function ($query) {
                return $query->orWhere('codsubcategoria', $this->search_subcategoria);
            })
            ->limit(50)
            ->orderBy('subcategoria')
            ->get();
    }

    public function submit()
    {
        $query = [
            'codfilial' => $this->codfilial,
            'codfornec' => $this->codfornec,
            'codprod' => $this->codprod,
            'codsec' => $this->codsec,
            'codcategoria' => $this->codcategoria,
            'codsubcategoria' => $this->codsubcategoria,
        ];

        $filtrosPreenchidos = collect($query)->filter(function ($valor) {
            return !is_null($valor) && $valor !== '';
        });

        if ($filtrosPreenchidos->count() < 2) {
            Flux::toast(
                heading: 'Atenção',
                text: 'Por favor, selecione ao menos 2 filtros para continuar.',
                variant: 'warning',
            );
        }

        return redirect()->to(route('abastecimento.show', $query));
    }

    public function resetar()
    {
        $this->reset([
            'codfilial',
            'codfornec',
            'codprod',
            'codsec',
            'codcategoria',
            'codsubcategoria',
        ]);
    }

    public function render()
    {
        return view('livewire.abastecimento');
    }
}
