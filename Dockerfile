FROM php:8.2-cli

WORKDIR /app
COPY . /app

# Linux file names are case-sensitive. Add a capitalised alias for every file in
# includes/ and models/ so both Database.php and database.php work.
SHELL ["/bin/bash", "-c"]
RUN for d in includes models; do \
      for f in /app/$d/*.php; do \
        b=$(basename "$f"); c="${b:0:1}"; c="${c^^}${b:1}"; \
        [ -e "/app/$d/$c" ] || ln -s "$b" "/app/$d/$c"; \
      done; \
    done

EXPOSE 10000

# Write .env from the host's environment variables, then start the server.
CMD ["bash", "-c", "if [ -n \"$SUPABASE_URL\" ] && [ -n \"$SUPABASE_ANON_KEY\" ]; then printf 'SUPABASE_URL=%s\\nSUPABASE_ANON_KEY=%s\\n' \"$SUPABASE_URL\" \"$SUPABASE_ANON_KEY\" > /app/.env; fi; exec php -S 0.0.0.0:${PORT:-10000} -t /app /app/router.php"]
