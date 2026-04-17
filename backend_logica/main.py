import os
import unicodedata
import statistics
import re 
import math
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import List, Optional
from qdrant_client import QdrantClient
from qdrant_client.http import models
from sentence_transformers import SentenceTransformer

from chef_service import generar_lista_desde_menu

app = FastAPI(title="Nutricionista IA")

# CORS 
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# INICIO SERVICIOS 
QDRANT_HOST = os.getenv('QDRANT_HOST', 'vector-db')
QDRANT_PORT = 6333
client = None
model = None

try:
    client = QdrantClient(host=QDRANT_HOST, port=QDRANT_PORT)
    client.get_collections()
    model = SentenceTransformer('paraphrase-multilingual-MiniLM-L12-v2')
    print("✅ Sistema Nutricionista IA Online.")
except Exception as e:
    print(f"❌ Error crítico al conectar con Qdrant: {e}")
    try:
        print("🔄 Reintentando conexión en localhost...")
        client = QdrantClient(host='localhost', port=6333)
        client.get_collections()
        model = SentenceTransformer('paraphrase-multilingual-MiniLM-L12-v2')
        print("✅ Conexión recuperada en Localhost.")
    except:
        print("💀 Imposible conectar a la Base de Datos Vectorial.")

# MODELOS 
class ItemBusqueda(BaseModel):
    ingrediente: str

class ListaCompraRequest(BaseModel):
    ingredientes: List[dict | str]
    dieta: Optional[str] = "Equilibrada"
    alergias: Optional[List[str]] = []
    objetivo: Optional[str] = "Ahorro"
    ingredientes_en_casa: Optional[List[str]] = []

class MenuRequest(BaseModel):
    prompt: str = "" # Hacemos opcional el prompt si enviamos preferencias, pero de momento conservamos por compati.
    prompt_usuario: Optional[str] = ""
    num_personas: Optional[int] = 2
    num_dias: Optional[int] = 7
    tipo_dieta: Optional[str] = "omnívora"
    intolerancias: Optional[List[str]] = []
    alergias: Optional[List[str]] = []
    no_me_gusta: Optional[List[str]] = []
    me_gusta: Optional[List[str]] = []
    objetivo: Optional[str] = "equilibrado"
    incluir_snacks: Optional[bool] = False
    ingredientes_en_casa: Optional[List[str]] = []

class ComparativaFinal(BaseModel):
    mejor_supermercado: str
    ahorro_total: float
    cesta_mercadona: dict
    cesta_dia: dict
    filas: List[dict] = []
    mensaje_ahorro: str = ""
    comparativa_completa: bool = True

from diccionarios_semanticos import *

# HERRAMIENTAS 
def normalizar(texto: str) -> str:
    if not texto: return ""
    texto = unicodedata.normalize('NFD', texto).encode('ascii', 'ignore').decode("utf-8")
    return texto.lower().strip()

def limpiar_ingrediente_avanzado(texto: str) -> str:
    if not texto: return ""
    
    # 1. Normalización base y minúsculas
    texto = normalizar(texto)
    
    # 2. Eliminar puntuación (comas, puntos, etc.)
    texto = re.sub(r'[,\.;:]', ' ', texto)
    
    # 3. Eliminar paréntesis y su contenido
    texto = re.sub(r'\(.*?\)', '', texto)
    
    # 4. Eliminar patrones de cantidad (ej: 1kg, 200g, 1 litro, 1l)
    # Buscamos números seguidos de unidades
    unidades = r'(g|kg|ml|l|litro|litros|unidades|ud|docena|paquete|bote)'
    texto = re.sub(r'\d+\s*' + unidades + r'\b', ' ', texto)
    texto = re.sub(r'\d+\b', ' ', texto) # Eliminar números sueltos
    
    # 5. Eliminar palabras de ruido específicas
    for palabra in PALABRAS_A_IGNORAR:
        # Usamos regex para asegurar que borramos palabras completas
        texto = re.sub(r'\b' + re.escape(palabra) + r'\b', ' ', texto)
    
    # 6. Limpieza final de espacios
    texto = re.sub(r'\s+', ' ', texto).strip()
    
    return texto

def tokenizar(texto: str) -> list:
    stopwords = {"de", "del", "el", "la", "los", "las", "en", "y", "o", "a", "para", "con", "sin", "pack", "bandeja", "g", "kg", "l", "ml", "litro", "brik", "botella", "al", "del", "lo", "un", "una"}
    palabras = normalizar(texto).split()
    tokens = []
    for p in palabras:
        if len(p) >= 2 and p not in stopwords:
            # Eliminamos plurales comunes sin destrozar la palabra, excepto casos como nuez/nueces
            if p == 'nueces':
                p = 'nuez'
            elif p.endswith('s') and len(p) > 3 and not p.endswith('ss'):
                p = p[:-1]
            tokens.append(p)
    return tokens

def normalizar_lista_textos(items: Optional[List[str]]) -> set:
    if not items:
        return set()
    return {normalizar(x) for x in items if isinstance(x, str) and normalizar(x)}

def filtrar_ingredientes_en_casa(lista_ingredientes: List[any], ingredientes_en_casa: Optional[List[str]]) -> tuple[list, list]:
    """Elimina ingredientes que el usuario ya tiene para recalcular la cesta real."""
    despensa = normalizar_lista_textos(ingredientes_en_casa)
    if not despensa:
        return lista_ingredientes, []

    filtrados = []
    excluidos = []
    
    # Palabras que cambian totalmente el sentido del producto y evitan "falsos positivos" de despensa
    # Ej: Si tengo "arroz" en casa, NO debo descartar el "vinagre de arroz" que pida la IA.
    protectores = {"vinagre", "caldo", "harina", "bebida", "salsa", "crema", "lata", "bote", "coco", "almendra", "soja", "avena"}

    for ing in lista_ingredientes:
        nombre = ing.get("nombre", "") if isinstance(ing, dict) else str(ing)
        nombre_norm = normalizar(nombre)
        if not nombre_norm: continue
        
        nombre_tokens = set(nombre_norm.split())
        excluir = False
        
        for desp_item in despensa:
            desp_tokens = set(desp_item.split())
            if not desp_tokens: continue
            
            # Caso 1: El usuario puso "aceite", la IA generó "aceite oliva" (desp_tokens es subconjunto de nombre_tokens)
            if desp_tokens.issubset(nombre_tokens):
                interseccion_proteccion = protectores.intersection(nombre_tokens)
                # Si el ingrediente de la IA tiene una palabra protectora (ej. "vinagre") que NO puso el usuario (ej. solo puso "arroz")
                if interseccion_proteccion and not interseccion_proteccion.intersection(desp_tokens):
                    continue # Protegemos el artículo, no lo excluimos
                excluir = True
                break
                
            # Caso 2: El usuario puso "aceite de oliva extra", la IA generó "aceite" o "aceite oliva" (nombre_tokens es subconjunto)
            if nombre_tokens.issubset(desp_tokens):
                excluir = True
                break

        if excluir:
            excluidos.append(nombre)
        else:
            filtrados.append(ing)
            
    return filtrados, excluidos

# Lógica de Scoring
def calcular_score_v15(producto: dict, query_original: str, alergias: list = None) -> float:
    nombre_prod = normalizar(producto['nombre'])
    categoria_prod = producto.get('categoria', '')
    
    # Filtro de Alergias
    if alergias:
        for alergia in alergias:
            alergia_clean = alergia.lower().strip()
            keywords = MAPEO_ALERGIAS.get(alergia_clean, [alergia_clean])
            if any(k in nombre_prod for k in keywords):
                return 0.0

    # 1. FILTRO DE PALABRAS PROHIBIDAS
    for prohibida in PALABRAS_PROHIBIDAS_GLOBAL:
        if prohibida in nombre_prod: 
            return 0.0

    # Filtro de categorías no alimentarias
    if any(cp in categoria_prod.lower() for cp in CATEGORIAS_PROHIBIDAS):
        return 0.0

    query_tokens = tokenizar(query_original)
    prod_tokens = tokenizar(nombre_prod)
    
    if not query_tokens or not prod_tokens: return 0.0

    # Expansión de sinónimos Multi-Supermercado (Eje: Dia vs Mercadona)
    # Buscamos coincidencias de texto completo también.
    query_tokens_extendidos = list(query_tokens)
    query_join = " ".join(query_tokens)
    for sin_key, sin_list in SINONIMOS_PRODUCTOS.items():
        if sin_key in query_join:
            for sin in sin_list:
                query_tokens_extendidos.extend(tokenizar(sin))
                
    query_tokens = query_tokens_extendidos
    q_set = set(query_tokens)
    p_set = set(prod_tokens)
    
    # Match Robusto: Palabras obligatorias (Flexibilidad con plurales)
    for ob in OBLIGATORIAS_GLOBAL:
        if ob in q_set:
            if not any(ob == p_t.rstrip('s') or ob == p_t.rstrip('es') or ob == p_t for p_t in prod_tokens):
                return 0.0

    match_absoluto = False
    for q_t in query_tokens:
        for p_t in prod_tokens:
            if q_t == p_t or q_t.rstrip('s') == p_t.rstrip('s') or q_t.rstrip('es') == p_t.rstrip('es'):
                match_absoluto = True
                break
        if match_absoluto: break
    
    # Match por fragmentos (prefijos)
    match_fragmento = False
    for q_t in query_tokens:
        if len(q_t) >= 4:
            for p_t in prod_tokens:
                if len(p_t) >= 4: # v37.1: Evita que 'al' de 'al punto' matchee con 'alga'
                    if p_t.startswith(q_t) or q_t.startswith(p_t):
                        match_fragmento = True
                        break
        if match_fragmento: break
    
    # Si no hay match de palabras, el score es 0
    if not match_absoluto and not match_fragmento:
        return 0.0

    # Ajuste de confianza por match parcial
    multiplicador_match = 1.0
    if not match_absoluto and match_fragmento:
        # Si solo es prefijo, bajamos la confianza
        multiplicador_match = 0.5
    main_word = query_tokens[0]
    try:
        idx_main = prod_tokens.index(main_word)
        if idx_main > 0:
            previa = prod_tokens[idx_main-1]
            if previa in ["pate", "pateo", "crema", "mousse", "sobrasada"]:
                return 0.0
    except ValueError: pass

    # 4. BOOSTING POR CATEGORÍA
    category_boost = 0.0
    for key, cats_validas in MAPEO_CATEGORIAS.items():
        if key == query_tokens[0]:
            if any(cv.lower() in categoria_prod.lower() for cv in cats_validas):
                category_boost = 0.35 # Subido ligeramente
                break

    # [BLOQUE MOVIDO] Las penalizaciones de procesados y fiambres ahora son globales y están al final (Filtro 9) para proteger carnes, verduras y legumbres por igual.
            
    # Evitar intercambio de animales (Ej: Si pide 'ternera', prohibir que matchee con 'cerdo', 'pavo' o pescados)
    animales_conocidos = {"pollo", "pavo", "cerdo", "ternera", "vaca", "vacuno", "buey", "cordero", "conejo", 
                          "panga", "abadejo", "mejillon", "gamba", "langostino", "merluza", "salmon", "atun", 
                          "pescado", "sepia", "calamar", "pulpo", "lubina", "dorada", "bacalao", "trucha", "sardina"}
    query_animales = set(query_tokens).intersection(animales_conocidos)
    prod_animales = set(prod_tokens).intersection(animales_conocidos)
    
    # Agrupamos familias para no bloquear sinónimos válidos (ej. Mercadona dice ternera, Dia dice vacuno)
    familia_bovina = {"ternera", "vaca", "vacuno", "buey"}
    if query_animales.intersection(familia_bovina): query_animales.update(familia_bovina)
    if prod_animales.intersection(familia_bovina): prod_animales.update(familia_bovina)
    
    if query_animales and prod_animales:
        # Si el query pide un animal específico, y el producto tiene otro animal mencionado, pero NO el que pido:
        if not query_animales.intersection(prod_animales):
            return 0.01 

    # 5. POSICIONAMIENTO
    pos_score = 0.0
    for i, p_t in enumerate(prod_tokens):
        if p_t in query_tokens:
            if i == 0: pos_score = 1.0       
            elif i == 1: pos_score = 0.8     
            elif i == 2: pos_score = 0.3     
            else: pos_score = 0.1
            break

    score_vector = producto['score_original']
    
    # 6. FÓRMULA FINAL
    ratio = len(q_set.intersection(p_set)) / len(q_set)
    final_score = (score_vector * 0.20) + (pos_score * 0.45) + (category_boost) + (ratio * 0.10)
    
    if pos_score >= 1.0:
        final_score += 0.15 

    # Boost para productos básicos
    BASIKOS = ["pepino", "tomate", "cebolla", "patata", "aguacate", "huevo", "leche", "arroz", "atun", "alga", "edamame", "azucar", "lechuga", "ajo", "garbanzo", "lenteja", "alubia", "carne", "cerdo", "ternera", "merluza", "pimenton", "sal", "espinaca", "platano", "zanahoria"]
    if any(any(b in t or t in b for t in query_tokens) for b in BASIKOS):
        # El bono solo se aplica si realmente hay un match con el básico
        for b in BASIKOS:
            if any(b in t or t in b for t in query_tokens) and any(b in t for t in prod_tokens):
                final_score += 0.55 
                if prod_tokens[0] == b or (len(prod_tokens) > 1 and prod_tokens[1] == b):
                    final_score += 0.25
                # Bono extra para leches y especias para asegurar que no se pierdan
                if b in ["leche", "pimenton", "sal", "platano", "atun"]:
                    final_score += 0.3

    # 8. PENALIZACIÓN POR EXCESO DE RUIDO
    extra_words = [w for w in p_set.difference(q_set) if w not in ["de", "con", "en", "el", "la", "unidad", "kg", "gr", "pieza"]]
    if len(extra_words) > 0:
        final_score -= (len(extra_words) * 0.04)
    
    # Filtros anti-confusión Proteinas y Soja
    if any(p in PROTEINAS_PESCADO for p in query_tokens):
        if "ahumado" in prod_tokens or "carpaccio" in prod_tokens:
            final_score -= 0.3 
        # Solo penalizamos conservas si el usuario especifica "fresco"
        if any(w in ["conserva", "lata", "aceite", "escabeche"] for w in prod_tokens):
            if any(w in query_tokens for w in ["fresco", "fresca", "frescos", "frescas"]):
                final_score -= 2.0
            else:
                # Si no pide fresco, la conserva es aceptable pero con un ligero ajuste
                final_score -= 0.05
    if "edamame" in query_tokens or "soja" in query_tokens:
        if any(w in ["postre", "yogur", "texturizada", "bebida"] for w in prod_tokens):
            final_score -= 0.7

    # 9. FILTRO GENÉRICO "FRESCO VS DERIVADO"
    # Evita falsos positivos como mayonesa por ajo, o harina por garbanzos.
    derivados = {"zumo", "nectar", "sabor", "helado", "caramelo", "bizcocho", "tarta", "mermelada", "salsa", "ketchup", "mayonesa", "mayo", "sorbete", "pure", "frito", "polvo", "rallado", "condimento", "aroma", "sirope", "gelatina", "extracto", "harina", "preparado", "brownie", "pastel", "galleta", "magdalena", "snack", "postre", "flan", "membrillo", "chicle", "chuche", "golosina", "gragea", "moscada", "dulce de leche", "chili", "bifidus", "plato", "preparado", "sazonador", "encurtido", "banderilla", "vinagreta", "aliño", "untable", "relleno", "canelones", "lasaña", "alimento infantil", "papilla", "postre lacteo", "bolsa 90 g", "bolsa 100 g", "bolsa 110 g", "smileat", "hero", "nestle"}
    procesados = {"nugget", "rebozado", "rebozada", "crunchy", "croqueta", "empanadilla", "empanado", "lonchas", "fiambre", "embutido", "pate", "salchicha", "chorizo", "morcilla", "salami", "pizza", "empanada", "lasaña", "canelones", "nuggets"}
    
    # Si la query no solicita explícitamente un derivado o ultraprocesado...
    if not any(d in query_tokens for d in derivados.union(procesados)):
        if any(d in nombre_prod for d in derivados):
            final_score -= 3.00 # ¡Ataque nuclear! Antes era -0.60 pero ciertos scores base subían a 2.0
        if any(p in nombre_prod for p in procesados):
            final_score -= 4.00 # Manda las empanadillas y los platos de chorizo a números negativos
            
    # 9b. PENALIZACIÓN ESPECÍFICA "FRESCO" VS "CONSERVA/LATA"
    # Si pides algo 'fresco' (ej. espinacas frescas), penalizamos fuertemente conservas o latas.
    if any(w in query_tokens for w in ["fresco", "fresca", "frescos", "frescas"]):
        if any(w in prod_tokens for w in ["conserva", "lata", "frasco", "bote", "cristal"]):
            final_score -= 2.5
            
    # 9c. ANTICONFUSIÓN QUESOS
    if "queso" in query_tokens:
        # Si el usuario no pide 'azul' explícitamente, penalizamos el queso azul por ser un sabor dominante/específico
        if "azul" in prod_tokens and "azul" not in query_tokens:
            final_score -= 1.5

    # 10. BLOQUEO INTER-BOTÁNICO Y ANIMAL EN LÁCTEOS
    # Evita que buscar "bebida de soja" acabe matcheando "bebida de avena" o "leche desnatada"
    botanicos = {"soja", "avena", "almendra", "arroz", "coco", "nuez", "avellana", "bifidus"}
    tipo_vaca = {"entera", "desnatada", "semidesnatada", "vaca", "oveja", "cabra", "condensada"}
    
    q_veg = set(query_tokens).intersection(botanicos)
    p_veg = set(prod_tokens).intersection(botanicos)
    q_vaca = set(query_tokens).intersection(tipo_vaca)
    p_vaca = set(prod_tokens).intersection(tipo_vaca)
    
    # 10a. Pide un vegetal y recibe otro distinto (Ej: Pide Almendra, recibe Soja)
    if q_veg and p_veg and not q_veg.intersection(p_veg):
        return 0.01 
        
    # 10b. Reglas Cruzadas Vaca vs Vegetal
    if "leche" in query_tokens or "bebida" in query_tokens:
        if q_veg and p_vaca: 
            return 0.01 # Pide Vegetal, recibe Vaca
        if q_vaca and p_veg: 
            return 0.01 # Pide Vaca, recibe Vegetal
        if (not q_veg) and p_veg:
            final_score -= 5.0 # Pide Leche a secas, recibe Vegetal rara

    # 11. VALIDACIÓN DE PALABRAS CLAVE OBLIGATORIAS (BURGOS, PICADA, NATURAL)
    # Evita que 'queso fresco de burgos' matchee 'queso de untar' o que 'carne picada' matchee 'carne a tacos'
    obligatorias_en_query = {"burgos", "picada", "natural", "integral", "rallado"}
    for w in obligatorias_en_query:
        if w in query_tokens and w not in prod_tokens:
            final_score -= 1.5
            
    # 12. BLOQUEO ESPECIFICO PASTA VS SAZONADOR/PIÑONES
    if "pasta" in query_tokens and not any(x in query_tokens for x in ["sazonador", "piñon"]):
        if any(x in prod_tokens for x in ["sazonador", "piñon", "rellena"]):
            final_score -= 2.0

    return final_score * multiplicador_match

def buscar_producto_inteligente(ingrediente: str, reintento_simple=False, alergias: list = None):
    if not client or not model: return None
    
    # 1. Limpieza de entrada
    nombre_raw = ingrediente['nombre'] if isinstance(ingrediente, dict) else ingrediente
    ingrediente_limpio = limpiar_ingrediente_avanzado(nombre_raw)
    
    # Si estamos en reintento, extraemos el sustantivo clave en lugar de ir a ciegas a por la primera palabra
    if reintento_simple:
        tokens = ingrediente_limpio.split()
        if tokens:
            nucleo_encontrado = next((t for t in tokens if t in OBLIGATORIAS_GLOBAL or t in PROTEINAS_CRÍTICAS), None)
            ingrediente_limpio = nucleo_encontrado if nucleo_encontrado else tokens[0]
            print(f"⚠️ Reintento inteligente detectó núcleo: '{ingrediente_limpio}'")

    # 2. Traducción Semántica
    # Intentamos primero singular, luego plural en el mapeo
    core_sin_s = ingrediente_limpio.rstrip('s')
    busqueda_vectorial = CONTEXTO_SEMANTICO.get(core_sin_s, 
                         CONTEXTO_SEMANTICO.get(ingrediente_limpio, ingrediente_limpio))
    
    # Enriquecimiento dinámico para productos básicos (Pull Vectorial)
    # Si es un básico (fruta, carne, legumbre), forzamos descriptores que los separen de yogures/purés
    if any(b in busqueda_vectorial or b in ingrediente_limpio for b in ["platano", "tomate", "atun", "garbanzo", "lenteja", "pollo", "pavo", "merluza"]):
        busqueda_vectorial += " natural fresco bolsa bote"
    
    print(f"🔍 Buscando: '{ingrediente}' -> Vector: '{busqueda_vectorial}'")

    vector = model.encode(busqueda_vectorial).tolist()
    
    # 3. Búsqueda Vectorial independiente por tienda (Aumentamos límites para Deep Search)
    limit_m = 1000 if any(x in ingrediente_limpio for x in ["leche", "platano", "atun", "garbanzo", "nuez"]) else 500
    limit_d = 700 if any(x in ingrediente_limpio for x in ["leche", "platano", "atun", "garbanzo", "nuez"]) else 400
    
    search_queries = [
        {"tienda": "Mercadona", "limit": limit_m},
        {"tienda": "Dia", "limit": limit_d} 
    ]
    
    resultados_totales = []
    for sq in search_queries:
        try:
            res = client.search(
                collection_name="productos_supermercado", 
                query_vector=vector, 
                query_filter=models.Filter(
                    must=[models.FieldCondition(key='tienda', match=models.MatchValue(value=sq['tienda']))]
                ),
                limit=sq['limit'],
                with_payload=True
            )
            resultados_totales.extend(res)
        except Exception as e:
            print(f"   ❌ Error Qdrant ({sq['tienda']}): {e}")

    if not resultados_totales:
        return None

    candidates = []
    for r in resultados_totales:
        p = r.payload
        if p.get('precio', 0) <= 0: continue
        
        item = {
            "nombre": p['nombre'],
            "tienda": p.get('tienda', '?'),
            "precio": float(p['precio']),
            "precio_ref": float(p.get('precio_ref', 0)),
            "unidad": p.get('unidad', 'ud'),
            "imagen": p.get('imagen', ''), 
            "categoria": p.get('categoria', ''),
            "nombre_estandar": p.get('nombre_estandar', ''),
            "score_original": r.score
        }
        
        item['final_score'] = calcular_score_v15(item, ingrediente_limpio, alergias)
        
        # Umbral de seguridad mínimo ELEVADO a 0.25 para evitar falsos positivos ridículos (ej. espinacas por butifarra)
        if item['final_score'] > 0.25: 
            item['es_formato_grande'] = item['precio'] > UMBRAL_PRECIO_NORMAL
            candidates.append(item)

    if not candidates: 
        # Reintento con núcleo si no hay resultados
        # Si no encontramos nada con el nombre largo, probamos solo con la primera palabra (ej: 'Lentejas')
        if not reintento_simple:
            return buscar_producto_inteligente(ingrediente, reintento_simple=True, alergias=alergias)
        return None

    # Ordenar por mejor coincidencia
    candidates.sort(key=lambda x: x['final_score'], reverse=True)
    
    # Selección por precio unitario/proporcional
    # Cogemos el top 3 de mejores coincidencias de cada súper
    m_opts = [c for c in candidates if c['tienda'] == 'Mercadona'][:3]
    d_opts = [c for c in candidates if c['tienda'] == 'Dia'][:3]

    # Función auxiliar para obtener el precio de comparación (Ref o Total)
    def get_comp_price(item, target_grams=None):
        """Calcula un precio de comparación ajustado por cantidad y 'sentido común'."""
        precio_total = item.get('precio', 0)
        p_ref = item.get('precio_ref', 0)
        base_price = p_ref if p_ref > 0.05 else precio_total
        
        # --- Lógica de Compra Proporcional (V20) ---
        # Si sabemos cuánto necesita el usuario (ej: 300g)
        if target_grams and p_ref > 0.05:
            target_kg = target_grams / 1000.0
            product_kg = precio_total / p_ref
            
            # Si el producto es demasiado grande para lo solicitado (ej: pides 300g y te da 4kg)
            if product_kg > (target_kg * 2.5):
                penalizacion_tamanio = min(product_kg / target_kg, 10.0)
                return base_price * penalizacion_tamanio
        
        # --- Lógica de Sentido Común (V17 - Fallback) ---
        if precio_total > UMBRAL_PRECIO_NORMAL:
            factor = min(precio_total / UMBRAL_PRECIO_NORMAL, MAX_PENALIZACION_FORMATO)
            return base_price * factor
            
        return base_price

    # De esos top 3, cogemos el que tenga MEJOR PRECIO UNITARIO / PROPORCIONAL
    # target_grams vendrá del objeto ingrediente si existe
    target_val = 0
    if isinstance(ingrediente, dict):
        target_val = ingrediente.get('cantidad', 0)

    best_m = min(m_opts, key=lambda x: get_comp_price(x, target_val)) if m_opts else None
    best_d = min(d_opts, key=lambda x: get_comp_price(x, target_val)) if d_opts else None

    ganador = None
    perdedor = []
    
    if best_m and best_d:
        # Comparamos por precio unitario/proporcional para decidir el ganador absoluto del producto
        if get_comp_price(best_m, target_val) < get_comp_price(best_d, target_val):
            ganador = best_m
            perdedor.append(best_d)
        else:
            ganador = best_d
            perdedor.append(best_m)
    elif best_m: ganador = best_m
    elif best_d: ganador = best_d

    return {"mejor": ganador, "otras": perdedor}

def calcular_unidades_a_comprar(target_qty, producto, ing_nombre=""):
    if not producto: return 1
    p_ref = producto.get('precio_ref', 0)
    precio = producto.get('precio', 0)
    unidad = producto.get('unidad', 'ud').lower()
    
    if p_ref <= 0.05 or target_qty <= 0:
        return 1
        
    tamano_producto = precio / p_ref
    
    # Determinar si target_qty son Unidades o Gramos/Ml
    ing_lower = ing_nombre.lower()
    es_unidad = any(x in ing_lower for x in ["huevo", "yogur", "manzana", "platano", "pera", "naranja", "aguacate", "tostada"]) 
    # v40: Si es Zumo o Bebida, NO es unidad (queremos tratarlo como volumen)
    if any(x in ing_lower for x in ["zumo", "bebida", "leche", "caldo"]):
        es_unidad = False
    
    target = float(target_qty)
    
    try:
        if es_unidad:
            if unidad in ['kg', 'kilo', 'l', 'litro']:
                necesidad_eq = target * 0.150 # 150g por pieza aprox
            elif '100' in unidad:
                necesidad_eq = target * 1.5
            elif unidad in ['docena', 'dc']:
                necesidad_eq = target / 12.0
            else:
                necesidad_eq = target
        else:
            if unidad in ['kg', 'kilo', 'l', 'litro']:
                necesidad_eq = target / 1000.0
            elif '100' in unidad:
                necesidad_eq = target / 100.0
            elif unidad in ['docena', 'dc']:
                necesidad_eq = (target / 50.0) / 12.0 
            else:
                # El súper vende uds, y tú pides gramos (ej. 10g de perejil, o 2kg de cebolla)
                if "ajo" in ing_lower:
                    peso_unidad = 50.0 # 50g cabeza
                elif any(x in ing_lower for x in ["sal", "especia", "oregano", "perejil", "laurel"]):
                    peso_unidad = 20.0 # botecito pequeño 20g
                elif "cebolla" in ing_lower or "calabacin" in ing_lower or "pimiento" in ing_lower:
                    peso_unidad = 200.0 
                else:
                    peso_unidad = 150.0 
                
                # v40: Rendimiento de zumo (1L de zumo requiere aprox 2kg de naranjas)
                # v40: Rendimiento de zumo (1L de zumo requiere aprox 2kg de naranjas)
                if "zumo" in ing_lower and "naranja" in ing_lower:
                    peso_unidad = 500.0 # Cada unidad de 1kg rinde solo 500ml de zumo real
                
                necesidad_eq = target / peso_unidad
                
        unidades = math.ceil(necesidad_eq / tamano_producto)
        return max(1, unidades)
    except:
        return 1

import asyncio

async def procesar_lista_compra(lista_ingredientes: List[any], alergias: list = None) -> dict:
    cesta_m = {"total": 0.0, "items": [], "missing": []}
    cesta_d = {"total": 0.0, "items": [], "missing": []}
    
    # Comparativa basada en productos presentes en ambos supermercados
    # Solo comparamos el ahorro de los productos que están en AMBOS.
    # Así el usuario tiene una referencia real de precio por los mismos items.
    
    comp_m = 0.0
    comp_d = 0.0
    comparativa_justa_m = 0.0
    comparativa_justa_d = 0.0
    items_comunes_count = 0
    filas_grouped = {} # Usaremos un dict para agrupar por (Prod_M, Prod_D)

    def local_comp_price(item, qty):
        if not item: return 0.0
        p_ref = item.get('precio_ref', 0)
        if p_ref > 0.05: return p_ref
        return item['precio']

    # Consolidación de ingredientes duplicados
    dict_consolidado = {}
    for ing_obj in lista_ingredientes:
        nombre = normalizar(ing_obj['nombre'] if isinstance(ing_obj, dict) else ing_obj)
        qty = ing_obj.get('cantidad', 0) if isinstance(ing_obj, dict) else 0
        if nombre not in dict_consolidado:
            dict_consolidado[nombre] = {"nombre": nombre, "cantidad": qty}
        else:
            dict_consolidado[nombre]["cantidad"] += qty
    
    ingredientes_finales = list(dict_consolidado.values())

    print(f"🚀 Lanzando búsqueda multi-hilo para {len(ingredientes_finales)} ingredientes únicos...")
    loop = asyncio.get_event_loop()
    
    # Disparar búsquedas concurrentes en Qdrant (ahorro bestial de tiempo I/O)
    tareas = [
        loop.run_in_executor(None, buscar_producto_inteligente, i_data['nombre'], False, alergias)
        for i_data in ingredientes_finales
    ]
    resultados_busqueda = await asyncio.gather(*tareas)

    for i_data, res in zip(ingredientes_finales, resultados_busqueda):
        ing_nombre = i_data['nombre']
        target_qty = i_data['cantidad']
        
        best_m = None
        best_d = None
        if res:
            if res.get('mejor') and res['mejor']['tienda'] == 'Mercadona': best_m = res['mejor']
            elif res.get('otras'): best_m = next((x for x in res['otras'] if x['tienda'] == 'Mercadona'), None)

            if res.get('mejor') and res['mejor']['tienda'] == 'Dia': best_d = res['mejor']
            elif res.get('otras'): best_d = next((x for x in res['otras'] if x['tienda'] == 'Dia'), None)

        # Lógica de asignación a cestas (Limpia)
        units_m = 0
        units_d = 0
        
        if best_m:
            units_m = calcular_unidades_a_comprar(target_qty, best_m, ing_nombre)
            best_m["multiplicador"] = units_m
            cesta_m["items"].append(best_m)
            cesta_m["total"] += best_m["precio"] * units_m
            comp_m += local_comp_price(best_m, target_qty) * units_m
        else:
            cesta_m["missing"].append(ing_nombre)

        if best_d:
            units_d = calcular_unidades_a_comprar(target_qty, best_d, ing_nombre)
            best_d["multiplicador"] = units_d
            cesta_d["items"].append(best_d)
            cesta_d["total"] += best_d["precio"] * units_d
            comp_d += local_comp_price(best_d, target_qty) * units_d
        else:
            cesta_d["missing"].append(ing_nombre)

        # Si están en ambos, los sumamos a la comparativa justa para el cálculo de ahorro
        if best_m and best_d:
            comparativa_justa_m += best_m["precio"] * units_m
            comparativa_justa_d += best_d["precio"] * units_d
            items_comunes_count += 1
            
        # [NUEVO] Agrupación por pareja de productos (Evita filas duplicadas en la tabla)
        # Generamos una clave única basada en los nombres de los productos encontrados
        key_group = (best_m["nombre"] if best_m else "None", best_d["nombre"] if best_d else "None")
        
        if key_group not in filas_grouped:
            filas_grouped[key_group] = {
                "ingrediente": ing_nombre.capitalize(),
                "mercadona": best_m,
                "dia": best_d
            }
        else:
            # Si ya existe esta pareja de productos, sumamos los multiplicadores
            if best_m and filas_grouped[key_group]["mercadona"]:
                filas_grouped[key_group]["mercadona"]["multiplicador"] += units_m
            if best_d and filas_grouped[key_group]["dia"]:
                filas_grouped[key_group]["dia"]["multiplicador"] += units_d
            # Añadimos el nuevo nombre de ingrediente al rastro para que el usuario sepa qué incluye la fila
            if ing_nombre.capitalize() not in filas_grouped[key_group]["ingrediente"]:
                filas_grouped[key_group]["ingrediente"] += f", {ing_nombre}"

    # Convertimos el diccionario agrupado a la lista final de filas
    filas_unicas = list(filas_grouped.values())

    # -- Calculo de la Cesta Mixta (Mejor opción absoluta) --
    cesta_mixta_total = 0.0
    cesta_mixta_items = 0
    for f in filas_unicas:
        pm = f["mercadona"]["precio"] if f["mercadona"] else float('inf')
        pd = f["dia"]["precio"] if f["dia"] else float('inf')
        
        if pm == float('inf') and pd == float('inf'):
            f["recomendado_mixto"] = "ninguno"
            continue
            
        if pm <= pd:
            cesta_mixta_total += pm
            cesta_mixta_items += 1
            f["recomendado_mixto"] = "Mercadona"
        else:
            cesta_mixta_total += pd
            cesta_mixta_items += 1
            f["recomendado_mixto"] = "Dia"

    # Cálculo final de ahorro y ganador
    # Atendiendo a la petición del usuario: comparamos la resta de los tickets reales
    ahorro = round(abs(cesta_m["total"] - cesta_d["total"]), 2)
    
    # Decidimos el ganador por el ticket más barato
    if cesta_m["total"] < cesta_d["total"]: ganador = "Mercadona"
    elif cesta_d["total"] < cesta_m["total"]: ganador = "Dia"
    else: ganador = "Empate"

    # Si la cesta mixta es todavía más barata, avisamos.
    if cesta_mixta_total < min(cesta_m["total"], cesta_d["total"]):
        ahorro_mixto = round(max(cesta_m["total"], cesta_d["total"]) - cesta_mixta_total, 2)
    else:
        ahorro_mixto = ahorro

    # Unificación de salida para evitar líneas repetidas en la UI
    # Si dos ingredientes distintos del menú mapean al MISMO producto del súper, 
    # los unificamos para no mostrar líneas duplicadas en la UI.
    def unificar_cesta(items_brutos):
        vistos = {}
        items_unicos = []
        for it in items_brutos:
            key = f"{it['nombre']}_{it['tienda']}_{it['precio']}"
            if key not in vistos:
                vistos[key] = True
                items_unicos.append(it)
        return items_unicos

    # --- NUEVA LÓGICA DE DETECCIÓN DE EFICIENCIA VS TICKET ---
    mensaje_ahorro = ""
    # Caso A: Mercadona más caro en ticket pero más barato por kilo/litro
    if cesta_m["total"] > cesta_d["total"] and comp_m < comp_d:
        mensaje_ahorro = "Nota: Mercadona tiene mejor precio medio por Kg/L, pero tu ticket es mayor porque los productos seleccionados son de mayor tamaño (formatos familiares/ahorro)."
    # Caso B: Dia más caro en ticket pero más barato por kilo/litro
    elif cesta_d["total"] > cesta_m["total"] and comp_d < comp_m:
        mensaje_ahorro = "Nota: Dia tiene mejor precio medio por Kg/L, pero tu ticket es mayor porque los productos seleccionados son de mayor tamaño (formatos familiares/ahorro)."

    comp_completa = (len(cesta_m["missing"]) == 0 and len(cesta_d["missing"]) == 0)

    return {
        "mejor_supermercado": ganador,
        "ahorro_total": ahorro,
        "cesta_mixta": {
            "total": round(cesta_mixta_total, 2),
            "ahorro_potencial": ahorro_mixto,
            "items": cesta_mixta_items
        },
        "cesta_mercadona": {
            "supermercado": "Mercadona",
            "total": round(cesta_m["total"], 2),
            "total_normalizado": round(comp_m, 2),
            "productos_encontrados": unificar_cesta(cesta_m["items"]),
            "productos_no_encontrados": sorted(list(set(cesta_m["missing"]))),
            "num_faltantes": len(set(cesta_m["missing"]))
        },
        "cesta_dia": {
            "supermercado": "Dia",
            "total": round(cesta_d["total"], 2),
            "total_normalizado": round(comp_d, 2),
            "productos_encontrados": unificar_cesta(cesta_d["items"]),
            "productos_no_encontrados": sorted(list(set(cesta_d["missing"]))),
            "num_faltantes": len(set(cesta_d["missing"]))
        },
        "filas": filas_unicas,
        "mensaje_ahorro": mensaje_ahorro,
        "comparativa_completa": comp_completa
    }

# ENDPOINTS 
@app.post("/comparar-lista-compra", response_model=ComparativaFinal)
async def comparar_lista_compra(lista: ListaCompraRequest):
    ingredientes_filtrados, _ = filtrar_ingredientes_en_casa(lista.ingredientes, lista.ingredientes_en_casa)
    return await procesar_lista_compra(ingredientes_filtrados, alergias=lista.alergias if hasattr(lista, 'alergias') else None)

@app.get("/test-debug")
async def test_debug():
    return {"status": "V33-ONLINE", "file": __file__}

@app.post("/planificar-menu")
async def planificar_menu(req: MenuRequest):
    import traceback
    try:
        prompt_usuario = (req.prompt_usuario or req.prompt or "").strip()
        num_personas = max(1, int(req.num_personas or 2))

        resultado_chef = generar_lista_desde_menu(
            prefs={
                "prompt_usuario": prompt_usuario,
                "num_personas": num_personas,
                "tipo_dieta": req.tipo_dieta,
                "intolerancias": req.intolerancias,
                "alergias": req.alergias,
                "no_me_gusta": req.no_me_gusta,
                "me_gusta": req.me_gusta,
                "objetivo": req.objetivo,
                "incluir_snacks": req.incluir_snacks,
                "num_dias": req.num_dias
            }
        )
        
        if "error" in resultado_chef:
            return {"error": resultado_chef["error"]}
        
        print(f"👨‍🍳 Chef dice: {resultado_chef}")
        ingredientes_crudos = resultado_chef.get("ingredientes_clave", [])
        ingredientes_filtrados, ingredientes_excluidos = filtrar_ingredientes_en_casa(
            ingredientes_crudos,
            req.ingredientes_en_casa
        )
        
        # Si la dieta es estrictamente Sin Gluten, inyectamos la alergia matemáticamente
        alergias_activas = req.alergias or []
        if req.tipo_dieta and "gluten" in req.tipo_dieta.lower() and "gluten" not in [a.lower() for a in alergias_activas]:
            alergias_activas.append("gluten")
        
        comparativa = await procesar_lista_compra(ingredientes_filtrados, alergias=alergias_activas)
        
        return {
            "menu": resultado_chef.get("menu_pensado", []),
            "ingredientes_originales": ingredientes_crudos,
            "ingredientes_limpios": ingredientes_filtrados,
            "ingredientes_excluidos_despensa": ingredientes_excluidos,
            "num_personas": num_personas,
            "comparativa": comparativa
        }
    except Exception as e:
        error_msg = traceback.format_exc()
        print(f"💥 ERROR EN /planificar-menu: {error_msg}")
        return {"error": f"Error interno: {str(e)}", "traceback": error_msg}