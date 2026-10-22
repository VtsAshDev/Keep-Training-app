<?php

use App\Actions\Training\GetExerciseProgress;
use App\Actions\Training\GetRecentWorkouts;
use App\Actions\Training\GetTrainedExercises;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public ?int $exerciseId = null;

    private function user(): User
    {
        /** @var User */
        return Auth::user();
    }

    public function with(): array
    {
        $user = $this->user();
        $exercises = app(GetTrainedExercises::class)->handle($user);

        $selected = $exercises->firstWhere('id', $this->exerciseId) ?? $exercises->first();

        return [
            'exercises' => $exercises,
            'progress' => $selected ? app(GetExerciseProgress::class)->handle($user, $selected->id) : null,
            'recent' => app(GetRecentWorkouts::class)->handle($user),
        ];
    }
};
?>

<x-ui.page>
    <x-slot:header>
        <x-training.header active="history" />
    </x-slot>

    @if ($progress)
        <x-ui.card class="p-4">
            <x-ui.select :label="__('Load progress')" wire:model.live="exerciseId">
                @foreach ($exercises as $exercise)
                    <option value="{{ $exercise->id }}" @selected($exercise->id === $progress->exercise->id)>{{ $exercise->name }}</option>
                @endforeach
            </x-ui.select>

            <div class="mt-3.5 flex flex-wrap gap-5">
                <x-ui.stat :label="__('Current')" :value="Weight::format($progress->currentKg)" unit="kg" />
                @if ($progress->record)
                    <x-ui.stat :label="__('Record')" :value="Weight::format($progress->record->weightKg)" :unit="'× '.$progress->record->reps" accent />
                @endif
                @if ($progress->deltaKg !== null)
                    <x-ui.stat
                        :label="trans_choice(':count workout|:count workouts', count($progress->points))"
                        :value="($progress->deltaKg > 0 ? '+' : '').Weight::format($progress->deltaKg)"
                        unit="kg"
                    />
                @endif
            </div>

            @if (count($progress->points) > 1)
                <x-ui.line-chart
                    class="mt-3"
                    :points="$progress->points"
                    :labels="[$progress->firstDate?->locale(app()->getLocale())->isoFormat('D MMM'), $progress->lastDate?->locale(app()->getLocale())->isoFormat('D MMM')]"
                    :label="__('Load of :exercise went from :from kg to :to kg in the last :count workouts', [
                        'exercise' => $progress->exercise->name,
                        'from' => Weight::format($progress->points[0]),
                        'to' => Weight::format($progress->points[count($progress->points) - 1]),
                        'count' => count($progress->points),
                    ])"
                />
            @else
                <p class="mt-3 text-sm text-ink-muted">{{ __('The chart appears after the second workout with this exercise.') }}</p>
            @endif
        </x-ui.card>
    @endif

    @if ($recent === [])
        <x-ui.empty-state icon="training" :title="__('No workouts yet')" :text="__('Finished workouts and the load progress of each exercise show up here.')" />
    @else
        <x-ui.section-label>{{ __('Recent workouts') }}</x-ui.section-label>
        @foreach ($recent as $workout)
            <x-ui.list-row
                wire:key="workout-{{ $workout->session->id }}"
                :href="route('training.summary', $workout->session->id)"
                wire:navigate
                :letter="$workout->letter"
                :title="Dates::long($workout->session->started_at)"
                :subtitle="collect([
                    $workout->durationMinutes.' min',
                    Weight::format($workout->volumeKg, 0).' kg',
                    $workout->records > 0 ? trans_choice(':count record|:count records', $workout->records) : null,
                ])->filter()->implode(' · ')"
            />
        @endforeach
    @endif
</x-ui.page>
