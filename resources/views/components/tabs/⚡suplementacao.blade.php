<?php

use App\Models\ConsumoDiario;
use App\Models\Suplemento;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public string $dataAtual;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $nome = '';

    public string $quantidade_dose = '';

    public string $unidade_medida = '';

    public string $informacao_adicional_dose = '';

    public string $horario_indicado = '';

    public string $regra_uso = 'diario';

    public string $observacao = '';

    public function mount(): void
    {
        $this->dataAtual = now()->toDateString();
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'quantidade_dose' => 'required|numeric|min:0.1',
            'unidade_medida' => 'required|string|max:50',
            'informacao_adicional_dose' => 'nullable|string|max:255',
            'horario_indicado' => 'required|string|max:255',
            'regra_uso' => 'required|in:diario,apenas_treino',
            'observacao' => 'nullable|string|max:255',
        ];
    }

    public function novoSuplemento(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function editarSuplemento(int $id): void
    {
        $suplemento = Suplemento::where('user_id', Auth::id())->findOrFail($id);

        $this->editingId = $suplemento->id;
        $this->nome = $suplemento->nome;
        $this->quantidade_dose = (string) floatval($suplemento->quantidade_dose);
        $this->unidade_medida = $suplemento->unidade_medida;
        $this->informacao_adicional_dose = $suplemento->informacao_adicional_dose ?? '';
        $this->horario_indicado = $suplemento->horario_indicado;
        $this->regra_uso = $suplemento->regra_uso;
        $this->observacao = $suplemento->observacao ?? '';

        $this->showModal = true;
    }

    public function salvarSuplemento(): void
    {
        $validated = $this->validate();
        $validated['user_id'] = Auth::id();

        if ($this->editingId) {
            Suplemento::where('user_id', Auth::id())
                ->where('id', $this->editingId)
                ->update($validated);
        } else {
            Suplemento::create($validated);
        }

        $this->fecharModal();
    }

    public function excluirSuplemento(int $id): void
    {
        Suplemento::where('user_id', Auth::id())->where('id', $id)->delete();
    }

    public function fecharModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'nome', 'quantidade_dose', 'unidade_medida',
            'informacao_adicional_dose', 'horario_indicado',
            'regra_uso', 'observacao',
        ]);
    }

    public function toggleConsumo(int $suplementoId): void
    {
        $consumo = ConsumoDiario::firstOrNew([
            'user_id' => Auth::id(),
            'suplemento_id' => $suplementoId,
            'data_consumo' => $this->dataAtual,
        ]);

        $consumo->consumido = ! $consumo->consumido;
        $consumo->save();
    }

    public function with(): array
    {
        return [
            'suplementos' => Suplemento::where('user_id', Auth::id())
                ->with(['consumos' => function ($query) {
                    $query->where('data_consumo', $this->dataAtual);
                }])
                ->get(),
        ];
    }
}
?>

<div>
    <h2 class="text-2xl font-black uppercase mb-2 text-[#000000] tracking-tight">SUPLEMENTAÇÃO</h2>
    <p class="text-[11px] font-mono uppercase text-gray-700 mb-8 tracking-widest">Controle diário de ingestão</p>

    <button wire:click="novoSuplemento" class="bg-[#000000] text-white px-6 py-2.5 font-bold flex items-center gap-2 mb-8 active:translate-y-1 transition-transform border-2 border-[#000000] shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
        <span class="text-xl leading-none">+</span> NOVO
    </button>

    <div class="border-b-2 border-[#000000] w-full mb-8"></div>

    <div id="supplement-cards-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($suplementos as $suplemento)
            @php
                $consumidoHoje = $suplemento->consumos->first()?->consumido ?? false;
            @endphp

            <div class="border-2 border-[#000000] bg-white flex flex-col shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:-translate-y-1 hover:shadow-[4px_4px_0px_0px_#fde047] transition-all duration-300 group">
                <div class="flex items-center justify-between p-4 border-b-2 border-[#000000] bg-transparent">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-[#000000]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 11h-4a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h4" />
                            <path d="M6 7v13a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7" />
                            <rect width="16" height="5" x="4" y="2" rx="1" />
                        </svg>
                        <h3 class="font-black text-sm uppercase tracking-wider text-[#000000] pt-1 leading-none">{{ $suplemento->nome }}</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <button wire:click="editarSuplemento({{ $suplemento->id }})" class="text-blue-600 hover:text-blue-800 group-hover:text-[#000000] transition-colors active:scale-95" title="Editar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                        <button wire:click="excluirSuplemento({{ $suplemento->id }})" wire:confirm="Tem certeza que deseja excluir este suplemento da sua rotina?" class="text-red-600 hover:text-red-800 group-hover:text-[#000000] transition-colors active:scale-95" title="Excluir">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-5 flex-1 flex flex-col items-center justify-center text-center">
                    <div class="mb-6 flex flex-row items-start justify-center gap-8 w-full">

                        <div class="flex flex-col items-center">
                            <span class="text-[9px] uppercase font-mono text-gray-500 group-hover:text-[#000000] mb-1 tracking-widest transition-colors">Dose</span>
                            <span class="font-black text-3xl text-[#000000] tracking-wide leading-none">
                                {{ floatval($suplemento->quantidade_dose) }}
                            </span>
                        </div>

                        <div class="flex flex-col items-center">
                            <span class="text-[9px] uppercase font-mono text-gray-500 group-hover:text-[#000000] mb-1 tracking-widest transition-colors">Quantidade</span>
                            <div class="flex items-baseline justify-center gap-1.5">
                                <span class="font-black text-xl text-[#000000] uppercase tracking-wide leading-none">
                                    {{ $suplemento->unidade_medida }}
                                </span>
                                @if($suplemento->informacao_adicional_dose)
                                    <span class="font-bold text-sm text-gray-600 group-hover:text-[#000000] tracking-wide transition-colors">
                                        ({{ $suplemento->informacao_adicional_dose }})
                                    </span>
                                @endif
                            </div>
                        </div>

                    </div>

                    <span class="inline-block border-2 border-[#000000] text-[#000000] text-[9px] font-bold uppercase tracking-wider px-2.5 py-1 bg-white group-hover:bg-[#000000] group-hover:text-[#fde047] transition-colors">
        {{ $suplemento->horario_indicado }}
    </span>
                </div>

                <div class="border-t-2 border-dashed border-gray-200 mx-4"></div>

                <div class="p-4 flex flex-col items-center justify-center">
                    <label class="flex items-center justify-center gap-3 cursor-pointer {{ $suplemento->regra_uso === 'apenas_treino' && !$consumidoHoje ? 'opacity-60' : '' }}">
                        <input
                            type="checkbox"
                            wire:click="toggleConsumo({{ $suplemento->id }})"
                            class="w-7 h-7 cursor-pointer rounded-sm text-[#000000] focus:ring-[#000000] border-2 bg-white transition-colors {{ $suplemento->regra_uso === 'apenas_treino' && !$consumidoHoje ? 'border-gray-400 group-hover:border-[#000000]' : 'border-[#000000]' }}"
                            @checked($consumidoHoje)
                        >
                        <span class="text-[13px] font-bold uppercase tracking-wider pt-0.5 transition-colors {{ $suplemento->regra_uso === 'apenas_treino' && !$consumidoHoje ? 'text-gray-500 group-hover:text-[#000000]' : 'text-[#000000]' }}">
                            {{ $suplemento->regra_uso === 'apenas_treino' && !$consumidoHoje ? 'Apenas dias de treino' : 'Consumido Hoje' }}
                        </span>
                    </label>

                    @if($suplemento->observacao)
                        <p class="text-[8px] font-mono text-center text-gray-500 group-hover:text-[#000000] mt-3 lowercase transition-colors">{{ $suplemento->observacao }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white border-2 border-[#000000] shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] w-full max-w-md max-h-[90vh] flex flex-col">
                <div class="flex justify-between items-center p-4 border-b-2 border-[#000000] bg-gray-50">
                    <h3 class="font-black text-lg uppercase tracking-wider text-[#000000]">
                        {{ $editingId ? 'Editar Suplemento' : 'Novo Suplemento' }}
                    </h3>
                    <button wire:click="fecharModal" class="text-[#000000] hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto flex-1">
                    <form wire:submit="salvarSuplemento" class="flex flex-col gap-4">

                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-black uppercase tracking-wider text-[#000000]">Nome do Suplemento</label>
                            <input type="text" wire:model="nome" class="border-2 border-[#000000] p-2 text-sm font-bold focus:outline-none focus:ring-0 bg-white" placeholder="Ex: Whey Protein">
                            @error('nome') <span class="text-red-500 text-[10px] font-bold uppercase">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-black uppercase tracking-wider text-[#000000]">Quantidade (Nº)</label>
                                <input type="number" step="0.1" wire:model="quantidade_dose" class="border-2 border-[#000000] p-2 text-sm font-bold focus:outline-none focus:ring-0 bg-white" placeholder="Ex: 5, 1, 2...">
                                @error('quantidade_dose') <span class="text-red-500 text-[10px] font-bold uppercase">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-black uppercase tracking-wider text-[#000000]">Medida</label>
                                <input type="text" wire:model="unidade_medida" class="border-2 border-[#000000] p-2 text-sm font-bold focus:outline-none focus:ring-0 bg-white" placeholder="Ex: Gramas, Scoop, Cápsulas">
                                @error('unidade_medida') <span class="text-red-500 text-[10px] font-bold uppercase">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-black uppercase tracking-wider text-[#000000]">Detalhe da dose (Opcional)</label>
                            <input type="text" wire:model="informacao_adicional_dose" class="border-2 border-[#000000] p-2 text-sm font-bold focus:outline-none focus:ring-0 bg-white" placeholder="Ex: 2000mg, 30g">
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-black uppercase tracking-wider text-[#000000]">Horário Indicado</label>
                            <input type="text" wire:model="horario_indicado" class="border-2 border-[#000000] p-2 text-sm font-bold focus:outline-none focus:ring-0 bg-white" placeholder="Ex: Pós-treino">
                            @error('horario_indicado') <span class="text-red-500 text-[10px] font-bold uppercase">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-black uppercase tracking-wider text-[#000000]">Regra de Uso</label>
                            <select wire:model="regra_uso" class="border-2 border-[#000000] p-2 text-sm font-bold focus:outline-none focus:ring-0 bg-white rounded-none">
                                <option value="diario">Uso Diário</option>
                                <option value="apenas_treino">Apenas em dias de treino</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-black uppercase tracking-wider text-[#000000]">Observação (Opcional)</label>
                            <input type="text" wire:model="observacao" class="border-2 border-[#000000] p-2 text-sm font-bold focus:outline-none focus:ring-0 bg-white" placeholder="Ex: Não beber com refrigerante">
                        </div>

                        <div class="flex gap-4 mt-4">
                            <button type="button" wire:click="fecharModal" class="flex-1 bg-white text-[#000000] px-4 py-3 font-black uppercase tracking-wider border-2 border-[#000000] active:translate-y-1 transition-transform">
                                Cancelar
                            </button>
                            <button type="submit" class="flex-1 bg-[#000000] text-white px-4 py-3 font-black uppercase tracking-wider border-2 border-[#000000] shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:translate-y-1 transition-transform">
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
