from __future__ import annotations

import base64
import os
from pathlib import Path

from google import genai


class GeminiClient:
    def __init__(self) -> None:
        api_key = os.getenv("GEMINI_API_KEY")
        self.text_model = os.getenv("GEMINI_TEXT_MODEL") or "gemini-3.8-flash"
        self.image_model = os.getenv("GEMINI_IMAGE_MODEL") or "gemini-nano-banana-2.1"
        if not api_key:
            raise RuntimeError("GEMINI_API_KEY não foi configurada.")
        if self.text_model == "modelo_de_texto" or self.image_model == "modelo_de_imagem":
            raise RuntimeError("Substitua os placeholders GEMINI_TEXT_MODEL e GEMINI_IMAGE_MODEL por modelos reais ou deixe as variáveis vazias para usar os padrões.")
        self.client = genai.Client(api_key=api_key)

    def generate_script(self, title: str, instructions: str | None) -> str:
        prompt = (
            "Crie um roteiro original para um vídeo curto vertical em português do Brasil.\n"
            f"Assunto: {title}\n"
            f"Instruções adicionais: {instructions or 'nenhuma'}\n\n"
            "Retorne apenas o roteiro, com gancho inicial, desenvolvimento e encerramento. "
            "Não invente fontes, números ou citações verificáveis."
        )
        response = self.client.models.generate_content(model=self.text_model, contents=prompt)
        text = (response.text or "").strip()
        if not text:
            raise RuntimeError("O Gemini não retornou um roteiro.")
        return text

    def generate_image(self, prompt: str, destination: Path) -> None:
        interaction = self.client.interactions.create(model=self.image_model, input=prompt)
        image = getattr(interaction, "output_image", None)
        data = getattr(image, "data", None)
        if not data:
            raise RuntimeError("O Gemini não retornou uma imagem.")
        destination.write_bytes(base64.b64decode(data))
