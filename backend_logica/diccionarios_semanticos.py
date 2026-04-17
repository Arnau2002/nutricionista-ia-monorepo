# Configuración Semántica básica (El diccionario traductor)
CONTEXTO_SEMANTICO = {
    # Carnes y Pescados
    "pollo": "pechuga pollo",
    "pechuga de pollo": "pechuga pollo",
    "contramuslo de pollo": "contramuslos pollo",
    "muslos de pollo": "muslos pollo",
    "muslo de pollo": "muslos pollo",
    "pavo": "pechuga pavo",
    "ternera": "carne ternera",
    "carne picada": "carne picada",
    "cerdo": "lomo cerdo",
    "atun": "atun claro conserva lata",
    "atun en conserva": "atun claro lata",
    "atun al natural": "atun claro natural",
    "salmon": "salmon fresco",
    "salmon ahumado": "salmon ahumado",
    "merluza": "merluza",
    "bacalao": "bacalao",
    "gambas": "gambas",
    "langostinos": "langostinos",
    
    # Lácteos y Huevos
    "leche": "leche entera",
    "leche desnatada": "leche desnatada",
    "leche semidesnatada": "leche semidesnatada",
    "queso": "queso",
    "queso fresco": "queso fresco",
    "queso rallado": "queso rallado",
    "queso de untar": "queso untar",
    "quesito": "queso porciones",
    "yogur": "yogur natural",
    "yogur griego": "yogur griego natural",
    "mantequilla": "mantequilla",
    "nata": "nata cocinar",
    "huevo": "huevos",
    "huevos": "huevos",
    "huevo duro": "huevo cocido pelado",
    
    # Desayuno, Pan y Bebidas Vegetales
    "pan": "pan",
    "pan integral": "pan integral",
    "pan de molde": "pan molde",
    "avena": "copos avena",
    "harina": "harina trigo",
    "bebida de soja": "bebida soja",
    "bebida de avena": "bebida avena",
    "bebida de almendra": "bebida almendras",
    "cafe": "cafe molido",
    "te": "te verde",
    "cacao": "cacao puro polvo",
    "azucar": "azucar blanco",
    
    # Básicos de Despensa
    "aceite": "aceite oliva",
    "aceite de oliva": "aceite oliva virgen",
    "aceite de girasol": "aceite girasol",
    "vinagre": "vinagre",
    "vinagre de manzana": "vinagre manzana",
    "vinagre de arroz": "vinagre arroz",
    "sal": "sal fina",
    "sal gorda": "sal gruesa",
    "pimienta": "pimienta negra",
    "pimenton": "pimenton dulce",
    "oregano": "oregano",
    "canela": "canela molida",
    "ajetes": "ajo tierno",
    
    # Hidratos y Legumbres
    "pasta": "macarrones",
    "pasta integral": "macarrones integrales",
    "espaguetis": "espaguetis",
    "fideos": "fideos",
    "tallarines": "tallarines",
    "arroz": "arroz",
    "arroz blanco": "arroz",
    "arroz cocido": "arroz",
    "arroz blanco cocido": "arroz",
    "arroz integral": "arroz integral",
    "arroz basmati": "arroz basmati",
    "lenteja": "lentejas",
    "lentejas cocidas": "lentejas",
    "garbanzo": "garbanzos",
    "garbanzos cocidos": "garbanzos",
    "alubia": "alubias blancas",
    "quinoa": "quinoa",
    
    # Verduras y Hortalizas "Anti-Confusión"
    "espinaca": "espinacas frescas bolsa",
    "lechuga": "lechuga iceberg",
    "canónigos": "canonigos",
    "cebolla": "cebolla",
    "cebolla morada": "cebolla morada",
    "ajo": "ajos",
    "tomate": "tomate",
    "tomate cherry": "tomate cherry",
    "tomate triturado": "tomate triturado",
    "salsa de tomate": "tomate frito",
    "patata": "patatas",
    "boniato": "boniato",
    "calabacin": "calabacin",
    "calabaza": "calabaza",
    "pepino": "pepino",
    "tomate": "tomate",
    "tomate maduro": "tomate",
    "tomate pera": "tomate",
    "tomate ensalada": "tomate",
    "tomate triturado": "tomate triturado",
    "tomate frito": "tomate frito",
    "pimiento": "pimiento",
    "pimiento rojo": "pimiento rojo",
    "pimiento verde": "pimiento verde",
    "pimiento italiano": "pimiento verde",
    "brocoli": "brocoli",
    "coliflor": "coliflor",
    "champiñon": "champiñon entero",
    "limon": "limones",
    
    # Frutas y Especialidades
    "platano": "platano de canarias fruta",
    "manzana": "manzana",
    "pera": "pera",
    "naranja": "naranjas",
    "frambuesa": "frambuesas",
    "arandanos": "arandanos",
    "nuez": "nueces naturales peladas",
    "almendra": "almendra",
    "cacahuete": "cacahuete",
    "pasas": "uvas pasas",
    "tofu": "tofu firme",
    "soja texturizada": "soja texturizada",
    "alga nori": "alga nori",
    "edamame": "edamame congelado",
    "leche de coco": "leche de coco",
    "menta": "hierbabuena",
    "hierbabuena": "hierbabuena"
}

# Palabras a ignorar en la búsqueda (Limpia descripciones del LLM)
PALABRAS_A_IGNORAR = [
    # Adjetivos de estado / calidad
    "maduro", "maduros", "fresco", "fresca", "frescas", "frescos", "natural", "naturales",
    "de grano completo", "tipo", "estilo", "casero", "casera", "selección",
    "premium", "gourmet", "bio", "eco", "orgánico", "sano", "healthy", "artesano", "artesana",
    "virgen", "dulce", "ahumado", "picatostes", "tostado",
    "calidad", "extra", "superior", "especial", "variado", "mixto", "mezcla", "sabor",
    "congelado", "congelada", "congelados", "congeladas", "ultracongelado",
    "fuego", "lento", "receta", "tradicional", "abuela",

    # Unidades de medida y envases
    "un kilo de", "una docena de", "medio kilo de", "litro de", "un bote de", "un paquete de", "caja de",
    "en bote", "en lata", "en frasco", "en conserva", "g", "kg", "ml", "litro", "litros", "kilo", "kilos",
    "gramos", "mililitros", "taza", "cucharada", "cucharadita", "pizca", "vaso", "puñado", "chorrito",
    "lonchas", "filete", "filetes", "rodajas", "porción", "porciones", "trozo", "trozos", "tiras", "dados",
    "dientes de", "cabeza de", "manojo de", "hojas de", "rama de",

    # Artículos y preposiciones
    "un", "una", "unos", "unas", "de", "con", "el", "la", "en", "para", "del", "las", "los", "al", "y", "o",

    # Estados de procesamiento
    "pardina", "castellana", "pelado", "pelada", "peladas", "pelados", "entero", "entera", "troceado", "troceada", "picada",
    "cocido", "cocida", "cocidas", "cocidos", "lavada", "cortada", "limpio", "limpia", "rallado", "rallada", "molido", "batido",
    "en polvo", "al gusto", "punto de sal", "sin sal", "sin azucar", "desnatada", "semidesnatada",

    # Marcas blancas típicas
    "marca", "blanca", "hacendado", "dia", "carrefour", "alcampo"
]

PALABRAS_PROHIBIDAS_GLOBAL = [
    # Higiene y Cosmética
    "kinder", "juguete", "sorpresa", "corporal", "hidratante", "champú", "colonia", "perfume",
    "cosmetica", "limpieza", "perfumeria", "protector solar", "solar", "depilatoria", 
    "limpiadora", "capilar", "facial", "bocal", "dentifrico", "enjuague", "cepillo", "gel de baño",
    "desodorante", "maquillaje", "crema de noche", "serum", "mascarilla", "tampones", "compresas", 
    "toallitas", "pañales", "chupete", "biberon", "crema de manos",
    "chicle", "caramelo", "golosina", "chuche", "gragea"

    # Limpieza Hogar
    "detergente", "suavizante", "lavavajillas", "servilleta", "papel higienico", "friegasuelos",
    "lejia", "amoniaco", "estropajo", "bayeta", "basura", "ambientador", "insecticida", "quitagrasas",

    # Mascotas
    "mascota", "pateo", "pienso", "comida para", "arena para gatos", "perro", "gato", "suplemento",

    # Marcas/Líneas no alimentarias explícitas
    "deliplus", "bosque verde", "baby smile", "fresco y limpio"
]

CATEGORIAS_PROHIBIDAS = [
    # General & Supermercados
    "cosmetica", "perfumeria", "higiene", "cuidado corporal", "facial", "maquillaje", 
    "limpieza", "hogar", "mascotas", "fitoterapia", "parafarmacia", "bebe", "botiquin",
    "detergente", "suavizante", "lavavajillas", "insecticida", "ambientador",
    "bolsas de basura", "pilas", "bombillas", "celulosa", "papel higienico",
    "papeleria", "bazar", "menaje", "electrodomesticos", "textil", "calzado",
    # Rutas específicas de Supermercados (URL patterns)
    "limpieza-y-hogar", "perfumeria-higiene-salud", "mascotas", "bebe", "cuidado-del-hogar", "cuidado-personal"
]

# Configuración de precios y formatos
UMBRAL_PRECIO_NORMAL = 15.0 
MAX_PENALIZACION_FORMATO = 5.0 

OBLIGATORIAS_GLOBAL = [
    "leche", "vino", "aceite", "vinagre", "huevo", "pan", "harina", "queso", 
    "yogur", "pasta", "arroz", "cafe", "te", "agua", "sal", "azucar", "mantequilla",
    "platano", "atun", "espinaca", "lenteja", "garbanzo", "champinon", "canela", "nuez", "patata", "tomate", "cebolla"
]

# Proteínas
PROTEINAS_PESCADO = ["atun", "salmon", "merluza", "bacalao", "gambas", "langostinos", "pescado", "sepia", "calamar", "pulpo", "dorada", "lubina"]
PROTEINAS_CARNE = ["pollo", "pavo", "cerdo", "ternera", "vaca", "buey", "cordero", "conejo", "lomo", "hamburguesa", "costillas"]
PROTEINAS_CRÍTICAS = PROTEINAS_PESCADO + PROTEINAS_CARNE

MAPEO_CATEGORIAS = {
    "pollo": ["Carniceria", "Aves", "Pollo", "/carnes/pollo/", "aves-y-pollo"],
    "pavo": ["Carniceria", "Aves", "Pavo", "/carnes/pavo/"],
    "ternera": ["Carniceria", "Vacuno", "/carnes/vacuno/", "vacuno", "ternera"],
    "vaca": ["Carniceria", "Vacuno", "vacuno"],
    "cerdo": ["Carniceria", "Cerdo", "/carnes/cerdo/", "cerdo"],
    "lomo": ["Carniceria", "Cerdo", "/charcuteria-y-quesos/lomo/"],
    "pescado": ["Pescaderia", "Pescado", "/pescados-y-mariscos/"],
    "merluza": ["Pescaderia", "Pescado", "merluza-y-bacalao"],
    "salmon": ["Pescaderia", "Pescado", "salmon"],
    "bacalao": ["Pescaderia", "Pescado", "merluza-y-bacalao", "bacalao"],
    "leche": ["Lacteos", "Leche", "/huevos-leche-y-mantequilla/leche/"],
    "queso": ["Charcuteria", "Quesos", "/charcuteria-y-quesos/queso/"],
    "yogur": ["Postres", "Yogures", "/postres-y-yogures/"],
    "mantequilla": ["Lacteos", "Mantequilla", "margarina-y-mantequilla"],
    "huevo": ["Huevos", "/huevos-leche-y-mantequilla/huevos/"],
    "aceite": ["Aceite", "Vinagre", "Sal", "Alacena", "Despensa", "/aceites-salsas-y-especias/aceites/"],
    "vinagre": ["Aceite", "Vinagre", "/aceites-salsas-y-especias/vinagres-y-alinos/"],
    "arroz": ["Arroz", "Legumbres", "Pasta", "/arroz-pastas-y-legumbres/arroz/"],
    "pasta": ["Pasta", "Arroz", "/arroz-pastas-y-legumbres/pastas/"],
    "macarron": ["Pasta", "macarron"],
    "espagueti": ["Pasta", "espagueti"],
    "garbanzo": ["Legumbres", "Conservas", "/arroz-pastas-y-legumbres/garbanzos/", "garbanzos-y-alubias"],
    "lenteja": ["Legumbres", "Conservas", "/arroz-pastas-y-legumbres/lentejas/", "garbanzos-y-alubias"],
    "alubia": ["Legumbres", "Conservas", "/arroz-pastas-y-legumbres/alubias/", "garbanzos-y-alubias"],
    "verdura": ["Verdura", "Fruta", "/verduras/"],
    "patata": ["Fruta", "Verdura", "Tubercu", "patatas-y-zanahorias"],
    "tomate": ["Fruta", "Verdura", "/verduras/tomates"],
    "cebolla": ["Fruta", "Verdura", "ajos-cebollas-y-puerros"],
    "ajo": ["ajos-cebollas-y-puerros"],
    "fruta": ["Fruta", "/frutas/"],
    "manzana": ["Fruta", "manzanas-y-peras"],
    "platano": ["Fruta", "platanos-y-bananas"],
    "naranja": ["Fruta", "citricos"],
    "atun": ["Conservas", "Pescado", "atun-bonito-y-caballa"],
    "pan": ["Panaderia", "Horno", "/panes-harinas-y-masas/"],
    "harina": ["Panaderia", "Alacena", "harinas"],
    "alga": ["Sushi", "Mundo", "Internacional", "algas"],
    "edamame": ["Congelados", "Verdura", "soja"],
    "aguacate": ["Fruta", "Verdura", "Tropical"],
    "pimenton": ["Especias", "Aceite", "Alacena", "Condimentos"],
    "sal": ["Especias", "Alacena", "Condimentos", "Sal"],
    "especias": ["Especias", "Condimentos"],
    "oregano": ["Especias", "Condimentos", "especias-y-hierbas"],
    "azucar": ["Azucar", "Alacena", "Despensa", "azucar-y-edulcorantes"],
    "cafe": ["Cafe", "Alacena", "Despensa", "cafe-y-te"],
    "te": ["Te", "Infusiones", "Despensa", "cafe-y-te"]
}

MAPEO_ALERGIAS = {
    "marisco": ["mejillon", "gamba", "langostino", "percebe", "calamar", "pulpo", "sepia", "marisco", "almeja", "berberecho", "ostra", "crustaceo", "molusco", "bogavante", "cangrejo"],
    "gluten": ["trigo", "harina", "pan", "pasta", "galleta", "bizcocho", "cebada", "centeno", "avena", "espelta", "kamut", "seitan"],
    "lactosa": ["leche", "queso", "yogur", "nata", "mantequilla", "lactico", "lactosa", "kefir", "cuajada"],
    "huevo": ["huevo", "clara", "yema", "mayonesa", "tortilla"],
    "frutos secos": ["nuez", "almendra", "avellana", "pistacho", "cacahuete", "anacardo", "pecana", "macadamia", "castaña", "piñon", "fruto seco"],
    "soja": ["soja", "edamame", "tofu", "tempeh", "salsa de soja", "tamari"],
    "pescado": ["pescado", "atun", "salmon", "merluza", "bacalao", "trucha", "sardina", "boqueron", "anchoa", "lubina", "dorada", "rape", "lenguado"],
    "cacahuete": ["cacahuete", "mani", "mantequilla de cacahuete"],
    "apio": ["apio"],
    "mostaza": ["mostaza"],
    "sesamo": ["sesamo", "tahini", "ajonjoli"],
    "sulfitos": ["vino", "cerveza", "vinagre", "sidra"],
    "altramuz": ["altramuz", "altramuces"]
}

# Sinónimos Multi-Supermercado para puentear diferencias entre Mercadona y Dia
SINONIMOS_PRODUCTOS = {
    'ternera': ['vacuno', 'añojo', 'buey', 'burger meat'],
    'vaca': ['vacuno', 'ternera', 'añojo'],
    'cerdo': ['porcino'],
    'carne picada': ['burger meat', 'picadillo', 'preparado picado'],
    'alubia': ['judia blanca', 'faba', 'habichuela', 'fabada'],
    'judia verde': ['vaina', 'judias verdes'],
    'batata': ['boniato'],
    'boniato': ['batata'],
    'guisante': ['arveja'],
    'aguacate': ['palta'],
    'melocoton': ['durazno'],
    'pan de molde': ['pan blanco', 'pan bimbo'],
    'mantequilla': ['margarina'],
    'natillas': ['postre lacteo'],
    'pavo': ['fiambre de pavo', 'pechuga pavo'],
    'pollo': ['ave'],
    'muslos de pollo': ['jamoncitos', 'muslo'],
    'calabacin': ['zapallo'],
    'atun': ['bonito', 'caballa'],
    'lenteja': ['lentejas'],
    'garbanzo': ['garbanzos'],
    'macarrones': ['macarron', 'pasta', 'pluma', 'trigo'],
    'espaguetis': ['espagueti', 'pasta', 'tallarines', 'fideo', 'nidos'],
    'pasta': ['macarrones', 'espaguetis', 'pasta trigo', 'pennes'],
    'perejil': ['hierbas'],
    'atun': ['bonito', 'caballa', 'conserva', 'lata', 'claro'],
    'limon': ['limones'],
    'ajetes': ['ajo tierno'],
    'muesli': ['cereales con frutas', 'granola', 'cereales'],
    'quesito': ['queso porciones', 'quesitos'],
    'pasas': ['uvas pasas', 'pasa'],
    'pimenton': ['pimenton dulce', 'especias'],
    'platano': ['banana', 'canarias', 'platanos', 'fruta'],
    'menta': ['hierbabuena', 'menta fresca', 'hojas de menta'],
    'tomate': ['ensalada', 'pera', 'maduro', 'rama'],
    'pimiento': ['rojo', 'verde', 'italiano', 'lamuyo']
}
