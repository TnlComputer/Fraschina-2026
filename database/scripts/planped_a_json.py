# Vista previa (solo lectura): textos de Plan ped unidos en hasta 6 renglones de 200 caracteres por cliente.
import openpyxl, re, json, sys
wb = openpyxl.load_workbook(sys.argv[1], read_only=True, data_only=True)
INICIOS = ('ENVIAR', 'ENNVIAR', 'IMPORTANTE', 'NOTA', 'MUCHAS', 'LUGAR DE ENTREGA', 'CUIT', 'POR FAVOR', 'RECORDAR', 'ES OBLIGATORIO',
           'DOCUMENTACI', 'LAS PERSONAS', 'TENER ESPECIAL', 'EL CLIENTE', 'SE ANUNCIAN', 'CALIDAD', 'SEG', 'HARINA', 'GH:', 'GLUTEN',
           'MUY IMPORTANTE', 'N° DE OC', 'Nº DE OC', 'N\xba DE OC', 'EL N', 'Y N', 'CERTIFICADO DE LIMPIEZA')
salida = {}
for ws in wb.worksheets:
    filas = [[str(c).strip() if c is not None else '' for c in f] for f in ws.iter_rows(values_only=True)]
    cab = next((i for i, f in enumerate(filas) if 'Tolva' in f), None)
    if cab is None: continue
    cliente = next((f[f.index('CLIENTE:') + 1] for f in filas if 'CLIENTE:' in f and f.index('CLIENTE:') + 1 < len(f)), '')
    if not cliente: continue
    ci = filas[cab].index('Observaciones')
    renglones = [f[ci] for f in filas[cab + 1:] if ci < len(f) and f[ci]]
    # 1. párrafos: un renglón nuevo empieza párrafo si el anterior terminó en . o : o si arranca con una palabra típica de inicio
    parrafos = []
    for r in renglones:
        nuevo = not parrafos or parrafos[-1].endswith(('.', ':')) or r.upper().startswith(INICIOS)
        if nuevo:
            parrafos.append(r)
        elif parrafos[-1].endswith('-'):
            parrafos[-1] = parrafos[-1][:-1] + r          # palabra cortada con guión: HORA- / RIO
        else:
            parrafos[-1] += ' ' + r
    parrafos = [re.sub(r'\s+', ' ', p).strip() for p in parrafos]
    # 2. hasta 6 renglones de 200: se juntan párrafos consecutivos mientras entren
    lineas = []
    for p in parrafos:
        if lineas and (len(parrafos) - parrafos.index(p) + len(lineas) > 6 or len(lineas) == 6) and len(lineas[-1]) + 1 + len(p) <= 200:
            lineas[-1] += ' ' + p
        else:
            lineas.append(p)
    # notas a la derecha (por molino) y datos de las otras columnas, tal cual
    notas = [x for f in filas[cab:] for j, x in enumerate(f) if x and j > ci]
    otros = [x for f in filas[cab + 1:] for j, x in enumerate(f) if x and j < ci]
    salida[ws.title] = {'cliente': cliente, 'lineas': lineas, 'entra': len(lineas) <= 6 and all(len(l) <= 200 for l in lineas), 'notas_derecha': notas, 'otras_columnas': otros}
json.dump(salida, open(sys.argv[2], 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
for hoja, d in salida.items():
    print(f"{hoja:16} {d['cliente'][:24]:24} renglones={len(d['lineas'])} {'ok' if d['entra'] else 'NO ENTRA'} notas_molino={'si' if d['notas_derecha'] else '-'}")
