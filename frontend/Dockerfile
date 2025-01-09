# Usa a imagem base do Node.js 22.2.0
FROM node:22.2.0-alpine

# Define o diretório de trabalho dentro do contêiner
WORKDIR /usr/src/app

# Copia os arquivos package.json e package-lock.json para o diretório de trabalho
COPY package*.json ./

# Instala todas as dependências, incluindo as de desenvolvimento
RUN npm install

# Copia o restante dos arquivos do projeto
COPY . .

# Expõe a porta 8080 que o Vue CLI utilizará
EXPOSE 8080

# Comando para iniciar a aplicação com Vue CLI
CMD ["npm", "run", "start"]
