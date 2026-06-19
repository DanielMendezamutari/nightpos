Bien. Tu carpeta pública es:

/home/vnplktsg/nightpos.ribersoft.com

Ahora copia el frontend compilado ahí.

1. Desde la raíz del proyecto NightPOS

Primero ubícate donde está el repo, por ejemplo:

cd /home/vnplktsg/nightpos

Verifica que exista el dist:

ls frontend/dist
2. Limpia la carpeta pública sin borrar backend si existe


cd /home/vnplktsg/nightpos.ribersoft.com

find . -maxdepth 1 -type f -name "*.md" -delete
rm -f admin-full-version.zip
rm -rf restaurant_bolivia-1

find /home/vnplktsg/nightpos.ribersoft.com -mindepth 1 ! -name backend -exec rm -rf {} +
3. Copia el frontend compilado
cp -r /home/vnplktsg/nightpos/frontend/dist/* /home/vnplktsg/nightpos.ribersoft.com/