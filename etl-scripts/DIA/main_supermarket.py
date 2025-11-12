from excel_data import *
from supermarket.dia import *
from datetime import *
from dotenv import load_dotenv
import pandas as pd

URL_CATEGORY_DIA = "https://www.dia.es/api/v1/plp-insight/initial_analytics/charcuteria-y-quesos/jamon-cocido-lacon-fiambres-y-mortadela/c/L2001?navigation=L2001"
URL_PRODUCTS_BY_CATEGORY_DIA = "https://www.dia.es/api/v1/plp-back/reduced"


LIST_IDS_FAVORITES_PRODUCTS_DIA = []


def check_favortites_products(df_supermercados):
    # Crear la columna 'Favorito' e inicializarla como False
    df_supermercados['Favorito'] = False

    # Marcar como True los registros con los Ids especificados
    df_supermercados.loc[df_supermercados['Id'].isin(LIST_IDS_FAVORITES_PRODUCTS_DIA), 'Favorito'] = True
    
    return df_supermercados


def checkContions(ruta_env, ruta):
    # Si la carpeta export no existe
    if not os.path.exists(ruta):
        print("En la raiz del proyecto debe haber una carpeta llamada 'export'")
        return False
    
    # Si el archivo .env no existe
    if not os.path.exists(ruta_env):
        print("En la raiz del proyecto debe encontrarse el archivo .env del proyecto")
        return False
    
    # Si la carpeta export no existe
    if not os.path.exists(ruta):
        print("En la raiz del proyecto debe haber una carpeta llamada 'export'")
        return False
    
    # Si la variable de sesion COOKIE_DIA no existe
    if os.getenv('COOKIE_DIA') is None:
        print("En el archivo .env debe existir una variable llamada 'COOKIE_DIA' con la cookie correspondiente a Dia.")
        return False
    
    if os.getenv('COOKIE_DIA') == "TU_COOKIE_DIA":
        print("Debe cumplimentar en el archivo .env la variable llamada 'COOKIE_DIA' con la cookie correspondiente a Dia. Para mas informacion consulte el archivo 'Guia env.pdf'")
        return False
    
    return True
    
if __name__ == "__main__":

    load_dotenv()
    
    ruta_actual = os.getcwd()
    ruta = ruta_actual + "\export\\"
    ruta_env = ruta_actual + "\.env"
    
    if checkContions(ruta_env, ruta) == True:

        print("")
        print("------------------------------------DIA------------------------------------")
        print("")
        df_dia = gestion_dia(ruta)
        print("")

       
       
        
        #Marcar los productos favoritos
        df_supermercados = check_favortites_products(df_dia)
        
        #Export Excel
        export_excel(df_supermercados, ruta, "products", "Productos")