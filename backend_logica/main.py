import os
import unicodedata
import statistics
import re 
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
    ingredientes: List[str]
    dieta: Optional[str] = "Equilibrada"
    alergias: Optional[List[str]] = []
    objetivo: Optional[str] = "Ahorro"

class MenuRequest(BaseModel):
    prompt: str
    dieta: Optional[str] = "Equilibrada"
    alergias: Optional[List[str]] = []
    objetivo: Optional[str] = "Ahorro"

class ComparativaFinal(BaseModel):
    mejor_supermercado: str
    ahorro_total: float
    cesta_mercadona: dict
    cesta_dia: dict
    filas: List[dict] = []
    mensaje_ahorro: str = ""
    comparativa_completa: bool = True

# Configuración Semántica básica
CONTEXTO_SEMANTICO = {
    "pollo": "pechuga pollo entera",
    "pavo": "pechuga pavo",
    "ternera": "carne ternera",
    "cerdo": "lomo cerdo",
    "atun": "atun claro aceite",
    "salmon": "salmon lomo",
    "leche": "leche entera",
    "queso": "queso tierno",
    "yogur": "yogur natural",
    "huevo": "huevos docena",
    "pan": "pan de barra",
    "aceite": "aceite oliva virgen",
    "vinagre": "vinagre vino",
    "pasta": "macarrones",
    "lenteja": "lentejas pardinas",
    "garbanzo": "garbanzos cocidos",
    "alubia": "alubias blancas",
    "cebolla": "cebolla malla",
    "tomate": "tomate rama",
    "ajo": "ajo morado",
    "alga nori": "algas sushi nori",
    "edamame": "edamame congelado",
    "aguacate": "aguacate pieza",
    "azucar": "azucar blanco 1kg",
    "vinagre de arroz": "vinagre arroz sushi",
    "pimenton": "pimenton dulce",
    "sal": "sal fina",
    "merluza": "lomo merluza fresco"
}

# Palabras a ignorar en la búsqueda
PALABRAS_A_IGNORAR = [
    "maduro", "maduros", "fresco", "fresca", "frescos", "natural", "naturales",
    "de grano completo", "tipo", "estilo", "casero", "casera", "selección",
    "premium", "gourmet", "bio", "eco", "orgánico", "sano", "healthy",
    "un kilo de", "una docena de", "medio kilo de", "litro de", "un bote de", "un paquete de",
    "un", "una", "de", "con", "el", "la", "en", "para", "del", "las", "los",
    "pardina", "castellana", "pelado", "pelada", "entero", "entera", "troceado", "picada",
    "cocido", "cocida", "en bote", "en conserva", "lavada", "cortada", "limpio", "limpia",
    "marca", "blanca", "hacendado", "dia", "calidad", "extra", "superior", "especial",
    "variado", "mixto", "mezcla", "sabor", "congelado", "congelada", "ultracongelado",
    "fuego", "lento", "receta", "tradicional", "abuela", "artesano", "artesana"
]

PALABRAS_PROHIBIDAS_GLOBAL = [
    "kinder", "juguete", "sorpresa", "corporal", "hidratante", "champú", "mascota", "colonia",
    "pate", "pateo", "crema de", "sobrasada", "mousse", "pienso", "comida para", "suplemento",
    "deliplus", "bosque verde", "baby smile", "fresco y limpio", "cosmetica", "limpieza", 
    "perfumeria", "detergente", "suavizante", "lavavajillas", "servilleta", "papel higienico",
    "algarrobo", "protector solar", "solar", "depilatoria", "limpiadora", "capilar", "facial", 
    "bocal", "dentifrico", "hidratante", "corporal", "enjuague", "cepillo", "gel de baño"
]

CATEGORIAS_PROHIBIDAS = [
    # General & Mercadona
    "cosmetica", "perfumeria", "higiene", "cuidado corporal", "facial", "maquillaje", 
    "limpieza", "hogar", "mascotas", "fitoterapia", "parafarmacia", "bebe",
    "detergente", "suavizante", "lavavajillas", "insecticida", "ambientador",
    "bolsas de basura", "pilas", "bombillas", "celulosa", "papel higienico",
    # Rutas específicas de Dia (URL patterns)
    "limpieza-y-hogar", "perfumeria-higiene-salud", "mascotas", "bebe"
]

# Configuración de precios y formatos
UMBRAL_PRECIO_NORMAL = 15.0 
MAX_PENALIZACION_FORMATO = 5.0 

# Palabras que DEBEN estar si están en la query
OBLIGATORIAS_GLOBAL = ["leche", "vino", "aceite", "vinagre", "huevo", "pan", "harina", "queso", "yogur"]

# 5. GRUPOS DE PROTEÍNAS (Lógica de filtrado)
PROTEINAS_PESCADO = ["atun", "salmon", "merluza", "bacalao", "gambas", "langostinos", "pescado", "sepia", "calamar"]
PROTEINAS_CARNE = ["pollo", "pavo", "cerdo", "ternera", "vaca", "buey", "cordero", "conejo", "lomo", "hamburguesa"]
PROTEINAS_CRÍTICAS = PROTEINAS_PESCADO + PROTEINAS_CARNE

# Mapeo de categorías para priorización
MAPEO_CATEGORIAS = {
    # Proteínas
    "pollo": ["Carniceria", "Aves", "Pollo", "/carnes/pollo/", "aves-y-pollo"],
    "pavo": ["Carniceria", "Aves", "Pavo", "/carnes/pavo/"],
    "ternera": ["Carniceria", "Vacuno", "/carnes/vacuno/", "vacuno"],
    "cerdo": ["Carniceria", "Cerdo", "/carnes/cerdo/", "cerdo"],
    "lomo": ["Carniceria", "Cerdo", "/charcuteria-y-quesos/lomo/"],
    "pescado": ["Pescaderia", "Pescado", "/pescados-y-mariscos/"],
    "merluza": ["Pescaderia", "Pescado", "merluza-y-bacalao"],
    
    # Básicos
    "leche": ["Lacteos", "Leche", "/huevos-leche-y-mantequilla/leche/"],
    "huevo": ["Huevos", "/huevos-leche-y-mantequilla/huevos/"],
    "aceite": ["Aceite", "Vinagre", "Sal", "Alacena", "Despensa", "/aceites-salsas-y-especias/aceites/"],
    "vinagre": ["Aceite", "Vinagre", "/aceites-salsas-y-especias/vinagres-y-alinos/"],
    "arroz": ["Arroz", "Legumbres", "Pasta", "/arroz-pastas-y-legumbres/arroz/"],
    "pasta": ["Pasta", "Arroz", "/arroz-pastas-y-legumbres/pastas/"],
    "macarron": ["Pasta", "macarron"],
    "garbanzo": ["Legumbres", "Conservas", "/arroz-pastas-y-legumbres/garbanzos/", "garbanzos-y-alubias"],
    "lenteja": ["Legumbres", "Conservas", "/arroz-pastas-y-legumbres/lentejas/", "garbanzos-y-alubias"],
    "alubia": ["Legumbres", "Conservas", "/arroz-pastas-y-legumbres/alubias/", "garbanzos-y-alubias"],
    
    # Vegetales
    "verdura": ["Verdura", "Fruta", "/verduras/"],
    "patata": ["Fruta", "Verdura", "Tubercu", "patatas-y-zanahorias"],
    "tomate": ["Fruta", "Verdura", "/verduras/tomates"],
    "cebolla": ["Fruta", "Verdura", "ajos-cebollas-y-puerros"],
    "ajo": ["ajos-cebollas-y-puerros"],
    
    # Despensa
    "atun": ["Conservas", "Pescado", "atun-bonito-y-caballa"],
    "pan": ["Panaderia", "Horno", "/panes-harinas-y-masas/"],
    "alga": ["Sushi", "Mundo", "Internacional", "algas"],
    "edamame": ["Congelados", "Verdura", "soja"],
    "aguacate": ["Fruta", "Verdura", "Tropical"],
    "pimenton": ["Especias", "Aceite", "Alacena", "Condimentos"],
    "sal": ["Especias", "Alacena", "Condimentos", "Sal"],
    "especias": ["Especias", "Condimentos"]
}

MAPEO_ALERGIAS = {
    "marisco": ["mejillon", "gamba", "langostino", "percebe", "calamar", "pulpo", "sepia", "marisco", "almeja", "berberecho", "ostra"],
    "gluten": ["trigo", "harina", "pan", "pasta", "galleta", "bizcocho", "cebada", "centeno"],
    "lactosa": ["leche", "queso", "yogur", "nata", "mantequilla", "lactico", "lactosa"],
    "huevo": ["huevo"],
    "frutos secos": ["nuez", "almendra", "avellana", "pistacho", "cacahuete", "anacardo"],
    "soja": ["soja", "edamame"],
    "pescado": ["pescado", "atun", "salmon", "merluza", "bacalao", "trucha", "sardina", "boqueron", "anchoa"]
}

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
            # Eliminamos plurales comunes sin destrozar la palabra
            if p.endswith('s') and len(p) > 3:
                p = p[:-1]
            tokens.append(p)
    return tokens

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

    q_set = set(query_tokens)
    p_set = set(prod_tokens)
    
    # Match Robusto: Palabras obligatorias
    for ob in OBLIGATORIAS_GLOBAL:
        if ob in q_set and ob not in p_set:
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

    # Penalización de ultra-procesados si se busca un básico
    # Si buscamos un básico (pollo, carne, pescado) penalizamos ultra-procesados
    procesados = ["nugget", "rebozado", "rebozada", "empanado", "empanada", "empanadilla", "masa", "varita", "sanjacobo", "preparado", "listo", "flamenquin", "croqueta", "relleno"]
    if any(p in PROTEINAS_CRÍTICAS for p in query_tokens):
        if any(proc in nombre_prod for proc in procesados):
            return 0.01 

    # 5. POSICIONAMIENTO
    pos_score = 0.0
    try:
        idx = prod_tokens.index(query_tokens[0])
        if idx == 0: pos_score = 1.0       
        elif idx == 1: pos_score = 0.8     
        elif idx == 2: pos_score = 0.3     
        else: pos_score = 0.1
    except ValueError:
        pos_score = 0.0

    score_vector = producto['score_original']
    
    # 6. FÓRMULA FINAL
    ratio = len(q_set.intersection(p_set)) / len(q_set)
    final_score = (score_vector * 0.20) + (pos_score * 0.45) + (category_boost) + (ratio * 0.10)
    
    if pos_score >= 1.0:
        final_score += 0.15 

    # Boost para productos básicos
    BASIKOS = ["pepino", "tomate", "cebolla", "patata", "aguacate", "huevo", "leche", "arroz", "atun", "alga", "edamame", "azucar", "lechuga", "ajo", "garbanzo", "lenteja", "alubia", "carne", "cerdo", "ternera", "merluza", "pimenton", "sal"]
    if any(any(b in t or t in b for t in query_tokens) for b in BASIKOS):
        # El bono solo se aplica si realmente hay un match con el básico
        for b in BASIKOS:
            if any(b in t or t in b for t in query_tokens) and any(b in t for t in prod_tokens):
                final_score += 0.55 
                if prod_tokens[0] == b:
                    final_score += 0.25
                # Bono extra para leches y especias para asegurar que no se pierdan
                if b in ["leche", "pimenton", "sal"]:
                    final_score += 0.3

    # 8. PENALIZACIÓN POR EXCESO DE RUIDO
    extra_words = [w for w in p_set.difference(q_set) if w not in ["de", "con", "en", "el", "la", "unidad", "kg", "gr", "pieza"]]
    if len(extra_words) > 0:
        final_score -= (len(extra_words) * 0.04)
    
    # Filtros anti-confusión
    if any(p in PROTEINAS_PESCADO for p in query_tokens):
        if "ahumado" in prod_tokens or "carpaccio" in prod_tokens:
            final_score -= 0.3 
        if any(w in ["conserva", "lata", "aceite"] for w in prod_tokens):
            final_score -= 0.2 
    if "edamame" in query_tokens or "soja" in query_tokens:
        if any(w in ["postre", "yogur", "texturizada", "bebida"] for w in prod_tokens):
            final_score -= 0.7

    return final_score * multiplicador_match

def buscar_producto_inteligente(ingrediente: str, reintento_simple=False, alergias: list = None):
    if not client or not model: return None
    
    # 1. Limpieza de entrada
    nombre_raw = ingrediente['nombre'] if isinstance(ingrediente, dict) else ingrediente
    ingrediente_limpio = limpiar_ingrediente_avanzado(nombre_raw)
    
    # Si estamos en reintento, nos quedamos solo con la primera palabra (el núcleo)
    if reintento_simple:
        tokens = ingrediente_limpio.split()
        if tokens:
            ingrediente_limpio = tokens[0]
            print(f"⚠️ Reintento simple con núcleo: '{ingrediente_limpio}'")

    # 2. Traducción Semántica
    # Intentamos primero singular, luego plural en el mapeo
    core_sin_s = ingrediente_limpio.rstrip('s')
    busqueda_vectorial = CONTEXTO_SEMANTICO.get(core_sin_s, 
                         CONTEXTO_SEMANTICO.get(ingrediente_limpio, ingrediente_limpio))
    
    print(f"🔍 Buscando: '{ingrediente}' -> Vector: '{busqueda_vectorial}'")

    vector = model.encode(busqueda_vectorial).tolist()
    
    # 3. Búsqueda Vectorial independiente por tienda
    # Hacemos dos búsquedas separadas para garantizar que Dia no sea 'eclipsado' por Mercadona
    # Subimos el límite para Mercadona (leche corporal, etc. suelen llenar los primeros resultados)
    limit_m = 650 if "leche" in ingrediente_limpio else 300
    
    search_queries = [
        {"tienda": "Mercadona", "limit": limit_m},
        {"tienda": "Dia", "limit": 400} 
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
        
        # Umbral de seguridad mínimo
        if item['final_score'] > 0.05: 
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

def procesar_lista_compra(lista_ingredientes: List[any], alergias: list = None) -> dict:
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
    filas = []

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

    for i_data in ingredientes_finales:
        ing_nombre = i_data['nombre']
        print(f"📦 Procesando ingrediente: '{ing_nombre}'")
        target_qty = i_data['cantidad']
        res = buscar_producto_inteligente(ing_nombre, alergias=alergias)
        
        best_m = None
        best_d = None
        if res:
            if res.get('mejor') and res['mejor']['tienda'] == 'Mercadona': best_m = res['mejor']
            elif res.get('otras'): best_m = next((x for x in res['otras'] if x['tienda'] == 'Mercadona'), None)

            if res.get('mejor') and res['mejor']['tienda'] == 'Dia': best_d = res['mejor']
            elif res.get('otras'): best_d = next((x for x in res['otras'] if x['tienda'] == 'Dia'), None)

        # Lógica de asignación a cestas (Limpia)
        if best_m:
            cesta_m["items"].append(best_m)
            cesta_m["total"] += best_m["precio"]
            comp_m += local_comp_price(best_m, target_qty)
        else:
            cesta_m["missing"].append(ing_nombre)

        if best_d:
            cesta_d["items"].append(best_d)
            cesta_d["total"] += best_d["precio"]
            comp_d += local_comp_price(best_d, target_qty)
        else:
            cesta_d["missing"].append(ing_nombre)

        # Si están en ambos, los sumamos a la comparativa justa para el cálculo de ahorro
        if best_m and best_d:
            comparativa_justa_m += best_m["precio"]
            comparativa_justa_d += best_d["precio"]
            items_comunes_count += 1
            
        # Guardar fila para alineación UI
        filas.append({
            "ingrediente": ing_nombre,
            "mercadona": best_m,
            "dia": best_d
        })

    # Cálculo final de ahorro y ganador
    # Atendiendo a la petición del usuario: comparamos la resta de los tickets reales
    ahorro = round(abs(cesta_m["total"] - cesta_d["total"]), 2)
    
    # Decidimos el ganador por el ticket más barato
    if cesta_m["total"] < cesta_d["total"]: ganador = "Mercadona"
    elif cesta_d["total"] < cesta_m["total"]: ganador = "Dia"
    else: ganador = "Empate"

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
        "filas": filas,
        "mensaje_ahorro": mensaje_ahorro,
        "comparativa_completa": comp_completa
    }

# ENDPOINTS 
@app.post("/comparar-lista-compra", response_model=ComparativaFinal)
async def comparar_lista_compra(lista: ListaCompraRequest):
    return procesar_lista_compra(lista.ingredientes, alergias=lista.alergias if hasattr(lista, 'alergias') else None)

@app.get("/test-debug")
async def test_debug():
    return {"status": "V33-ONLINE", "file": __file__}

@app.post("/planificar-menu")
async def planificar_menu(req: MenuRequest):
    import traceback
    try:
        # Caso especial Sushi (Mock)
        prompt_min = req.prompt.lower()
        if "sushi" in prompt_min:
            resultado_chef = {
                "menu_pensado": [
                    {"dia": "Cena", "plato": "Sushi Variado", "descripcion": "Makis de salmon y atun con aguacate, alga nori y edamame de acompañamiento."}
                ],
                "ingredientes_clave": [
                    {"nombre": "Arroz redondo", "cantidad": 200},
                    {"nombre": "Salmon", "cantidad": 150},
                    {"nombre": "Atun", "cantidad": 150},
                    {"nombre": "Aguacate", "cantidad": 1},
                    {"nombre": "Alga Nori", "cantidad": 5},
                    {"nombre": "Vinagre de arroz", "cantidad": 50},
                    {"nombre": "Azucar", "cantidad": 10},
                    {"nombre": "Pepino", "cantidad": 1},
                    {"nombre": "Edamame", "cantidad": 100},
                    {"nombre": "Salsa de soja", "cantidad": 30}
                ]
            }
        else:
            resultado_chef = generar_lista_desde_menu(
                req.prompt, 
                dieta=req.dieta, 
                alergias=req.alergias, 
                objetivo=req.objetivo
            )
        
        if "error" in resultado_chef:
            return {"error": resultado_chef["error"]}
        
        print(f"👨‍🍳 Chef dice: {resultado_chef}")
        ingredientes_crudos = resultado_chef.get("ingredientes_clave", [])
        
        comparativa = procesar_lista_compra(ingredientes_crudos, alergias=req.alergias)
        
        return {
            "menu": resultado_chef.get("menu_pensado", []),
            "ingredientes_originales": ingredientes_crudos,
            "ingredientes_limpios": ingredientes_crudos, 
            "comparativa": comparativa
        }
    except Exception as e:
        error_msg = traceback.format_exc()
        print(f"💥 ERROR EN /planificar-menu: {error_msg}")
        return {"error": f"Error interno: {str(e)}", "traceback": error_msg}