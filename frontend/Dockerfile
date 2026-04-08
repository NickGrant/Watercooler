FROM node:24-alpine

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm install

COPY . .

EXPOSE 4200

CMD ["sh", "-c", "npm install && npm run start -- --host 0.0.0.0 --port 4200"]
