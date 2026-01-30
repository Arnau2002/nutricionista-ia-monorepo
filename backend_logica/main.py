from fastapi import FastAPI, HTTPException
from fastapi.responses import PlainTextResponse
from fastapi.middleware.cors import CORSMiddleware
import requests
import json
import pandas as pd
from io import StringIO
from typing import List, Dict
from pydantic import BaseModel

app = FastAPI(
    title="Mercadona Category API",
    version="1.0.0",
    description="Expone datos de categorías de Mercadona en formato JSON o CSV."
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

API_BASE = "https://tienda.mercadona.es/api/categories/{category}/"

def fetch_category(category_id: int, lang: str = "es", wh: str = "vlc1", timeout: int = 10) -> dict:
    url = API_BASE.format(category=category_id)
    params = {"lang": lang, "wh": wh}
    try:
        r = requests.get(url, params=params, timeout=timeout)
        r.raise_for_status()
    except requests.exceptions.RequestException as e:
        raise HTTPException(status_code=502, detail=f"Error al obtener datos de Mercadona: {e}")

    try:
        return r.json()
    except json.JSONDecodeError:
        raise HTTPException(status_code=502, detail="Respuesta JSON inválida desde Mercadona.")

def flatten_products(data: dict) -> list[dict]:
    categoria_principal = data.get("name", "N/A")
    productos = []
    for subcategoria in data.get("categories", []):
        nombre_subcategoria = subcategoria.get("name")
        for producto in subcategoria.get("products", []):
            precios = producto.get("price_instructions", {}) or {}
            productos.append({
                "Categoria": categoria_principal,
                "Subcategoria": nombre_subcategoria,
                "Nombre_Producto": producto.get("display_name", ""),
                "Envase": producto.get("packaging", ""),
                "Precio_Total": precios.get("unit_price"),
                "Precio_Referencia": precios.get("reference_price"),
                "Formato_Referencia": precios.get("reference_format", "")
            })
    return productos

@app.get("/health")
def health():
    return {"status": "ok"}

@app.get("/mercadona/category/{category_id}")
def get_category(category_id: int, lang: str = "es", wh: str = "vlc1"):
    data = fetch_category(category_id, lang, wh)
    filas = flatten_products(data)
    return {
        "categoria": data.get("name"),
        "category_id": category_id,
        "total": len(filas),
        "productos": filas
    }

@app.get("/mercadona/category/{category_id}/best_prices")
def best_prices(category_id: int, lang: str = "es", wh: str = "vlc1"):
    data = fetch_category(category_id=category_id, lang=lang, wh=wh)
    filas = flatten_products(data)

    mejores: Dict[str, dict] = {}
    for p in filas:
        sub = p.get("Subcategoria")
        name = p.get("Nombre_Producto")
        price = p.get("Precio_Total")
        if sub is None or name is None or price is None:
            continue
        try:
            price_num = float(price)
        except (TypeError, ValueError):
            try:
                price_num = float(str(price).replace("€", "").replace(",", "."))
            except Exception:
                continue

        if sub not in mejores or price_num < mejores[sub]["price"]:
            mejores[sub] = {
                "subcategory_name": sub,
                "subcategory_id": None,
                "name": name,
                "price": price_num,
                "currency": "€",
            }

    return {"items": list(mejores.values())}


# Modelo de lo que te envía el Frontend
class UserQuery(BaseModel):
    query: str          # Ej: "Desayuno sin gluten"
    intolerancias: List[str] = [] # Ej: ["gluten", "lactosa"]

@app.post("/recommend")
def recommend_products(request: UserQuery):
    # 1. AQUÍ IMPRIMES PARA VER SI LLEGA
    print(f"Buscando: {request.query} con filtros: {request.intolerancias}")

    # 2. (MOCK) Por ahora devuelve datos falsos para que el Miembro 3 pueda trabajar
    return [
        {"name": "Leche de Soja", "price": 1.20, "supermarket": "Mercadona", "reason": "Es vegana"},
        {"name": "Galletas Avena", "price": 1.50, "supermarket": "DIA", "reason": "Bajas en azúcar"}
    ]
