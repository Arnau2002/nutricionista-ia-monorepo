import os
import json
import google.generativeai as genai
from dotenv import load_dotenv

# Carga de configuración
load_dotenv()
api_key = os.getenv("GOOGLE_API_KEY")

if api_key:
    genai.configure(api_key=api_key)

def generar_lista_desde_menu(prefs: dict):
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

    prompt = f"""Eres un nutricionista experto. Genera un menú semanal completo (lunes a domingo) para {prefs.get('num_personas', 2)} persona(s).

COMIDAS POR DÍA: {comidas}

RESTRICCIONES Y PREFERENCIAS:
{restricciones_txt}
{prompt_usuario_txt}

INSTRUCCIONES DE FORMATO:
Responde EXCLUSIVAMENTE con un JSON válido (sin texto adicional, sin markdown, sin explicaciones).
El JSON debe tener esta estructura exacta:

{{
  "menu_semanal": {{
    "lunes": {{
      "desayuno": {{
        "plato": "nombre del plato",
        "ingredientes": ["ingrediente1", "ingrediente2"]
      }},
      "comida": {{
        "plato": "nombre del plato",
        "ingredientes": ["ingrediente1", "ingrediente2"]
      }},
      "cena": {{
        "plato": "nombre del plato",
        "ingredientes": ["ingrediente1", "ingrediente2"]
      }}
    }},
    "martes": {{ ... }}
  }}
}}

REGLAS PARA LOS INGREDIENTES:
1. Usa nombres genéricos de producto (ej: "leche entera", "pechuga de pollo", "arroz", "tomate")
2. NO incluyas cantidades en los ingredientes. Solo el nombre del ingrediente clave.
3. NO incluyas marcas comerciales.
4. Los ingredientes deben ser productos que se encuentren en un supermercado español.
5. Varía los platos a lo largo de la semana.
6. Asegúrate de que sea nutricionalmente equilibrado.

Responde SOLO con el JSON:"""

    for nombre_modelo in modelos_a_probar:
        try:
            print(f"🔄 Intentando conectar con el Chef: {nombre_modelo}...")
            model = genai.GenerativeModel(nombre_modelo)

            response = model.generate_content(prompt)
            
            # Limpieza
            texto_limpio = response.text.replace("```json", "").replace("```", "").strip()
            
            # Intento de corrección de JSON sucio
            if "{" in texto_limpio:
                texto_limpio = texto_limpio[texto_limpio.find("{"):texto_limpio.rfind("}")+1]

            datos = json.loads(texto_limpio)
            
            # ----------------------------------------------------
            # EXTRACCIÓN DE INGREDIENTES ÚNICOS (Lógica de algoritmo.ipynb)
            # ----------------------------------------------------
            todos_ingredientes = []
            menu_formateado = []
            
            # Formateamos el menu para la compatibilidad con el frontend ("menu_pensado")
            dias = datos.get("menu_semanal", datos)
            if not isinstance(dias, dict):
                raise ValueError("Formato de JSON invalido devuelto por el modelo (no contiene menu_semanal).")

            for dia, comidas_dia in dias.items():
                if isinstance(comidas_dia, dict):
                    for momento, detalle in comidas_dia.items():
                        if isinstance(detalle, dict):
                            plato = detalle.get("plato", "")
                            ingredientes_plato = detalle.get("ingredientes", [])
                            # Agregamos al listado universal para la lista de la compra
                            todos_ingredientes.extend(ingredientes_plato)
                            # Agregamos al formato de UI esperado
                            menu_formateado.append({
                                "dia": f"{dia.capitalize()} - {momento.capitalize()}",
                                "plato": plato,
                                "descripcion": f"Ingredientes: {', '.join(ingredientes_plato)}"
                            })
            
            # Normalizar y eliminar duplicados
            ingredientes_unicos = sorted(set(i.strip().lower() for i in todos_ingredientes if isinstance(i, str) and i.strip()))
            
            ingredientes_clave = []
            for ing in ingredientes_unicos:
                ingredientes_clave.append({
                    "nombre": ing,
                    "cantidad": 1 # Generico, la comparacion de precios ya lo normaliza o usa el mínimo.
                })

            resultado_final = {
                "menu_pensado": menu_formateado,
                "ingredientes_clave": ingredientes_clave
            }
            
            print(f"📦 Ingredientes únicos generados: {len(ingredientes_clave)}")
            print(f"✅ ¡Éxito con el modelo {nombre_modelo}!")
            return resultado_final

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
    resultado = generar_lista_desde_menu({
        "prompt_usuario": "Quiero cenar ligero 3 días, nada de carne",
        "num_personas": 2
    })
    print(json.dumps(resultado, indent=2, ensure_ascii=False))