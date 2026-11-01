FROM node:22-alpine
WORKDIR /app

# package-lock.json is picked up if present. Run `npm install` once and commit it,
# then switch this line to `npm ci --omit=dev` for reproducible builds.
COPY package*.json ./
RUN npm install --omit=dev

COPY src ./src

ENV NODE_ENV=production
EXPOSE 8080
USER node
CMD ["node", "src/server.js"]
