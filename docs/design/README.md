# Design de referência — Keep Focus MVP

Telas aprovadas em 2026-10-22. São **referência visual**, não código de produção: cada arquivo em `telas/` é HTML estático com estilos inline e cores em hex. Ao implementar, reproduza layout, hierarquia, espaçamentos, tamanhos e textos, mas usando os componentes Blade de `resources/views/components/ui/` e os tokens semânticos do `app.css` (nunca hex direto, nunca copiar os estilos inline).

Todas as telas estão em modo escuro, exceto `hoje-modo-claro.html`, que mostra como os mesmos tokens ficam no modo claro. Tamanho de referência: celular 390 × 844.

Os dados nas telas (nomes de fichas, cargas, datas) são exemplos. Placeholders entre colchetes, como `[seu e-mail do Google]`, são valores reais do usuário.

## Telas

| Arquivo | Rota | Observações |
|---|---|---|
| `entrada.html` | `/` | Logo, frase e botão "Entrar com Google". |
| `hoje.html` | `/hoje` | Treino sugerido pelo rodízio, botões Iniciar e Trocar, último treino; meta semanal com bolinhas seg–dom e sequência; suplementos do dia. `training_days` aparece desabilitado com etiqueta "Se treinar hoje". |
| `hoje-retomar.html` | `/hoje` | Estado com treino iniciado e não finalizado: card laranja cheio com "Retomar treino", sem opção de iniciar outro. Suplementos `training_days` ficam habilitados. |
| `hoje-primeiro-acesso.html` | `/hoje` | Estado sem fichas: card de estado vazio com "Criar ficha", meta 0 de N, convite para cadastrar suplementos. |
| `hoje-modo-claro.html` | `/hoje` | Mesma tela de `hoje.html` no modo claro. |
| `treino-em-andamento.html` | `/treino/sessao/{id}` | Cabeçalho com voltar, ficha, tempo decorrido, séries feitas e "Finalizar"; barra de progresso; exercícios concluídos e futuros recolhidos; exercício atual expandido com aviso de progressão e tabela de séries (Série, Anterior, kg, Reps, concluir); barra fixa do timer de descanso (−15, +15, Pular). Sem navegação inferior durante o treino. |
| `resumo.html` | `/treino/sessao/{id}/resumo` | Duração, volume e séries; recordes batidos; resumo por exercício; status da meta semanal; "Voltar para Hoje". |
| `fichas.html` | `/treino/fichas` | Abas da seção Treino; fichas ativas na ordem do rodízio com alça de arrastar; "Próxima do rodízio" destacada; "Nova ficha"; arquivadas recolhidas. |
| `editar-ficha.html` | `/treino/fichas/{id}` | Nome da ficha; exercícios em ordem com alça; item expandido com steppers de séries e descanso e campos de faixa de repetições; "Adicionar exercício" e "Salvar". |
| `historico.html` | `/treino/historico` | Seletor de exercício, carga atual, recorde e variação; gráfico de linha da carga; treinos recentes. |
| `suplementos.html` | `/treino/suplementos` | Suplementos agrupados por regra (Todo dia, Dias de treino) com editar; "Novo suplemento". |
| `configuracoes.html` | `/configuracoes` | Stepper da meta semanal; conta Google; aparência (segue o sistema); sair. A linha "Unidade de carga" depende de decisão em aberto. |

A tela `/treino/exercicios` não foi desenhada: segue o padrão de `suplementos.html` (lista agrupada por grupo muscular, com busca e "Novo exercício").

## Regras de interface definidas nas telas

- Navegação inferior com três itens: **Hoje**, **Treino**, **Ajustes** (rótulo curto para Configurações). Some durante o treino em andamento.
- Dentro de Treino, abas em pílula no topo: Fichas, Histórico, Exercícios, Suplementos.
- **Pré-preenchimento:** cada série vem com carga e repetições da mesma série do último treino com aquele exercício. Quando a regra de progressão dupla se aplica, aparece o aviso de sugestão no topo do exercício, mas a carga **não** é alterada automaticamente.
- Concluir uma série dispara o timer de descanso com o `rest_seconds` do exercício na ficha.
- Toda área tocável tem no mínimo 44 px de altura; botões principais têm 52 px.
- Texto sobre laranja é sempre `on-brand` (quase preto).
- Números de carga, repetições e tempo usam Barlow Condensed com `tabular-nums`.

## Componentes que as telas exigem

Básicos: button (primary, secondary, ghost, add tracejado), input, select, checkbox, card, modal.

Específicos:
- `stepper` (− valor +), usado em séries, descanso e meta semanal
- `tabs` em pílula (subnavegação)
- `bottom-nav` e `top-bar` (com voltar e ação à direita)
- `list-row` (alça de arrastar opcional, título, subtítulo, seta ou ação)
- `set-row` (número, anterior, carga, reps, botão concluir; estados pendente, atual e concluído)
- `supplement-item` (checkbox, nome, dose e horário, etiqueta opcional, estado desabilitado)
- `tag` (fundo laranja suave, texto laranja escuro)
- `rest-timer` (barra fixa com contagem e controles)
- `week-dots` (seg–dom: feito, hoje, pendente)
- `progress-bar`
- `stat` (rótulo e número grande)
- `record-banner` (lista de recordes batidos)
- `empty-state` (ícone, título, texto, ação)
- `line-chart` (SVG simples, sem biblioteca)
