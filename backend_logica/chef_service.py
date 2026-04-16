import os
import json
import google.generativeai as genai
from dotenv import load_dotenv

# Carga de configuración
load_dotenv()
api_key = os.getenv("GOOGLE_API_KEY")

if api_key:
    genai.configure(api_key=api_key)

def estimar_cantidad_base(ingrediente: str) -> tuple[int, str]:
    import unicodedata
    # Para ser robustos con acentos
    ing = unicodedata.normalize('NFD', ingrediente).encode('ascii', 'ignore').decode("utf-8").lower().strip()

    if any(x in ing for x in ["pollo", "pavo", "ternera", "cerdo", "atun", "salmon", "merluza", "pescado", "tofu", "carne", "lomo"]):
        return 150, "g"
    if any(x in ing for x in ["arroz", "pasta", "lenteja", "garbanzo", "alubia", "quinoa"]):
        return 80, "g"
    if any(x in ing for x in ["tomate", "cebolla", "zanahoria", "calabacin", "pimiento", "brocoli", "verdura", "lechuga", "espinaca", "champiñon", "seta"]):
        return 120, "g"
    if any(x in ing for x in ["patata", "boniato"]):
        return 180, "g"
    if any(x in ing for x in ["huevo"]):
        return 1, "ud"
    if any(x in ing for x in ["leche", "bebida", "caldo", "zumo"]):
        return 250, "ml"
    if any(x in ing for x in ["aceite", "vinagre", "salsa", "soja"]):
        return 15, "ml"
    if any(x in ing for x in ["sal", "pimenton", "especia", "oregano", "canela", "pimienta", "perejil", "ajo", "laurel", "romero", "avena", "harina", "azucar"]):
        return 5, "g"
    if any(x in ing for x in ["pan", "tostada"]):
        return 80, "g"
    if any(x in ing for x in ["yogur"]):
        return 1, "ud"
    if any(x in ing for x in ["manzana", "platano", "pera", "naranja", "aguacate", "kiwi", "limon"]):
        return 1, "ud"

    return 1, "ud"

def estimar_cantidad_total(ingrediente: str, frecuencia: int, num_personas: int) -> int:
    base, _ = estimar_cantidad_base(ingrediente)
    total = base * max(1, frecuencia) * max(1, num_personas)
    return int(total)

def generar_lista_desde_menu(prefs: dict):
    if not api_key:
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
    prompt = f"""Eres un nutricionista experto. Genera un menú planificado para {num_dias} días consecutivos para {prefs.get('num_personas', 2)} persona(s).

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
    "dia_2": {{ ... }}
  }}
}}

REGLAS PARA LOS INGREDIENTES:
1. Usa nombres genéricos de producto (ej: "leche entera", "pechuga de pollo", "arroz", "tomate")
2. NO incluyas marcas comerciales.
3. Los ingredientes deben ser productos que se encuentren en un supermercado español.
4. Varía los platos a lo largo de la semana.
5. Asegúrate de que sea nutricionalmente equilibrado.
6. Reutiliza ingredientes en varios platos de la semana para optimizar la compra (ej: un mismo arroz, verduras base, proteina base).
7. Evita ingredientes hiper-específicos de un solo uso si hay alternativas equivalentes ya presentes en el menú.
8. NO seas tan creativo con la creación de los menús, hazlos más generales y típicos. Tampoco que siempre sean los mismos.

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
            dias = datos.get("menu_planificado", datos.get("menu_semanal", datos))
            if not isinstance(dias, dict):
                raise ValueError("Formato de JSON invalido devuelto por el modelo (no contiene menu_planificado).")

            for dia, comidas_dia in dias.items():
                if isinstance(comidas_dia, dict):
                    for momento, detalle in comidas_dia.items():
                        if isinstance(detalle, dict):
                            plato = detalle.get("plato", "")
                            ingredientes_plato = detalle.get("ingredientes", [])
                            # Agregamos al listado universal para la lista de la compra
                            todos_ingredientes.extend(ingredientes_plato)
                            
                            # Imprimimos cantidad visual para la UI del Menú
                            ing_con_cantidad = []
                            for ing in ingredientes_plato:
                                base, uni = estimar_cantidad_base(ing)
                                cant = base * max(1, int(prefs.get("num_personas", 2)))
                                ing_con_cantidad.append(f"{ing} ({cant}{uni})")
                                
                            # Agregamos al formato de UI esperado
                            menu_formateado.append({
                                "dia": f"{dia.capitalize()} - {momento.capitalize()}",
                                "plato": plato,
                                "descripcion": f"Ingredientes: {', '.join(ing_con_cantidad)}"
                            })
            
            # Consolidación de ingredientes: preservamos frecuencia para estimar compra semanal
            conteo_ingredientes = {}
            for i in todos_ingredientes:
                if isinstance(i, str) and i.strip():
                    key = i.strip().lower()
                    conteo_ingredientes[key] = conteo_ingredientes.get(key, 0) + 1

            ingredientes_clave = []
            for ing in sorted(conteo_ingredientes.keys()):
                frecuencia = conteo_ingredientes.get(ing, 1)
                base, uni = estimar_cantidad_base(ing)
                total = estimar_cantidad_total(ing, frecuencia, int(prefs.get("num_personas", 2)))
                ingredientes_clave.append({
                    "nombre": ing,
                    "cantidad": total,
                    "unidad": uni,
                    "frecuencia_menu": frecuencia
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