FROM node:22.14.0-bookworm-slim
WORKDIR /app

RUN npx --yes playwright@1.49.1 install-deps chromium
COPY package.json package-lock.json ./
RUN npm ci --omit=dev --no-audit --no-fund
COPY server.js ./
RUN useradd --uid 1000 --create-home --shell /usr/sbin/nologin app \
    && mkdir -p /app/session \
    && chown -R app:app /app

USER app
EXPOSE 3001
CMD ["node", "server.js"]
