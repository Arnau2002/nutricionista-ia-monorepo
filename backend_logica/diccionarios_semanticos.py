# Configuración Semántica básica (El diccionario traductor)
CONTEXTO_SEMANTICO = {
    # Carnes y Pescados
    "pollo": "pechuga pollo entera",
    "pechuga de pollo": "pechuga pollo entera",
    "pavo": "pechuga pavo",
    "ternera": "carne ternera",
    "cerdo": "lomo cerdo",
    "atun": "atun claro aceite",
    "salmon": "salmon fresco lomo",
    "merluza": "lomo merluza fresco",
    
    # Lácteos y Huevos
    "leche": "leche entera",
    "queso": "queso tierno",
    "queso fresco": "queso fresco de burgos",
    "yogur": "yogur natural",
    "huevo": "huevos docena",
    "huevos": "huevos docena",
    "huevo duro": "huevo cocido pelado",
    
    # Desayuno, Pan y Bebidas Vegetales
    "pan": "pan de barra",
    "pan integral": "pan de molde integral",
    "avena": "copos de avena suaves paquete",
    "bebida de soja": "bebida de soja clasica",
    "bebida de avena": "bebida de avena clasica",
    "bebida de almendra": "bebida de almendras clasica",
    "azucar": "azucar blanco 1kg",
    
    # Básicos de Despensa
    "aceite": "aceite oliva virgen",
    "aceite de oliva": "aceite oliva virgen extra",
    "vinagre": "vinagre vino",
    "sal": "sal fina",
    "pimenton": "pimenton dulce",
    
    # Hidratos y Legumbres
    "pasta": "macarrones",
    "pasta integral": "macarrones integrales",
    "arroz": "arroz redondo paquete",
    "arroz integral": "arroz integral paquete",
    "lenteja": "lenteja pardina seca",
    "garbanzo": "garbanzo cocido frasco",
    "alubia": "alubia blanca cocida",
    
    # Verduras y Hortalizas "Anti-Confusión"
    "espinaca": "espinacas frescas bolsa",
    "lechuga": "lechuga iceberg fresca",
    "cebolla": "cebollas tiernas malla",
    "tomate": "tomate pera fresco",
    "tomate triturado": "tomate triturado natural lata",
    "ajo": "ajos morados cabezas",
    "zanahoria": "zanahorias frescas bolsa",
    "patata": "patatas malla limpia",
    "calabacin": "calabacin unidad fresca",
    "calabaza": "calabaza pelada fresca",
    "pepino": "pepino fresco unidad",
    "aguacate": "aguacate pieza",
    "pimiento": "pimiento rojo fresco",
    "brocoli": "brocoli fresco",
    
    # Frutas y Especialidades
    "platano": "platano canarias",
    "manzana": "manzana golden",
    "tofu": "tofu firme",
    "alga nori": "algas sushi nori",

    "edamame": "edamame congelado",
    "vinagre de arroz": "vinagre arroz sushi",
    "nuez": "nuez pelada frutos secos",
    "avena": "copos avena",
    "salsa de tomate": "salsa tomate frito triturado"
}

# Palabras a ignorar en la búsqueda
PALABRAS_A_IGNORAR = [
    "maduro", "maduros", "fresco", "fresca", "frescas", "frescos", "natural", "naturales",
    "de grano completo", "tipo", "estilo", "casero", "casera", "selección",
    "premium", "gourmet", "bio", "eco", "orgánico", "sano", "healthy",
    "un kilo de", "una docena de", "medio kilo de", "litro de", "un bote de", "un paquete de",
    "un", "una", "de", "con", "el", "la", "en", "para", "del", "las", "los",
    "pardina", "castellana", "pelado", "pelada", "entero", "entera", "troceado", "picada",
    "cocido", "cocida", "en bote", "en conserva", "lavada", "cortada", "limpio", "limpia",
    "marca", "blanca", "hacendado", "dia", "calidad", "extra", "superior", "especial",
    "variado", "mixto", "mezcla", "sabor", "congelado", "congelada", "ultracongelado",
    "fuego", "lento", "receta", "tradicional", "abuela", "artesano", "artesana",
    "virgen", "dulce", "ahumado", "picatostes", "tostado"
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
OBLIGATORIAS_GLOBAL = ["leche", "vino", "aceite", "vinagre", "huevo", "pan", "harina", "queso", "yogur", "pasta", "arroz"]

# DICCIONARIOS DE SUSTITUCIÓN Y LIMPIEZA
# Proteínas
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
