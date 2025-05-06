FROM node:18-alpine

WORKDIR /app

COPY parser-frontend/package*.json ./

RUN npm install

COPY parser-frontend/ .

RUN npm run build

EXPOSE 777

CMD ["npm", "run", "dev"]
