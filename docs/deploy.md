# Lançamento do Keep Focus (etapa 6)

Domínio: **https://keep-focus.luvcode.tech**. A VPS continua servindo o Keep Training a partir da `main` até o merge final; nada aqui deve ser aplicado antes da validação da etapa 5.

## Roteiro com os scripts

Os passos abaixo estão automatizados em dois scripts; o resto deste documento explica cada um.

1. **No WSL**, baixar e validar a cópia do banco (não altera a produção):
   ```bash
   scripts/validate-production-db.sh root@187.77.60.230
   ```
   Todas as linhas precisam terminar em ✔. Mande o resultado antes de seguir.
2. **À mão:** registro `A` de `keep-focus` para `187.77.60.230` no painel do DNS, e as duas URLs no Google Cloud Console (seção "Google Cloud Console").
3. **Copiar o script para a VPS:**
   ```bash
   scp scripts/vps-launch.sh root@187.77.60.230:/root/
   ```
4. **Na VPS**, com o DNS já respondendo:
   ```bash
   bash /root/vps-launch.sh nginx     # site do Nginx + certificado SSL
   ```
5. **Na hora do lançamento**, na VPS:
   ```bash
   bash /root/vps-launch.sh env       # backup + .env do Keep Focus (use "env --new-key" para trocar o APP_KEY)
   ```
   Logo em seguida, fazer o merge da `KT-20` na `main` pelo GitHub (CI verde) e esperar o deploy.
6. **Depois do deploy**, na VPS:
   ```bash
   bash /root/vps-launch.sh check     # migrations, resumo da cópia dos suplementos e HTTPS
   ```
7. Reinstalar o PWA no celular e testar o fluxo (seção "Depois do deploy").

Para voltar atrás: os backups do banco e do `.env` ficam em `/root/keep-focus-backups/` (gerados por `vps-launch.sh backup` e `env`) e em `database/backups/` (gerados pelo deploy).

## Antes do merge

1. **Backup do banco de produção.** Copiar `database/database.sqlite` da VPS para um lugar seguro fora do servidor.
2. **Validar a cópia dos suplementos (etapa 5)** com essa cópia, numa máquina local:
   - colocar o arquivo em `database/producao.sqlite` (ignorado pelo git);
   - dentro do container: `cp database/producao.sqlite /tmp/kf-prod.sqlite && DB_DATABASE=/tmp/kf-prod.sqlite php artisan migrate --force`;
   - comparar `suplementos` × `supplements` e `consumo_diario` × `supplement_logs`, e conferir o resumo `Legacy supplements copied` no `storage/logs/laravel.log` (registros ignorados por pertencerem a outro usuário aparecem em `skipped_foreign_logs`).
3. Depois da validação, criar a migration que remove `suplementos`, `consumo_diario` e os models `Suplemento` e `ConsumoDiario`.

## DNS, Nginx e SSL

1. **DNS:** registro `A` de `keep-focus.luvcode.tech` apontando para o IP da VPS (ou `CNAME` para o host atual do `keep-training.luvcode.tech`).
2. **Nginx:** um `server` para `keep-focus.luvcode.tech` com o mesmo proxy usado hoje pelo Keep Training (o app confia no proxy e lê `X-Forwarded-Proto`, então o proxy precisa enviar esse cabeçalho). O Sail na VPS escuta na porta do `APP_PORT` (hoje `8082`):

   ```nginx
   server {
       server_name keep-focus.luvcode.tech;

       location / {
           proxy_pass http://127.0.0.1:8082;
           proxy_set_header Host $host;
           proxy_set_header X-Real-IP $remote_addr;
           proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
           proxy_set_header X-Forwarded-Proto $scheme;
       }
   }
   ```

3. **SSL:** `certbot --nginx -d keep-focus.luvcode.tech`.
4. Opcional: redirecionar `keep-training.luvcode.tech` para o domínio novo (`return 301 https://keep-focus.luvcode.tech$request_uri;`).

## Google Cloud Console

No cliente OAuth usado hoje:

- **Origens JavaScript autorizadas:** adicionar `https://keep-focus.luvcode.tech`.
- **URIs de redirecionamento autorizados:** adicionar `https://keep-focus.luvcode.tech/auth/google/callback`.
- Manter as URLs antigas até o redirecionamento do domínio antigo estar no ar.

## `.env` da VPS

Alterar ou adicionar (manter `APP_PORT=8082`, `APP_SERVICE`, `APP_KEY` e as credenciais do Google que já existem na VPS):

```dotenv
APP_NAME="Keep Focus"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://keep-focus.luvcode.tech
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=pt_BR
APP_TIMEZONE=America/Sao_Paulo
APP_VERSION=1.0.0

SESSION_SECURE_COOKIE=true

GOOGLE_REDIRECT_URI=https://keep-focus.luvcode.tech/auth/google/callback
```

Trocar `APP_NAME` muda o nome do cookie de sessão (`keep-focus-session`): todo mundo precisa entrar de novo uma vez. Por isso, o lançamento também é um bom momento para gerar um `APP_KEY` novo (`./vendor/bin/sail artisan key:generate --force`), que derruba as sessões antigas.

Só trocar o `.env` no momento do merge: antes disso, a `main` ainda roda o Keep Training.

## Merge e deploy

1. Abrir o PR da última branch da transição para a `main` e esperar o CI verde.
2. Fazer o merge. O deploy da `main` roda sozinho: faz backup do SQLite em `database/backups/` (guarda os 10 mais recentes), ensaia as migrations numa cópia e só migra o banco real se o ensaio passar (`scripts/deploy-migrate.sh`). Se falhar, o deploy para com o banco intacto.
3. Conferir na VPS o resumo `Legacy supplements copied` no log e abrir `/hoje`.
4. Renomear o repositório no GitHub (o GitHub redireciona o endereço antigo; atualizar o `git remote` local quando quiser). O caminho na VPS (`/opt/projects/Keep-Training-app`) pode continuar o mesmo.

## Depois do deploy

- Desinstalar o PWA antigo do Keep Training no celular, abrir `https://keep-focus.luvcode.tech` e instalar de novo pelo menu do navegador.
- Testar no celular: entrar com Google, iniciar um treino, concluir uma série (timer), fechar o app, retomar e finalizar; marcar um suplemento.
- A cada versão que mudar ícones, manifest ou a página offline, aumentar `CACHE_VERSION` em `public/sw.js`.
