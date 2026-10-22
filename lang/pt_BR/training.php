<?php

return [
    'usage_rule' => [
        'daily' => 'Todo dia',
        'training_days' => 'Dias de treino',
    ],

    'muscle_group' => [
        'chest' => 'Peito',
        'back' => 'Costas',
        'shoulders' => 'Ombros',
        'biceps' => 'Bíceps',
        'triceps' => 'Tríceps',
        'forearms' => 'Antebraço',
        'abs' => 'Abdômen',
        'quadriceps' => 'Quadríceps',
        'hamstrings' => 'Posterior de coxa',
        'glutes' => 'Glúteos',
        'calves' => 'Panturrilha',
        'full_body' => 'Corpo inteiro',
        'cardio' => 'Cardio',
    ],

    'equipment' => [
        'barbell' => 'Barra',
        'dumbbell' => 'Halter',
        'machine' => 'Máquina',
        'cable' => 'Polia',
        'bodyweight' => 'Peso do corpo',
        'kettlebell' => 'Kettlebell',
        'band' => 'Elástico',
        'other' => 'Outro',
    ],

    'errors' => [
        'workout_in_progress' => 'Já existe um treino em andamento. Retome ou finalize antes de iniciar outro.',
        'session_not_in_progress' => 'Este treino já foi finalizado.',
        'plan_archived' => 'Esta ficha está arquivada.',
        'supplement_unavailable' => 'Este suplemento só pode ser marcado depois de iniciar o treino do dia.',
        'exercise_in_use' => 'Este exercício está em uma ficha ou no histórico e não pode ser excluído.',
    ],
];
