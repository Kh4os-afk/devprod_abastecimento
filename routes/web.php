<?php

use Illuminate\Support\Facades\Route;

Route::get('/',\App\Livewire\Abastecimento::class);
Route::get('/abastecimento',\App\Livewire\Abastecimento::class);
Route::get('/abastecimento/show', \App\Livewire\AbastecimentoShow::class)->name('abastecimento.show');
