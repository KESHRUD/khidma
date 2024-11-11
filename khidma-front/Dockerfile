FROM node:20.18.0

WORKDIR /app

# Installation des dépendances globales
RUN npm install -g @angular/cli@18

# Copie des fichiers de configuration
COPY package*.json ./

# Installation des dépendances
RUN npm install

# Copie du reste du code
COPY . .

# Exposition du port
EXPOSE 4200

# Commande par défaut
CMD ["ng", "serve", "--host", "0.0.0.0", "--poll", "1"]