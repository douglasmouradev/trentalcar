# Site estático (legado) — **DEPRECATED**

> **Não use em produção.** Os HTML estáticos (`index.html`, `login.html`, etc.) foram
> removidos; esta pasta mantém-se apenas como marcador histórico no repositório.

O site oficial é a aplicação PHP em `public/`:

```bash
php -S localhost:8888 -t public public/router.php
```

## Rotas públicas (fonte de verdade)

| Antigo (site/) | Actual (public/) |
|----------------|------------------|
| `index.html` | `GET /` |
| formulário offline | `GET /reservar`, `POST /lead` |
| — | `GET /consultar` |
| `privacidade.html` | `GET /privacidade` |
| `termos.html` | `GET /termos` |
| `login.html` | `GET /login` |

## Remoção futura

A pasta pode ser eliminada num major release quando ninguém depender dela.
Até lá, **não adicione** novos ficheiros aqui — use `app/views/landing/` e `public/landing/`.
