import os
import unicodedata
import statistics
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import List, Optional
from qdrant_client import QdrantClient
from sentence_transformers import SentenceTransformer

app = FastAPI(title="Nutricionista IA")

#  CORS 
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

#  INICIO SERVICIOS 
QDRANT_HOST = os.getenv('QDRANT_HOST', 'vector-db')
QDRANT_PORT = 6333
client = None
model = None

try:
    client = QdrantClient(host=QDRANT_HOST, port=QDRANT_PORT)
    client.get_collections()
    model = SentenceTransformer('paraphrase-multilingual-MiniLM-L12-v2')
    print("✅ Sistema IA V13 Online.")
except Exception as e:
    print(f"❌ Error crítico: {e}")

#  MODELOS 
class ItemBusqueda(BaseModel):
    ingrediente: str

class ListaCompraRequest(BaseModel):
    ingredientes: List[str]

class ComparativaFinal(BaseModel):
    mejor_supermercado: str
    ahorro_total: float
    cesta_mercadona: dict
    cesta_dia: dict

#  1. CONTEXTO SEMÁNTICO (Lo que la IA debe buscar) 
CONTEXTO_SEMANTICO = {
    "huevo": "huevos frescos gallina docena",
    "huevos": "huevos frescos gallina docena",
    "pollo": "pechuga pollo fresco entero",
    "leche": "leche vaca brik litro",
    "arroz": "arroz grano crudo paquete kilo",
    "cafe": "cafe molido natural paquete",
    "atun": "atun lata aceite conserva",
    "tomate": "tomate natural triturado kilo",
    "aceite": "aceite oliva virgen botella litro",
    "pan": "barra pan fresco hogaza"
}

#  2. FILTRO DE "IMPUREZAS" 
# Si un producto tiene estas palabras, se considera de peor calidad/procesado
# y recibe una penalización matemática fuerte.

PALABRAS_PROCESADAS = [
    # Procesados cárnicos/pescado
    "cocido", "cocida", "adobado", "adobada", "marinado",
    "empanada", "empanadilla", "rebozado", "frito", 
    "pate", "paté", "foie", "sobrasada", "crema", "untable",
    "fiambre", "mortadela", "salchicha", "salchichas", "burguer", "hamburguesa",
    "nuggets", "croquetas", "albóndigas", "albondigas", "varitas",
    
    # Platos listos
    "listo", "preparado", "microondas", "calentar", 
    "pizza", "lasaña", "canelones", "tortilla", "ensaladilla", "gazpacho",
    
    # Repostería/Saborizados
    "sabor", "aroma", "galleta", "pasta", "bollo", "yogur", "postre",
    "ajo", "trufa", "picante", "especias", "hierbas", "limón", "naranja",
    
    # Nicho / Calidad Inferior
    "codorniz", "orujo", "mix", "mezcla", "mini" 
]

#  3. LISTA NEGRA GLOBAL 
PALABRAS_PROHIBIDAS_GLOBAL = [
    "kinder", "juguete", "sorpresa", "corporal", "hidratante", "champú", "mascota"
]

#  HERRAMIENTAS 
def normalizar(texto: str) -> str:
    if not texto: return ""
    texto = unicodedata.normalize('NFD', texto).encode('ascii', 'ignore').decode("utf-8")
    return texto.lower().strip()

def tokenizar(texto: str) -> list:
    stopwords = {"de", "del", "el", "la", "los", "las", "en", "y", "o", "a", "para", "con", "sin", "pack", "bandeja", "g", "kg", "l", "ml", "litro", "brik", "botella"}
    palabras = normalizar(texto).split()
    tokens = []
    for p in palabras:
        if len(p) > 1 and p not in stopwords:
            raiz = p[:-1] if p.endswith('s') else p
            tokens.append(raiz)
    return tokens

#  SCORING V13 

def calcular_score_v13(producto: dict, query_original: str) -> float:
    nombre_prod = normalizar(producto['nombre'])
    
    # A. KILL SWITCH
    for prohibida in PALABRAS_PROHIBIDAS_GLOBAL:
        if prohibida in nombre_prod: return 0.0

    # B. DETECTOR DE PROCESADOS (El "Filtro de Pureza")
    is_processed = False
    for proc in PALABRAS_PROCESADAS:
        # Solo penalizamos si la palabra procesada NO estaba en la búsqueda del usuario
        # (Ej: Si busco "Salchichas", no penalizo "Salchichas". Si busco "Pollo", sí penalizo "Salchichas").
        if proc in nombre_prod and proc not in query_original:
            is_processed = True
            break 
    
    # C. ANÁLISIS DE TOKENS
    query_tokens = tokenizar(query_original)
    prod_tokens = tokenizar(nombre_prod)
    
    if not query_tokens or not prod_tokens: return 0.0

    # Intersección
    q_set = set(query_tokens)
    p_set = set(prod_tokens)
    coincidencias = len(q_set.intersection(p_set))
    
    if coincidencias < len(q_set):
         if len(q_set) < 3: return 0.0
         elif coincidencias < len(q_set) - 1: return 0.0

    # D. POSICIÓN (Vital para distinguir nombre de adjetivo)
    pos_score = 0.0
    try:
        idx = prod_tokens.index(query_tokens[0])
        if idx == 0: pos_score = 1.0       # "Pollo..."
        elif idx == 1: pos_score = 0.9     # "Pechuga Pollo..."
        elif idx == 2: pos_score = 0.4     # "Paté de Pollo..." (Baja relevancia)
        else: pos_score = 0.1
    except ValueError:
        pos_score = 0.0

    # E. CÁLCULO FINAL
    score_vector = producto['score_original']
    
    # Penalización fuerte (-0.4) para matar al Paté y al Orujo
    penalty_processed = 0.4 if is_processed else 0.0

    final_score = (score_vector * 0.35) + (pos_score * 0.65) - penalty_processed
    
    return final_score

def buscar_producto_inteligente(ingrediente: str):
    if not client or not model: return None
    
    ingrediente_clean = normalizar(ingrediente)
    busqueda_vectorial = CONTEXTO_SEMANTICO.get(ingrediente_clean, ingrediente)
    
    vector = model.encode(busqueda_vectorial).tolist()
    
    try:
        resultados = client.search(
            collection_name="productos_supermercado", 
            query_vector=vector, 
            limit=300, 
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
            "score_original": r.score
        }
        
        item['final_score'] = calcular_score_v13(item, ingrediente_clean)
        
        # Umbral de calidad
        if item['final_score'] > 0.40:
            candidates.append(item)

    if not candidates: return None

    # Anti-Packs
    precios = [c['precio'] for c in candidates]
    if precios:
        mediana_precio = statistics.median(precios)
        for c in candidates:
            if c['precio'] > (mediana_precio * 3.0):
                if "pack" not in ingrediente_clean:
                    c['final_score'] -= 0.3

    candidates.sort(key=lambda x: x['final_score'], reverse=True)
    
    m_opts = [c for c in candidates if c['tienda'] == 'Mercadona']
    d_opts = [c for c in candidates if c['tienda'] == 'Dia']

    best_m = min(m_opts[:5], key=lambda x: x['precio']) if m_opts else None
    best_d = min(d_opts[:5], key=lambda x: x['precio']) if d_opts else None

    # Comparación
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

#  ENDPOINTS 
@app.post("/comparar-lista-compra", response_model=ComparativaFinal)
async def comparar_lista_compra(lista: ListaCompraRequest):
    cesta_m = {"total": 0.0, "items": [], "missing": []}
    cesta_d = {"total": 0.0, "items": [], "missing": []}
    
    comp_m = 0.0
    comp_d = 0.0

    for ing in lista.ingredientes:
        res = buscar_producto_inteligente(ing)
        
        item_m = None
        item_d = None

        if res:
            if res['mejor'] and res['mejor']['tienda'] == 'Mercadona': item_m = res['mejor']
            elif res['otras']: item_m = next((x for x in res['otras'] if x['tienda'] == 'Mercadona'), None)

            if res['mejor'] and res['mejor']['tienda'] == 'Dia': item_d = res['mejor']
            elif res['otras']: item_d = next((x for x in res['otras'] if x['tienda'] == 'Dia'), None)

        precio_m = 0.0
        precio_d = 0.0

        if item_m:
            cesta_m["items"].append(item_m)
            cesta_m["total"] += item_m["precio"]
            precio_m = item_m["precio"]
        else:
            cesta_m["missing"].append(ing)

        if item_d:
            cesta_d["items"].append(item_d)
            cesta_d["total"] += item_d["precio"]
            precio_d = item_d["precio"]
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