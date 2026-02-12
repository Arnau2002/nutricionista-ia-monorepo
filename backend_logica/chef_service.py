import os
import json
import google.generativeai as genai
from dotenv import load_dotenv

# 1. Cargar la llave del archivo .env
load_dotenv()
api_key = os.getenv("GOOGLE_API_KEY")

# Configurar Gemini
if not api_key:
    print("⚠️ ALERTA: No se encontró GOOGLE_API_KEY en el archivo .env")
else:
    genai.configure(api_key=api_key)

def generar_lista_desde_menu(instruccion_usuario: str):
    if not api_key:
        return {"error": "Falta configurar la API Key del Chef"}

    # LISTA DE MODELOS A PROBAR (Del más moderno al más compatible)
    # Si falla el primero, probará el segundo, etc.
    modelos_a_probar = [
        'gemini-1.5-flash',       # El estándar gratuito actual (Rápido y bueno)
        'gemini-1.5-flash-latest',# Alias alternativo
        'gemini-pro',             # El clásico (Suele funcionar siempre)
        'gemini-flash-latest'     # El alias genérico
    ]
    
    ultimo_error = ""

    for nombre_modelo in modelos_a_probar:
        try:
            print(f"🔄 Intentando conectar con el Chef: {nombre_modelo}...")
            model = genai.GenerativeModel(nombre_modelo)

            prompt = f"""
            Eres un Nutricionista y Chef experto. 
            El usuario quiere: "{instruccion_usuario}".
            
            Tu misión:
            1. Generar un menú realista basado en esa petición.
            2. Extraer la lista de compra con ingredientes ESENCIALES y genéricos (ej: "Arroz", no "Arroz marca X").
            3. Cantidades aproximadas para una persona.
            
            IMPORTANTE: Responde ÚNICAMENTE con un JSON válido con esta estructura, sin texto extra ni markdown (no uses ```json):
            {{
                "menu_pensado": [
                    {{ "dia": "Día 1", "plato": "Nombre del plato", "descripcion": "Breve descripción" }}
                ],
                "ingredientes_clave": ["Ingrediente 1", "Ingrediente 2", "Ingrediente 3"]
            }}
            """

            response = model.generate_content(prompt)
            
            # Limpieza
            texto_limpio = response.text.replace("```json", "").replace("```", "").strip()
            
            # Intento de corrección de JSON sucio
            if "{" in texto_limpio:
                texto_limpio = texto_limpio[texto_limpio.find("{"):texto_limpio.rfind("}")+1]

            datos = json.loads(texto_limpio)
            
            print(f"✅ ¡Éxito con el modelo {nombre_modelo}!")
            return datos

        except Exception as e:
            # Si falla, guardamos el error y probamos el siguiente modelo
            print(f"⚠️ Falló {nombre_modelo}: {e}")
            ultimo_error = str(e)
            continue

    # Si llegamos aquí, fallaron todos
    print("❌ Todos los modelos fallaron.")
    return {"error": f"El Chef no está disponible. Último error: {ultimo_error}"}

# --- PRUEBA RÁPIDA ---
if __name__ == "__main__":
    print("👨‍🍳 El Chef está encendiendo los fogones...")
    resultado = generar_lista_desde_menu("Quiero cenar ligero 3 días, nada de carne")
    print(json.dumps(resultado, indent=2, ensure_ascii=False))