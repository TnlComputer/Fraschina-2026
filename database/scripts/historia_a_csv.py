# Extrae a CSV las hojas "Diaria" (Diaria.xlsx) y "Ctrol" (Ctrl.xlsx) con los valores ya calculados del Excel.
# Uso: python historia_a_csv.py Diaria.xlsx Ctrl.xlsx carpeta_salida
# Cada CSV lleva la fila de origen y las columnas tal cual (texto), para que el comando de la app las cargue sin perder nada.
import openpyxl, csv, datetime, sys, pathlib

def texto(v):
    if v is None:
        return ''
    if isinstance(v, datetime.datetime):
        return v.strftime('%Y%m%d') if (v.hour, v.minute) == (0, 0) else v.strftime('%Y%m%d %H%M')
    if isinstance(v, datetime.time):
        return v.strftime('%H%M')
    if isinstance(v, float) and v.is_integer():
        return str(int(v))
    return str(v).strip()

def exportar(archivo, hoja, columnas, destino):
    ws = openpyxl.load_workbook(archivo, read_only=True, data_only=True)[hoja]
    n = 0
    with open(destino, 'w', newline='', encoding='utf-8') as fh:
        w = csv.writer(fh)
        w.writerow(['fila'] + columnas)
        for fila, f in enumerate(ws.iter_rows(min_row=2, max_col=len(columnas), values_only=True), start=2):
            vals = [texto(f[i]) if i < len(f) else '' for i in range(len(columnas))]
            if any(vals):
                w.writerow([fila] + vals)
                n += 1
    print(f'{hoja}: {n} filas -> {destino}')

salida = pathlib.Path(sys.argv[3])
exportar(sys.argv[1], 'Diaria', ['f', 'h', 'm', 'p', 'ra', 'c', 'es', 'fe', 'he', 'ord', 'pl', 'ca', 't', 'b', 'precio', 'd', 'o'], salida / 'diaria.csv')
exportar(sys.argv[2], 'Ctrol', ['fe', 'm', 'c', 'transporte', 'hc', 'o', 'g', 'kg_mol', 'kg_cte', 'kg_dif', 'remito', 'cte'], salida / 'ctrol.csv')
