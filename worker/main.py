from __future__ import annotations

import argparse
import json
from pathlib import Path

from dotenv import load_dotenv

from gemini_client import GeminiClient


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Gera roteiro e imagens com Gemini.")
    parser.add_argument("--titulo", required=True, help="Título do assunto")
    parser.add_argument("--instrucoes", default="", help="Instruções adicionais")
    parser.add_argument("--imagens", type=int, default=5, help="Quantidade de imagens")
    parser.add_argument("--saida", default="worker/output", help="Diretório de saída")
    return parser.parse_args()


def main() -> None:
    args = parse_args()
    if args.imagens < 1 or args.imagens > 20:
        raise SystemExit("--imagens deve estar entre 1 e 20.")

    project_root = Path(__file__).resolve().parents[1]
    load_dotenv(project_root / ".env")
    output_dir = Path(args.saida)
    output_dir.mkdir(parents=True, exist_ok=True)

    client = GeminiClient()
    script = client.generate_script(args.titulo, args.instrucoes)
    (output_dir / "roteiro.txt").write_text(script, encoding="utf-8")

    image_paths: list[str] = []
    for index in range(1, args.imagens + 1):
        prompt = (
            f"Crie uma imagem vertical 9:16, sem texto e sem marcas d'água, para ilustrar o assunto: {args.titulo}. "
            f"A imagem {index} deve representar uma cena visual diferente, com estilo documental realista."
        )
        destination = output_dir / f"imagem_{index:02d}.png"
        client.generate_image(prompt, destination)
        image_paths.append(str(destination))

    manifest = {"titulo": args.titulo, "roteiro": str(output_dir / "roteiro.txt"), "imagens": image_paths}
    (output_dir / "manifest.json").write_text(json.dumps(manifest, ensure_ascii=False, indent=2), encoding="utf-8")
    print(json.dumps(manifest, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
