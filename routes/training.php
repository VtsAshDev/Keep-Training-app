<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('treino')->name('training.')->group(function () {
    Route::redirect('/', '/treino/fichas');
    Route::livewire('/fichas', 'training.plans')->name('plans');
    Route::livewire('/fichas/{plan}', 'training.plan-editor')->whereNumber('plan')->name('plans.edit');
    Route::livewire('/sessao/{session}', 'training.workout')->whereNumber('session')->name('session');
    Route::livewire('/sessao/{session}/resumo', 'training.summary')->whereNumber('session')->name('summary');
    Route::livewire('/historico', 'training.history')->name('history');
    Route::livewire('/exercicios', 'training.exercises')->name('exercises');
    Route::livewire('/suplementos', 'training.supplements')->name('supplements');
});
