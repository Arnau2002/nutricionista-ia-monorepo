import os
import unicodedata
import statistics
import re 
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import List, Optional
from qdrant_client import QdrantClient
from sentence_transformers import SentenceTransformer

# Importamos a nuestro Chef IA
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
    print("✅ Sistema IA V14 (Ultra-Pro) Online.")
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

class MenuRequest(BaseModel):
    prompt: str

class ComparativaFinal(BaseModel):
    mejor_supermercado: str
    ahorro_total: float
    cesta_mercadona: dict
    cesta_dia: dict

# --- 🧠 EL CEREBRO DEL TRADUCTOR (AQUÍ ESTÁ LA MAGIA) ---
# Mapeamos lo que dice el Chef -> A lo que entiende el Súper
CONTEXTO_SEMANTICO = {
    # BÁSICOS
    "huevo": "huevos frescos gallina docena",
    "huevos": "huevos frescos gallina docena",
    "pollo": "pechuga pollo fresco entero",
    "leche": "leche vaca brik litro",
    "arroz": "arroz grano redondo",
    "cafe": "cafe molido natural",
    "aceite": "aceite oliva virgen extra",
    "pan": "barra pan",

    # SOLUCIONES A TUS ERRORES
    "platanos maduros": "platano canarias", # Quitamos "maduros"
    "platano": "platano canarias",
    "espinacas frescas": "espinacas bolsa", # "frescas" a veces lía, "bolsa" acierta mejor
    "nueces y almendras naturales": "natural cocktail frutos secos", # Buscamos mix o uno de los dos
    "nueces": "nueces peladas",
    "almendras": "almendra natural",
    "semillas de chia": "semillas chia", # A veces la preposición molesta
    "chia": "semillas chia",
    "queso cottage": "queso fresco granulado", # En España se llama así
    "champinones frescos": "champiñones laminados bandeja",
    "leche o bebida vegetal": "bebida avena", # Elegimos una por defecto para evitar ambigüedad
    "pan integral de grano completo": "pan integral", # Simplificamos
    "pan integral": "pan molde integral 100%",
    "perejil fresco": "perejil manojo",
    "diente de ajo": "ajos malla",
    "canonigos o lechuga mix": "ensalada mezcla",
    "entrecot": "entrecot vacuno",
    "lomo de merluza": "merluza filetes",
    "fruta fresca": "manzana golden", # Default por si pide genérico
    "frutos rojos": "frutos rojos congelados"
}

# 2. FILTRO DE "RUIDO" (Palabras que confunden al buscador)
PALABRAS_A_IGNORAR = [
    "maduro", "maduros", "fresco", "fresca", "frescos", "natural", "naturales",
    "de grano completo", "tipo", "estilo", "casero", "casera", "selección",
    "premium", "gourmet", "bio", "eco", "orgánico", "sano", "healthy"
]

PALABRAS_PROHIBIDAS_GLOBAL = [
    "kinder", "juguete", "sorpresa", "corporal", "hidratante", "champú", "mascota", "colonia"
]

# HERRAMIENTAS 
def normalizar(texto: str) -> str:
    if not texto: return ""
    texto = unicodedata.normalize('NFD', texto).encode('ascii', 'ignore').decode("utf-8")
    return texto.lower().strip()

def limpiar_ingrediente_avanzado(texto: str) -> str:
    """Limpia adjetivos innecesarios para mejorar la búsqueda vectorial."""
    texto = normalizar(texto)
    # Quitamos paréntesis
    texto = re.sub(r'\(.*?\)', '', texto)
    # Quitamos palabras de ruido
    for palabra in PALABRAS_A_IGNORAR:
        texto = texto.replace(palabra, "")
    # Quitamos espacios dobles
    return re.sub(r'\s+', ' ', texto).strip()

def tokenizar(texto: str) -> list:
    stopwords = {"de", "del", "el", "la", "los", "las", "en", "y", "o", "a", "para", "con", "sin", "pack", "bandeja", "g", "kg", "l", "ml", "litro", "brik", "botella"}
    palabras = normalizar(texto).split()
    tokens = []
    for p in palabras:
        if len(p) > 1 and p not in stopwords:
            raiz = p[:-1] if p.endswith('s') else p
            tokens.append(raiz)
    return tokens

# SCORING V14 (Más flexible)
def calcular_score_v14(producto: dict, query_original: str) -> float:
    nombre_prod = normalizar(producto['nombre'])
    
    for prohibida in PALABRAS_PROHIBIDAS_GLOBAL:
        if prohibida in nombre_prod: return 0.0

    query_tokens = tokenizar(query_original)
    prod_tokens = tokenizar(nombre_prod)
    
    if not query_tokens or not prod_tokens: return 0.0

    q_set = set(query_tokens)
    p_set = set(prod_tokens)
    
    # Intersección
    coincidencias = len(q_set.intersection(p_set))
    
    # BONUS: Si la palabra clave principal está, damos muchos puntos
    # Ejemplo: Si busco "chia" y el producto tiene "chia", es un match fuerte.
    bonus_match = 0.0
    if query_tokens[0] in prod_tokens:
        bonus_match = 0.3

    # Penalización suave por longitud (menos estricta que antes)
    ratio = coincidencias / len(q_set)
    
    # Si coinciden menos de la mitad de las palabras, descartamos (salvo que sea 1 sola palabra)
    if len(q_set) > 1 and ratio < 0.5:
        return 0.0

    pos_score = 0.0
    try:
        idx = prod_tokens.index(query_tokens[0])
        if idx == 0: pos_score = 1.0       
        elif idx == 1: pos_score = 0.9     
        elif idx == 2: pos_score = 0.4     
        else: pos_score = 0.1
    except ValueError:
        pos_score = 0.0

    score_vector = producto['score_original']
    
    # Fórmula ajustada: Vector + Posición + Bonus - Penalización
    final_score = (score_vector * 0.40) + (pos_score * 0.40) + bonus_match
    return final_score

def buscar_producto_inteligente(ingrediente: str):
    if not client or not model: return None
    
    # 1. Limpieza Inteligente (El paso clave)
    ingrediente_limpio = limpiar_ingrediente_avanzado(ingrediente)
    
    # 2. Traducción Semántica (Usamos el diccionario o el limpio)
    busqueda_vectorial = CONTEXTO_SEMANTICO.get(ingrediente_limpio, ingrediente_limpio)
    
    print(f"🔍 Buscando: '{ingrediente}' -> Limpio: '{ingrediente_limpio}' -> Vector: '{busqueda_vectorial}'")

    vector = model.encode(busqueda_vectorial).tolist()
    
    try:
        resultados = client.search(
            collection_name="productos_supermercado", 
            query_vector=vector, 
            limit=50, # Reducimos el límite para ser más rápidos
            with_payload=True
        )
    except: return None

    candidates = []
    
    for r in resultados:
        p = r.payload
        if p.get('precio', 0) <= 0: continue
        
        item = {
            "nombre": p['nombre'],
            "tienda": p.get('tienda', '?'),
            "precio": float(p['precio']),
            "precio_ref": float(p.get('precio_referencia', 0)),
            "unidad": p.get('unidad', 'ud'),
            "imagen": p.get('imagen', ''), 
            "score_original": r.score
        }
        
        # Usamos el ingrediente limpio para el scoring textual
        item['final_score'] = calcular_score_v14(item, ingrediente_limpio)
        
        # Umbral ligeramente más bajo (0.35) para permitir más flexibilidad
        if item['final_score'] > 0.35:
            candidates.append(item)

    if not candidates: return None

    # Ordenar por mejor coincidencia
    candidates.sort(key=lambda x: x['final_score'], reverse=True)
    
    # Estrategia de Selección de Precios:
    # Cogemos el top 3 de mejores coincidencias de cada súper
    m_opts = [c for c in candidates if c['tienda'] == 'Mercadona'][:3]
    d_opts = [c for c in candidates if c['tienda'] == 'Dia'][:3]

    # De esos top 3, cogemos el más barato (así evitamos coger "Salmón Premium" si hay "Salmón Normal")
    best_m = min(m_opts, key=lambda x: x['precio']) if m_opts else None
    best_d = min(d_opts, key=lambda x: x['precio']) if d_opts else None

    ganador = None
    perdedor = []
    
    if best_m and best_d:
        if best_m['precio'] < best_d['precio']:
            ganador = best_m
            perdedor.append(best_d)
        else:
            ganador = best_d
            perdedor.append(best_m)
    elif best_m: ganador = best_m
    elif best_d: ganador = best_d

    return {"mejor": ganador, "otras": perdedor}

def procesar_lista_compra(lista_ingredientes: List[str]) -> dict:
    cesta_m = {"total": 0.0, "items": [], "missing": []}
    cesta_d = {"total": 0.0, "items": [], "missing": []}
    
    comp_m = 0.0
    comp_d = 0.0

    for ing in lista_ingredientes:
        res = buscar_producto_inteligente(ing)
        
        item_m = None
        item_d = None

        if res:
            if res.get('mejor') and res['mejor']['tienda'] == 'Mercadona': item_m = res['mejor']
            elif res.get('otras'): item_m = next((x for x in res['otras'] if x['tienda'] == 'Mercadona'), None)

            if res.get('mejor') and res['mejor']['tienda'] == 'Dia': item_d = res['mejor']
            elif res.get('otras'): item_d = next((x for x in res['otras'] if x['tienda'] == 'Dia'), None)

        precio_m = item_m["precio"] if item_m else 0.0
        precio_d = item_d["precio"] if item_d else 0.0

        if item_m:
            cesta_m["items"].append(item_m)
            cesta_m["total"] += precio_m
        else:
            cesta_m["missing"].append(ing)

        if item_d:
            cesta_d["items"].append(item_d)
            cesta_d["total"] += precio_d
        else:
            cesta_d["missing"].append(ing)

        if item_m and item_d:
            comp_m += precio_m
            comp_d += precio_d
        elif item_m and not item_d:
            comp_m += precio_m
            comp_d += precio_m
        elif not item_m and item_d:
            comp_m += precio_d
            comp_d += precio_d

    ganador = "Empate"
    ahorro = 0.0
    
    if comp_m < comp_d:
        ganador = "Mercadona"
        ahorro = round(comp_d - comp_m, 2)
    elif comp_d < comp_m:
        ganador = "Dia"
        ahorro = round(comp_m - comp_d, 2)
    else:
        if len(cesta_m["items"]) > len(cesta_d["items"]): ganador = "Mercadona"
        elif len(cesta_d["items"]) > len(cesta_m["items"]): ganador = "Dia"

    return {
        "mejor_supermercado": ganador,
        "ahorro_total": ahorro,
        "cesta_mercadona": {
            "supermercado": "Mercadona",
            "total": round(cesta_m["total"], 2),
            "productos_encontrados": cesta_m["items"],
            "productos_no_encontrados": cesta_m["missing"]
        },
        "cesta_dia": {
            "supermercado": "Dia",
            "total": round(cesta_d["total"], 2),
            "productos_encontrados": cesta_d["items"],
            "productos_no_encontrados": cesta_d["missing"]
        }
    }

# ENDPOINTS 
@app.post("/comparar-lista-compra", response_model=ComparativaFinal)
async def comparar_lista_compra(lista: ListaCompraRequest):
    return procesar_lista_compra(lista.ingredientes)

@app.post("/planificar-menu")
async def planificar_menu(req: MenuRequest):
    resultado_chef = generar_lista_desde_menu(req.prompt)
    
    if "error" in resultado_chef:
        return {"error": resultado_chef["error"]}
    
    ingredientes_crudos = resultado_chef.get("ingredientes_clave", [])
    
    # La limpieza se hace ahora dentro de 'procesar_lista_compra' -> 'buscar_producto_inteligente'
    # Así que podemos pasar los ingredientes tal cual o con una limpieza ligera
    comparativa = procesar_lista_compra(ingredientes_crudos)
    
    return {
        "menu": resultado_chef.get("menu_pensado", []),
        "ingredientes_originales": ingredientes_crudos,
        "ingredientes_limpios": ingredientes_crudos, 
        "comparativa": comparativa
    }