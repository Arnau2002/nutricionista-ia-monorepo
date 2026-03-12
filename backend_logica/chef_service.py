import os
import json
import google.generativeai as genai
from dotenv import load_dotenv

# Carga de configuración
load_dotenv()
api_key = os.getenv("GOOGLE_API_KEY")

if api_key:
    genai.configure(api_key=api_key)

def generar_lista_desde_menu(
    instruccion_usuario: str, 
    dieta: str = "Equilibrada", 
    alergias: list = [], 
    objetivo: str = "Ahorro"
):
    if not api_key:
        return {"error": "Falta configurar la API Key del Chef"}

    # Modelos recomendados para evitar obsolescencia
    modelos_a_probar = [
        'gemini-2.5-flash', 
        'gemini-3.1-flash-lite-preview',
        'gemini-3-flash-preview',
        'gemini-2.5-pro',
        'gemini-1.5-flash'
    ]
    
    ultimo_error = ""

    # Preparar el contexto de restricciones
    restricciones = ""
    if dieta != "Equilibrada":
        restricciones += f"- TIPO DE DIETA: {dieta}. NUNCA propongas platos que no la cumplan.\n"
    if alergias:
        restricciones += f"- ALERGIAS (PROHIBIDO): {', '.join(alergias)}. SI UN PLATO SUELE LLEVAR ESTO, SUSTITÚYELO O ELIMÍNALO.\n"
    if objetivo != "Ahorro":
        restricciones += f"- OBJETIVO NUTRICIONAL: {objetivo}. Prioriza ingredientes que ayuden a este fin.\n"

    for nombre_modelo in modelos_a_probar:
        try:
            print(f"🔄 Intentando conectar con el Chef: {nombre_modelo}...")
            model = genai.GenerativeModel(nombre_modelo)

            prompt = f"""
            Eres un Nutricionista y Chef especializado en AHORRO inteligente para jóvenes independizados.
            
            CONTEXTO DEL USUARIO:
            - Petición: "{instruccion_usuario}"
            {restricciones}
            
            TU MISIÓN:
            1. Generar un menú realista que sea BARATO y cumpla con las restricciones anteriores.
            2. Calcular la cantidad necesaria (en gramos o unidades estándar) para UNA persona.
            
            REGLAS DE VOCABULARIO (SÓLO NOMBRES BÁSICOS):
            - Usa el nombre base del producto (una o dos palabras máximo).
            - NUNCA uses adjetivos, variedades, marcas ni tipos de corte/cocción.
            - EJEMPLOS: "Pollo", "Lentejas", "Arroz".
            - PROHIBIDOS: "Pollo campero", "Lentejas pardinas", "Arroz bomba".
            
            RESPONDE ÚNICAMENTE CON ESTE FORMATO JSON:
            {{
                "menu_pensado": [
                    {{ "dia": "Lunes - Cena", "plato": "Nombre del plato", "descripcion": "Breve descripción con enfoque en ahorro" }}
                ],
                "ingredientes_clave": [
                    {{ "nombre": "Nombre Simple", "cantidad": 500 }}
                ]
            }}
            """

            response = model.generate_content(prompt)
            
            # Limpieza
            texto_limpio = response.text.replace("```json", "").replace("```", "").strip()
            
            # Intento de corrección de JSON sucio
            if "{" in texto_limpio:
                texto_limpio = texto_limpio[texto_limpio.find("{"):texto_limpio.rfind("}")+1]

            datos = json.loads(texto_limpio)
            print(f"📦 Ingredientes generados: {[i['nombre'] for i in datos.get('ingredientes_clave', [])]}")
            
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

# Test de integración rápida
if __name__ == "__main__":
    print("👨‍🍳 El Chef está encendiendo los fogones...")
    resultado = generar_lista_desde_menu("Quiero cenar ligero 3 días, nada de carne")
    print(json.dumps(resultado, indent=2, ensure_ascii=False))