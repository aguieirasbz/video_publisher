# Worker Gemini

Este worker gera somente roteiro e imagens. Ele ainda não monta vídeos, não chama FFmpeg e não publica conteúdo.

## Configuração

Na raiz do projeto, configure no `.env`:

```env
GEMINI_API_KEY=sua_chave
GEMINI_TEXT_MODEL=gemini-3.8-flash
GEMINI_IMAGE_MODEL=gemini-nano-banana-2.1
```

Instale as dependências:

```powershell
python -m pip install -r worker/requirements.txt
```

Execute um teste:

```powershell
python worker/main.py --titulo "Curiosidades sobre tecnologia" --imagens 3
```

Os arquivos serão gravados em `worker/output/`. Não versionar esse diretório nem a chave da API.
