import os
import json
import google.generativeai as genai
import urllib.parse
from dotenv import load_dotenv

# Carga de configuración
load_dotenv()
api_key = os.getenv("GOOGLE_API_KEY")
pollinations_key = os.getenv("POLLINATIONS_API_KEY")

if api_key:
    genai.configure(api_key=api_key)

def generar_lista_desde_menu(prefs: dict):
    print(f"👨‍🍳 Chef iniciando petición para {prefs.get('num_personas', 2)} personas y {prefs.get('num_dias', 7)} días.")
    if not api_key:
        print("❌ Error: No hay API Key configurada.")
        return {"error": "Falta configurar la API Key del Chef"}

    # Modelos confirmados como vigentes en tu cuenta de Google
    modelos_a_probar = [
        'gemini-3-flash-preview',
        'gemini-2.5-flash',
        'gemini-2.0-flash'
    ]
    
    ultimo_error = ""

    restricciones = []
    if prefs.get("tipo_dieta", "omnívora") != "omnívora":
        restricciones.append(f"La dieta debe ser {prefs['tipo_dieta']}.")
    if prefs.get("intolerancias"):
        restricciones.append(f"El comensal tiene intolerancia a: {', '.join(prefs['intolerancias'])}. No incluyas estos ingredientes.")
    if prefs.get("alergias"):
        restricciones.append(f"ALERGIA (CRÍTICO): {', '.join(prefs['alergias'])}. Estos ingredientes están PROHIBIDOS.")
    if prefs.get("no_me_gusta"):
        restricciones.append(f"No le gustan: {', '.join(prefs['no_me_gusta'])}. Evítalos.")
    if prefs.get("me_gusta"):
        restricciones.append(f"Le gustan especialmente: {', '.join(prefs['me_gusta'])}. Inclúyelos cuando sea apropiado.")
    
    objetivo = prefs.get("objetivo", "equilibrado")
    if objetivo == "perder peso":
        restricciones.append("Enfoca los platos en ser bajos en calorías, ricos en proteínas y fibra.")
    elif objetivo == "ganar masa muscular":
        restricciones.append("Enfoca los platos en ser ricos en proteínas y carbohidratos complejos.")
    elif objetivo == "económico" or objetivo == "Ahorro":
        restricciones.append("Usa ingredientes baratos y de temporada. Reutiliza ingredientes entre comidas para evitar desperdicio.")

    comidas = "desayuno, comida y cena"
    if prefs.get("incluir_snacks"):
        comidas = "desayuno, media mañana, comida, merienda y cena"

    restricciones_txt = "\n".join(f"- {r}" for r in restricciones) if restricciones else "- Sin restricciones especiales."
    prompt_usuario_txt = f"\nPETICIÓN ADICIONAL DEL USUARIO: {prefs.get('prompt_usuario')}\n" if prefs.get("prompt_usuario") else ""

    num_dias = prefs.get('num_dias', 7)
    num_personas = int(prefs.get('num_personas', 2))
    
    prompt = f"""Eres un nutricionista experto. Genera un menú planificado para {num_dias} días consecutivos para {num_personas} persona(s).

COMIDAS POR DÍA: {comidas}

RESTRICCIONES Y PREFERENCIAS:
{restricciones_txt}
{prompt_usuario_txt}

INSTRUCCIONES DE FORMATO:
Responde EXCLUSIVAMENTE con un JSON válido (sin texto adicional, sin markdown, sin explicaciones).
El JSON debe tener esta estructura exacta:

{{
  "menu_planificado": {{
    "dia_1": {{
      "desayuno": {{
        "plato": "nombre del plato",
        "ingredientes": [
            {{"nombre": "nombre limpio", "cantidad_persona": 50, "unidad": "g"}},
            {{"nombre": "nombre limpio", "cantidad_persona": 1, "unidad": "ud"}}
        ],
        "prompt_visual": "Detailed English description. Mention specific ingredients and textures."
      }},
      "comida": {{ ... }},
      "cena": {{ ... }}
    }},
    "dia_2": {{ ... }}
  }}
}}

REGLAS CRÍTICAS PARA LOS INGREDIENTES:
1. "nombre": DEBE SER TOTALMENTE GENÉRICO y en SINGULAR (ej: "pechuga de pollo", "arroz redondo", "tomate"). NUNCA incluyas cantidades, marcas ni estados (ej: "100g de arroz" es incorrecto, debe ser "arroz").
2. "cantidad_persona": Un número entero que represente la ración nutricional adecuada para UNA SOLA PERSONA en esta receta específica.
3. "unidad": Solo puedes usar "g", "ml", o "ud".
4. Asegúrate de que las raciones sean coherentes con el objetivo del usuario (ej: raciones más grandes si es para ganar masa muscular).
5. Para 'prompt_visual', genera una DESCRIPCIÓN EXTREMADAMENTE DETALLADA en INGLÉS para generar una foto de comida profesional.
"""

    for nombre_modelo in modelos_a_probar:
        try:
            print(f"🔄 Intentando conectar con el Chef: {nombre_modelo}...")
            model = genai.GenerativeModel(nombre_modelo)

            response = model.generate_content(prompt)
            print(f"✅ Respuesta recibida de {nombre_modelo}. Procesando JSON...")
            
            texto_limpio = response.text.replace("```json", "").replace("```", "").strip()
            
            if "{" in texto_limpio:
                texto_limpio = texto_limpio[texto_limpio.find("{"):texto_limpio.rfind("}")+1]

            datos = json.loads(texto_limpio)
            
            menu_formateado = []
            conteo_ingredientes = {} # Para la lista de la compra consolidada
            
            dias = datos.get("menu_planificado", datos.get("menu_semanal", datos))
            if not isinstance(dias, dict):
                raise ValueError("Formato de JSON invalido devuelto por el modelo.")

            for dia, comidas_dia in dias.items():
                if isinstance(comidas_dia, dict):
                    for momento, detalle in comidas_dia.items():
                        if isinstance(detalle, dict):
                            plato = detalle.get("plato", "")
                            ingredientes_datos = detalle.get("ingredientes", [])
                            
                            ing_con_cantidad_str = []
                            for ing_obj in ingredientes_datos:
                                if not isinstance(ing_obj, dict): continue
                                
                                nombre = ing_obj.get("nombre", "Ingrediente").lower().strip()
                                cant_persona = ing_obj.get("cantidad_persona", 0)
                                unidad = ing_obj.get("unidad", "g")
                                
                                # Calculamos total para el plato según personas
                                cant_total_plato = cant_persona * num_personas
                                ing_con_cantidad_str.append(f"{nombre} ({cant_total_plato}{unidad})")
                                
                                # --- Consolidación para Lista de Compra ---
                                # Normalización básica para evitar duplicados por plurales o erratas
                                key = nombre
                                if key.endswith('s') and not key.endswith('es') and key != "arroz":
                                    key = key.rstrip('s')
                                
                                mapeo_unificar = {
                                    "lenteja": "lentejas", "garbanzo": "garbanzos", "huevo": "huevos",
                                    "aceituna": "aceitunas", "nuece": "nueces", "nuez": "nueces"
                                }
                                key = mapeo_unificar.get(key, key)

                                if key not in conteo_ingredientes:
                                    conteo_ingredientes[key] = {"cantidad": 0, "unidad": unidad, "usos": 0}
                                
                                conteo_ingredientes[key]["cantidad"] += cant_total_plato
                                conteo_ingredientes[key]["usos"] += 1

                            visual_prompt = detalle.get("prompt_visual", f"Professional food photography of {plato}")
                            prompt_encoded = urllib.parse.quote(visual_prompt)
                            import random
                            seed = random.randint(1, 100000)
                            url_ia = f"/api_proxy_ia.php?prompt={prompt_encoded}&seed={seed}"
                            
                            menu_formateado.append({
                                "dia": f"{dia.capitalize()} - {momento.capitalize()}",
                                "plato": plato,
                                "descripcion": f"Ingredientes: {', '.join(ing_con_cantidad_str)}",
                                "imagen": url_ia
                            })
            
            # Convertimos conteo_ingredientes al formato que espera el frontend
            ingredientes_clave = []
            for nombre, info in sorted(conteo_ingredientes.items()):
                ingredientes_clave.append({
                    "nombre": nombre,
                    "cantidad": info["cantidad"],
                    "unidad": info["unidad"],
                    "frecuencia_menu": info["usos"]
                })

            resultado_final = {
                "menu_pensado": menu_formateado,
                "ingredientes_clave": ingredientes_clave
            }
            
            print(f"✅ ¡Éxito con el modelo {nombre_modelo}! Menu generado con raciones IA.")
            return resultado_final

        except Exception as e:
            print(f"⚠️ Falló {nombre_modelo}: {e}")
            ultimo_error = str(e)
            continue

    return {"error": f"El Chef no está disponible. Último error: {ultimo_error}"}

if __name__ == "__main__":
    print("👨‍🍳 El Chef está encendiendo los fogones con IA pura...")
    resultado = generar_lista_desde_menu({
        "prompt_usuario": "Menú saludable 3 días",
        "num_personas": 2
    })
    print(json.dumps(resultado, indent=2, ensure_ascii=False))