# Extrae los certificados de General.xlsx (hoja Registro) a CSV, con los valores ya calculados del Excel.
import openpyxl, csv, datetime, sys
origen, destino = sys.argv[1], sys.argv[2]
ws = openpyxl.load_workbook(origen, read_only=True, data_only=True)['Registro']
campos = ['fila', 'fecha', 'mo', 'cl', 'modo', 'prod', 'p', 'l', 'pl', 'w', 'gh', 'gs', 'gi', 'hum', 'cz', 'est', 'abs', 'fn', 'punt', 'particularidades']
columnas = [2, 3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20]   # 0 = A
n = 0
with open(destino, 'w', newline='', encoding='utf-8') as fh:
    w = csv.writer(fh)
    w.writerow(campos)
    for fila, f in enumerate(ws.iter_rows(max_col=21, values_only=True), start=1):
        if not isinstance(f[1], datetime.datetime):
            continue
        vals = []
        for c in columnas:
            v = f[c] if c < len(f) else None
            if isinstance(v, datetime.datetime):
                v = v.date().isoformat()
            vals.append('' if v is None else v)
        w.writerow([fila, f[1].date().isoformat()] + vals)
        n += 1
print('registros:', n)
