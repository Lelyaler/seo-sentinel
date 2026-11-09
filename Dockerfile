FROM php:8.2-cli

WORKDIR /app

# Copy project files
COPY . /app

# Environment port (Render / Cloud platforms pass $PORT)
ENV PORT=8000
EXPOSE 8000

# Run PHP built-in web server
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8000} -t public"]
