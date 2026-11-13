#!/bin/bash

set -euo pipefail

SALIDA="codigoFuente.txt"

if [ ! -d "Controllers" ] && [ ! -d "Models" ] && [ ! -d "Views" ]
    then
    echo "Error: este script debe ejecutarse dentro de la carpeta app"
    exit 1
fi

: > "$SALIDA"

find Controllers Models Views -type f 2>/dev/null | sort | while IFS= read -r archivo
    do
    if [ "$archivo" = "$SALIDA" ]
        then
        continue
    fi

    {
    echo "============================================================"
    echo "ARCHIVO: $archivo"
    echo "============================================================"
    echo
    cat "$archivo"
    echo
    echo
    } >> "$SALIDA"
done

echo "Archivo generado: $SALIDA"
