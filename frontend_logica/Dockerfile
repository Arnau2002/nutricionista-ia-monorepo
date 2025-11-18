# Dockerfile para el Frontend (Asumiendo que usa Node/NPM)
FROM node:20-alpine

WORKDIR /app

# Instalar dependencias
COPY package.json .
RUN npm install

# Copiar el código del proyecto
COPY . .

# Comando para arrancar el servidor de frontend
# (Ajustar si usas npm start, yarn start, o un build final)
CMD ["npm", "run", "dev"]