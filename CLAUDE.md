# Keep Focus

Hub pessoal para acompanhar vida e hábitos, construído sobre a base técnica do Keep Training. O entregável atual é o **MVP de transição**: nova identidade Keep Focus, núcleo do hub e o módulo **Treino** completo.

Este arquivo é a referência do projeto para escopo, convenções, modelo de dados e regras de negócio. Siga-o sempre. Decisões novas devem ser registradas aqui (seção "Decisões tomadas").

**Princípio central:** o Keep Focus é um núcleo (usuário, tela Hoje, hábitos, design system) cercado por módulos independentes. Treino é o primeiro módulo e define o padrão que os próximos copiam.

## Status atual

- Branches seguem o padrão do hook `commit-msg` (`KT-<n>`, ex.: `KT-15`); commits no formato `KT-<n>: [TIPO]:Descrição`. A `main` e a VPS continuam rodando o Keep Training até a etapa 6.
- Etapa em andamento: **1. Base limpa** (ver "Plano de transição").

## Stack

- PHP 8.3+, Laravel 13
- Livewire 4 (componentes de arquivo único), Blade, Tailwind 4, Vite
- Autenticação: Google OAuth via Laravel Socialite
- Banco: SQLite (em produção também, com backup antes de cada migration)
- PWA instalável (manifest, service worker `sw.js`, página offline)
- Qualidade: Pest, PHPStan (Larastan), Pint, Husky + lint-staged, GitHub Actions
- Infra: VPS própria, Nginx, deploy a partir da `main`

Antes de concluir qualquer tarefa, rode Pint, PHPStan e Pest e garanta que tudo passa.

## Convenções (obrigatórias)

- Código em **inglês** (tabelas, colunas, classes, métodos, rotas internas). Interface em **português** via arquivos de tradução, locale `pt_BR`.
- Lógica de negócio em classes **Action**, nunca dentro de componentes Livewire. Componentes só orquestram estado e chamam Actions.
- Toda consulta de dados do usuário é escopada por `user_id`. **Nenhuma Action aceita um id sem validar o dono.**
- Valores fechados (regra de uso, tipo de exercício, grupo muscular) como **Enums do PHP**.
- Toda Action tem teste em Pest. Regras de borda (rodízio, dia de treino, progressão, meta semanal) têm testes dedicados.
- Telas montadas apenas com componentes do design system (`components/ui/`). Nada de classes Tailwind longas repetidas nas views.
- Branch por fase ou feature. Merge na `main` somente com CI verde.

## Arquitetura por módulos

Cada módulo tem pasta própria em todas as camadas:

```
app/
  Actions/Training/        regras de negócio do Treino
  Enums/Training/          UsageRule, MuscleGroup...
  Models/Training/         Exercise, WorkoutPlan, WorkoutSession...
  Models/User.php          núcleo
resources/views/
  components/ui/           design system (button, card, input, modal...)
  livewire/core/           Hoje, navegação, configurações
  livewire/training/       telas do módulo Treino
routes/
  web.php                  núcleo + include dos módulos
  training.php             rotas com prefixo /treino
tests/Feature/Training/    testes do módulo
```

**Integração entre módulos:** a tela Hoje não conhece detalhes internos dos módulos. Cada módulo expõe o que aparece no dia (treino sugerido, suplementos pendentes) por uma Action pública, e o Hoje apenas renderiza esses blocos.

## Origem: o que vem do Keep Training

| Item | Destino | Observação |
|---|---|---|
| Stack (Laravel, Livewire, Tailwind, Vite) | Mantém | Base do projeto |
| Login Google (Socialite) | Mantém | Atualizar URL de callback no novo domínio |
| PWA (sw.js, offline, instalação) | Mantém e adapta | Novo manifest, ícones e versão de cache |
| CI, Husky, Pest, PHPStan, Pint, Sail | Mantém | Sem alterações |
| Suplementação (lógica e tabelas) | Refatora | Vira parte do módulo Treino, com dados migrados |
| Views, app.css, ícones e logo | Descarta | Substituídos pelo novo design system |
| Abas placeholder (Treino, Dieta, Saúde, Perfil) | Descarta | Recriadas como módulos quando entrarem no roadmap |
| Arquivo `testing_test_2` (SQLite) | Remove | Adicionar ao `.gitignore` |

**Bugs conhecidos a corrigir na refatoração da suplementação:**
- O toggle de consumo não verifica se o suplemento pertence ao usuário.
- A restrição única de consumo não inclui o `user_id`.

**Importante:** as tabelas antigas de suplementação (`suplementos`, `consumo_diario`) e seus models só podem ser removidos depois da migração de dados (etapa 5).

## Escopo do MVP

Pronto quando for possível usar o Keep Focus todos os dias para treinar com fichas e controlar a suplementação a partir da tela Hoje.

**Dentro do escopo:**
- Nova identidade Keep Focus: nome, logo, paleta, tipografia, design system com modo claro e escuro.
- Núcleo: login Google, tela Hoje, navegação entre módulos, configurações básicas (meta semanal de treinos).
- Treino: catálogo de exercícios (padrão + personalizados).
- Treino: fichas em rodízio com exercícios, séries, faixa de repetições e descanso.
- Treino: registro do treino do dia, pré-preenchido com o último treino, com timer de descanso.
- Treino: histórico e evolução de carga por exercício.
- Treino: suplementação refatorada, integrada à regra de dia de treino.
- Meta semanal de treinos com sequência de semanas cumpridas.
- Migração dos dados de suplementação existentes e deploy no novo domínio.

**Fora do escopo (não implementar):** hábitos genéricos, módulo Foco (Pomodoro), medidas corporais/peso/fotos, Dieta, Saúde, Metas, Tarefas, Diário, notificações push, sincronização offline de registros, compartilhamento social.

## Modelo de dados

Todas as tabelas têm `id` e timestamps. Exceto os exercícios padrão, todas têm `user_id` com cascade on delete.

| Tabela | Campos principais | Regras |
|---|---|---|
| users | name, email, password, weekly_workout_goal (padrão 4) | Núcleo; criado no login Google |
| exercises | user_id (nulo = catálogo padrão), name, muscle_group, equipment | Usuário vê os padrão + os seus |
| workout_plans | user_id, name, position, archived_at, notes | Ficha; arquivar em vez de apagar |
| workout_plan_exercises | workout_plan_id, exercise_id, position, sets, reps_min, reps_max, rest_seconds, notes | Exercícios da ficha, em ordem |
| workout_sessions | user_id, workout_plan_id, started_at, finished_at, notes | finished_at nulo = em andamento |
| workout_sets | workout_session_id, exercise_id, set_number, weight_kg, reps, completed | Aponta para o exercício, não para o item da ficha |
| supplements | user_id, name, dose, unit, dose_notes, scheduled_time, usage_rule, notes | usage_rule: `daily` ou `training_days` |
| supplement_logs | user_id, supplement_id, date, taken | Único em (user_id, supplement_id, date) |

`workout_sets` aponta para `exercises` porque editar ou arquivar uma ficha não pode alterar o histórico, e a evolução de carga deve continuar mesmo se o exercício mudar de ficha.

## Regras de negócio

**Rodízio de fichas.** A próxima ficha é a seguinte, por `position`, à ficha do último treino finalizado, entre as fichas ativas. Depois da última, volta à primeira. Faltar um dia não avança o rodízio.
- Sem nenhum treino finalizado: sugere a primeira ficha ativa.
- Ficha do último treino arquivada: usa a `position` dela para achar a próxima ativa.
- Treino iniciado e não finalizado: a tela Hoje oferece retomar, nunca iniciar outro.
- O usuário pode escolher outra ficha; o rodízio segue a partir da que foi realmente feita.

**Dia de treino.** Não há agenda: um dia é de treino quando existe um `workout_session` iniciado naquela data.

**Suplementação.** Suplementos `daily` aparecem todo dia. Suplementos `training_days` aparecem no Hoje marcados como "se treinar hoje" e ficam ativos quando um treino do dia é iniciado. Marcar e desmarcar alterna o registro do dia, sempre validando o dono do suplemento.

**Pré-preenchimento e progressão.** Ao iniciar um treino, cada série vem com carga e repetições da mesma série do último treino com aquele exercício. Progressão dupla: se no último treino todas as séries atingiram `reps_max`, o app sugere aumentar a carga.

**Meta semanal.** Semana de segunda a domingo. A meta é cumprida quando o número de treinos finalizados na semana atinge `weekly_workout_goal`. A sequência conta semanas consecutivas cumpridas; a semana atual só quebra a sequência depois de terminar.

## Telas

| Tela | Rota | Conteúdo |
|---|---|---|
| Entrada | `/` | Apresentação e botão Entrar com Google |
| Hoje | `/hoje` | Treino sugerido (ou retomar), progresso da meta semanal, suplementos do dia |
| Fichas | `/treino/fichas` | Fichas ativas em ordem, reordenar, arquivar |
| Editar ficha | `/treino/fichas/{id}` | Exercícios com séries, faixa de repetições e descanso |
| Treino em andamento | `/treino/sessao/{id}` | Exercícios em ordem, séries pré-preenchidas, timer de descanso, finalizar |
| Resumo do treino | `/treino/sessao/{id}/resumo` | Duração, volume total, recordes batidos |
| Histórico | `/treino/historico` | Treinos passados e evolução de carga por exercício |
| Exercícios | `/treino/exercicios` | Catálogo padrão e personalizados |
| Suplementos | `/treino/suplementos` | CRUD de suplementos |
| Configurações | `/configuracoes` | Meta semanal, conta, sair |

**Fluxo principal:** abrir no Hoje ("Treino de hoje: Ficha B") → iniciar (ou trocar de ficha antes) → para cada série, confirmar ou ajustar carga e repetições; o timer de descanso dispara ao concluir a série → finalizar e ver o resumo → voltar ao Hoje, com os suplementos de dia de treino ativos.

## Plano de transição

1. **Base limpa** — backup do SQLite da VPS (feito manualmente pelo dono); criar branch `KT-15`; remover views, app.css, ícones, abas placeholder e `testing_test_2`; atualizar APP_NAME, composer.json, manifest.json e locale pt_BR; app rodando com login Google e uma tela `/hoje` vazia.
2. **Design system** — logo, paleta e tipografia; tokens no `@theme` do Tailwind com modo claro e escuro; componentes Blade: button, input, card, modal, checkbox, navegação.
3. **Dados e regras do Treino** — migrations, models e Enums; seeder do catálogo padrão; Actions: próxima ficha, iniciar e finalizar treino, registrar série, pré-preenchimento, progressão, meta semanal, toggle de suplemento; testes Pest de todas as regras de borda.
4. **Telas do Treino e tela Hoje** — exercícios, fichas e edição; treino em andamento com timer e resumo; suplementos e histórico; tela Hoje e configurações.
5. **Migração de dados** — migration que copia `suplementos` e `consumo_diario` para `supplements` e `supplement_logs`; validar com cópia do banco de produção.
6. **Infraestrutura e lançamento** — domínio, DNS, Nginx, SSL; callback no Google Cloud Console; `.env` da VPS e versão de cache do `sw.js`; renomear repositório; merge na main, deploy, reinstalar o PWA.

## Critérios de aceite

- Login com Google funciona e redireciona para a tela Hoje.
- A tela Hoje sugere a ficha correta do rodízio, inclusive após faltas e trocas manuais.
- Um treino pode ser iniciado, retomado depois de fechar o app e finalizado.
- As séries vêm pré-preenchidas com o último treino de cada exercício.
- O timer de descanso dispara ao concluir uma série.
- O histórico mostra a evolução de carga por exercício.
- Suplementos de dia de treino só ficam ativos após iniciar um treino no dia.
- A meta semanal e a sequência de semanas são calculadas corretamente.
- Os suplementos e registros antigos aparecem no Keep Focus.
- Nenhuma Action aceita dados de outro usuário (coberto por testes).
- CI verde: Pint, PHPStan e Pest.
- PWA instalável com nome e ícones do Keep Focus, funcionando em modo claro e escuro.

## Decisões em aberto

- Novo domínio do Keep Focus.
- Identidade visual: paleta, tipografia e logo.
- Manter SQLite em produção ou migrar para MySQL/PostgreSQL antes do lançamento.
- Lista inicial do catálogo padrão de exercícios.
- Unidade de carga fixa em kg ou configurável por usuário.

## Decisões tomadas

(Registrar aqui, com data, cada decisão nova.)

- **2026-10-22** — A branch `keep-focus` virou `KT-15`: o hook `commit-msg` rejeita branches fora do padrão `KT-<n>`.
- **2026-10-22** — O login por e-mail e senha (componente `core.login`) é mantido, ao lado do login Google.
- **2026-10-22** — As migrations `diet_plans` e `diet_plan_items` são mantidas no repositório, sem models nem telas. Dieta continua fora do escopo do MVP.
- **2026-10-22** — O componente legado `components/tabs/⚡suplementacao.blade.php` fica no lugar, fora da navegação e sem rota, como referência até ser substituído pelas Actions e telas do módulo Treino.
- **2026-10-22** — Os ícones do PWA (`public/icon-*.png`, `apple-touch-icon.png`, `favicon.svg`) são provisórios ("KF" sobre fundo escuro) até a identidade da etapa 2.
