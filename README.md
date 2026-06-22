# Třídní půjčovna

Symfony aplikace pro evidenci zapůjčení ve třídě. Ukázka pro výuku v 3. třídě ZŠ.

## Požadavky

- Docker + Docker Compose (doporučeno)
- nebo PHP 8.3+, Composer, běžící MySQL 8

## Spuštění

### Docker (doporučeno)

```bash
cd /home/ian/Documents/pujcovna-trida
docker compose up --build -d
```

| Služba | URL / port |
|--------|------------|
| Aplikace | http://localhost:8090 |
| MySQL | `localhost:3307` (uživatel `pujcovna` / heslo `pujcovna`, DB `pujcovna`) |

Zastavení: `docker compose down`  
Smazání dat MySQL: `docker compose down -v`

Při startu kontejner automaticky aktualizuje schéma (Doctrine) a založí admin účet.

**Vývoj bez restartu:** celý projekt je namapovaný do kontejneru (`APP_ENV=dev`). Změny v `src/`, `templates/`, `config/` a `public/` stačí obnovit stránku v prohlížeči.

| Co měníš | Co udělat |
|----------|-----------|
| PHP, Twig, YAML, CSS | jen F5 v prohlížeči |
| `composer.json` / `composer.lock` | `docker compose exec app composer install` |
| `Dockerfile` | `docker compose up --build -d` |

### Lokálně bez Dockeru

```bash
composer install
# uprav DATABASE_URL v .env (port 3307 pokud běží MySQL z Dockeru)
php bin/console doctrine:schema:update --force
php bin/console app:init-database
symfony server:start -d --port=8090
# nebo: php -S localhost:8090 -t public
```

## Výchozí účty

| Role | Přihlašovací jméno | Heslo |
|------|-------------------|-------|
| Administrátor | `admin` | `admin123` |

Učitelé se registrují sami na `/register`.

## Struktura (Symfony)

```
pujcovna-trida/
├── public/              # Web root (index.php, css, uploads)
├── src/
│   ├── Controller/      # Controllery s attribute routing
│   ├── Entity/          # Doctrine entity
│   ├── Repository/      # DQL dotazy
│   ├── Security/        # Voters (oprávnění k třídě)
│   └── Command/         # Konzolové příkazy
├── templates/           # Twig šablony
├── config/              # Symfony konfigurace
├── bin/console          # Symfony CLI
├── docker-compose.yml   # app + MySQL
└── Dockerfile
```

## Routování

Symfony používá **attribute routing** — každá akce controlleru má anotaci `#[Route(...)]`:

| URL | Route name | Popis |
|-----|------------|-------|
| `/login` | `app_login` | Přihlášení |
| `/register` | `app_register` | Registrace učitele |
| `/dashboard` | `app_dashboard` | Přehled tříd |
| `/trida/{id}` | `app_trida_detail` | Detail třídy |
| `/trida/nova` | `app_trida_create` | Nová třída |
| `/admin` | `app_admin` | Administrace |

Odkazy v šablonách: `{{ path('app_dashboard') }}`

## Bezpečnost

- **Symfony Security** — přihlášení formulářem, role `ROLE_ADMIN` / `ROLE_UCITEL`
- **TridaVoter** — učitel spravuje jen své třídy, admin vidí vše

## Databáze (MySQL + Doctrine ORM)

Připojení přes `DATABASE_URL` v `.env` / docker-compose.

```bash
docker compose exec mysql mysql -u pujcovna -ppujcovna pujcovna -e "SELECT * FROM trida;"
php bin/console doctrine:schema:update --force
php bin/console app:init-database
```

### Entity

| Třída | Tabulka | Vazby |
|-------|---------|-------|
| `App\Entity\Ucitel` | `ucitel` | 1:N → `Trida` |
| `App\Entity\Trida` | `trida` | N:1 → `Ucitel`, 1:N → `Zak`, `Vec` |
| `App\Entity\Zak` | `zak` | N:1 → `Trida` |
| `App\Entity\Vec` | `vec` | N:1 → `Trida` |
| `App\Entity\Zapujceni` | `zapujceni` | N:1 → `Vec`, `Zak` |

## Prezentace

Interaktivní průvodce tvorbou aplikace: http://localhost:8090/prezentace-tvorba.html

Původní obecná prezentace o programování: `/home/ian/Documents/programovani-3-trida/index.html`
