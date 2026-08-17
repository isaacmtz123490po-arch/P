from flask import Flask, render_template, request, send_file, jsonify
import barcode
from barcode.writer import ImageWriter
import random
import json
import os
from datetime import datetime
import io

app = Flask(__name__)

# Archivos para guardar datos
ARCHIVO_NUMEROS = "numeros_generados.json"
ARCHIVO_SENSIBILIDADES = "sensibilidades_generadas.json"

# Cargar números ya generados para evitar repeticiones
def cargar_numeros():
    if os.path.exists(ARCHIVO_NUMEROS):
        with open(ARCHIVO_NUMEROS, 'r') as f:
            return set(json.load(f))
    return set()

def guardar_numeros(numeros):
    with open(ARCHIVO_NUMEROS, 'w') as f:
        json.dump(list(numeros), f)

# Cargar sensibilidades guardadas
def cargar_sensibilidades():
    if os.path.exists(ARCHIVO_SENSIBILIDADES):
        with open(ARCHIVO_SENSIBILIDADES, 'r') as f:
            return json.load(f)
    return []

def guardar_sensibilidad(sensibilidad):
    sensibilidades = cargar_sensibilidades()
    sensibilidades.append(sensibilidad)
    with open(ARCHIVO_SENSIBILIDADES, 'w') as f:
        json.dump(sensibilidades, f, indent=2)

# Generar número único de N dígitos
def generar_numero_unico(cantidad_digitos=12):
    numeros_usados = cargar_numeros()
    
    # Calcular rango según cantidad de dígitos
    minimo = 10 ** (cantidad_digitos - 1)
    maximo = (10 ** cantidad_digitos) - 1
    
    # Verificar que haya números disponibles
    if len(numeros_usados) >= (maximo - minimo + 1):
        return None, "No hay más números únicos disponibles para esta cantidad de dígitos"
    
    # Generar número único
    intentos = 0
    while intentos < 10000:
        numero = str(random.randint(minimo, maximo)).zfill(cantidad_digitos)
        if numero not in numeros_usados:
            numeros_usados.add(numero)
            guardar_numeros(numeros_usados)
            return numero, None
        intentos += 1
    
    return None, "No se pudo generar un número único después de muchos intentos"

# Generar código de barras
def generar_codigo_barras(numero):
    # Usar formato Code128 (admite todos los números)
    codigo_clase = barcode.get_barcode_class('code128')
    codigo = codigo_clase(numero, writer=ImageWriter())
    
    # Guardar en memoria en lugar de archivo
    buffer = io.BytesIO()
    codigo.write(buffer, options={'write_text': True, 'font_size': 10, 'text_distance': 1.0})
    buffer.seek(0)
    return buffer

# Generar sensibilidades variadas
def generar_sensibilidades(tipo="aleatorio", cantidad=10, rango_min=0, rango_max=100):
    """
    Tipos de sensibilidad:
    - aleatorio: Valores aleatorios en el rango
    - lineal: Valores incrementales
    - logaritmico: Valores con distribución logarítmica
    - gaussiano: Valores con distribución normal
    - por_niveles: Valores agrupados por niveles de sensibilidad
    """
    sensibilidades = []
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    
    if tipo == "aleatorio":
        for i in range(cantidad):
            valor = round(random.uniform(rango_min, rango_max), 4)
            sensibilidades.append({
                "id": i + 1,
                "valor": valor,
                "nivel": clasificar_sensibilidad(valor, rango_min, rango_max),
                "tipo": "aleatorio"
            })
    
    elif tipo == "lineal":
        paso = (rango_max - rango_min) / (cantidad - 1) if cantidad > 1 else 0
        for i in range(cantidad):
            valor = round(rango_min + (i * paso), 4)
            sensibilidades.append({
                "id": i + 1,
                "valor": valor,
                "nivel": clasificar_sensibilidad(valor, rango_min, rango_max),
                "tipo": "lineal"
            })
    
    elif tipo == "logaritmico":
        import math
        for i in range(cantidad):
            # Distribución logarítmica (más valores en el extremo bajo)
            ratio = (i + 1) / cantidad
            valor_log = math.log10(1 + ratio * 9)  # Log10 de 1 a 10
            valor = round(rango_min + valor_log * (rango_max - rango_min) / 1, 4)
            sensibilidades.append({
                "id": i + 1,
                "valor": valor,
                "nivel": clasificar_sensibilidad(valor, rango_min, rango_max),
                "tipo": "logarítmico"
            })
    
    elif tipo == "gaussiano":
        import statistics
        media = (rango_min + rango_max) / 2
        desviacion = (rango_max - rango_min) / 6  # ~99.7% dentro del rango
        
        for i in range(cantidad):
            valor = round(random.gauss(media, desviacion), 4)
            # Asegurar que esté dentro del rango
            valor = max(rango_min, min(rango_max, valor))
            sensibilidades.append({
                "id": i + 1,
                "valor": valor,
                "nivel": clasificar_sensibilidad(valor, rango_min, rango_max),
                "tipo": "gaussiano"
            })
    
    elif tipo == "por_niveles":
        niveles = ["Muy baja", "Baja", "Media", "Alta", "Muy alta"]
        cantidad_por_nivel = cantidad // len(niveles)
        resto = cantidad % len(niveles)
        
        id_actual = 1
        for idx_nivel, nivel in enumerate(niveles):
            num_en_nivel = cantidad_por_nivel + (1 if idx_nivel < resto else 0)
            
            # Calcular sub-rango para este nivel
            rango_nivel = (rango_max - rango_min) / len(niveles)
            sub_min = rango_min + idx_nivel * rango_nivel
            sub_max = rango_min + (idx_nivel + 1) * rango_nivel
            
            for _ in range(num_en_nivel):
                valor = round(random.uniform(sub_min, sub_max), 4)
                sensibilidades.append({
                    "id": id_actual,
                    "valor": valor,
                    "nivel": nivel,
                    "tipo": "por_niveles"
                })
                id_actual += 1
    
    # Guardar la generación
    registro = {
        "timestamp": timestamp,
        "tipo": tipo,
        "cantidad": cantidad,
        "rango": [rango_min, rango_max],
        "sensibilidades": sensibilidades
    }
    guardar_sensibilidad(registro)
    
    return sensibilidades, timestamp

def clasificar_sensibilidad(valor, rango_min, rango_max):
    """Clasifica el valor en niveles de sensibilidad"""
    rango_total = rango_max - rango_min
    if rango_total == 0:
        return "Único"
    
    posicion = (valor - rango_min) / rango_total
    
    if posicion < 0.2:
        return "Muy baja"
    elif posicion < 0.4:
        return "Baja"
    elif posicion < 0.6:
        return "Media"
    elif posicion < 0.8:
        return "Alta"
    else:
        return "Muy alta"

# Rutas web
@app.route('/')
def index():
    return render_template('index.html')

@app.route('/generar_codigo', methods=['POST'])
def generar_codigo():
    datos = request.get_json()
    cantidad_digitos = datos.get('cantidad_digitos', 12)
    
    numero, error = generar_numero_unico(int(cantidad_digitos))
    
    if error:
        return jsonify({"error": error}), 400
    
    buffer_imagen = generar_codigo_barras(numero)
    
    # Guardar imagen temporalmente para enviar
    nombre_archivo = f"codigo_{numero}.png"
    ruta_temporal = os.path.join("static", nombre_archivo)
    os.makedirs("static", exist_ok=True)
    
    with open(ruta_temporal, 'wb') as f:
        f.write(buffer_imagen.getvalue())
    
    return jsonify({
        "numero": numero,
        "imagen_url": f"/static/{nombre_archivo}",
        "total_generados": len(cargar_numeros())
    })

@app.route('/generar_sensibilidades', methods=['POST'])
def generar_sensibilidades_route():
    datos = request.get_json()
    tipo = datos.get('tipo', 'aleatorio')
    cantidad = int(datos.get('cantidad', 10))
    rango_min = float(datos.get('rango_min', 0))
    rango_max = float(datos.get('rango_max', 100))
    
    if rango_min >= rango_max:
        return jsonify({"error": "El rango mínimo debe ser menor que el máximo"}), 400
    
    if cantidad < 1 or cantidad > 1000:
        return jsonify({"error": "La cantidad debe estar entre 1 y 1000"}), 400
    
    sensibilidades, timestamp = generar_sensibilidades(tipo, cantidad, rango_min, rango_max)
    
    return jsonify({
        "sensibilidades": sensibilidades,
        "timestamp": timestamp,
        "total_generados": len(cargar_sensibilidades())
    })

@app.route('/ver_numeros_generados')
def ver_numeros_generados():
    numeros = sorted(list(cargar_numeros()))
    return jsonify({
        "cantidad": len(numeros),
        "numeros": numeros[-50:]  # Mostrar últimos 50
    })

@app.route('/ver_sensibilidades_generadas')
def ver_sensibilidades_generadas():
    sensibilidades = cargar_sensibilidades()
    return jsonify({
        "cantidad_registros": len(sensibilidades),
        "registros": sensibilidades[-10:]  # Mostrar últimos 10 registros
    })

@app.route('/reiniciar_numeros', methods=['POST'])
def reiniciar_numeros():
    """Elimina todos los números guardados (solo para pruebas)"""
    if os.path.exists(ARCHIVO_NUMEROS):
        os.remove(ARCHIVO_NUMEROS)
    return jsonify({"mensaje": "Números reiniciados correctamente"})

if __name__ == '__main__':
    os.makedirs("static", exist_ok=True)
    app.run(debug=True, host='0.0.0.0', port=5000)
