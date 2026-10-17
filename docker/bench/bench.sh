#!/usr/bin/env bash
# Latency and memory benchmark for the FrankenPHP modes (ADR-0013, US-006 / T-006-03 and T-006-06).
#
# Same pages, same load, same machine: run it once against the classic container and once against the worker one
# (FRANKENPHP_WORKER_MODE=1), then compare the two tables. Needs ApacheBench (ab) and a running instance with the
# demo fixtures. With CONTAINER set, the memory of the container (cgroup + sum of the process RSS (shared pages counted once per process), cron included)
# is sampled every second during the whole run.
#
# Usage: BASE=http://localhost:8090 CONTAINER=spike-classic [REQUESTS=600] [CONCURRENCY=4] docker/bench/bench.sh
set -u

BASE="${BASE:-http://localhost:8080}"
CONTAINER="${CONTAINER:-}"
REQUESTS="${REQUESTS:-600}"
CONCURRENCY="${CONCURRENCY:-4}"
WARMUP="${WARMUP:-60}"
PRODUCT="${PRODUCT:-/en_US/products/epi-chau-006-en}"
CATEGORY="${CATEGORY:-/en_US/taxons/quincaillerie}"
PASSWORD="${DEMO_PASSWORD:-Demo1234!}"
TMP="$(mktemp -d)"
SAMPLER=""
trap 'rm -rf "$TMP"; [ -n "$SAMPLER" ] && kill "$SAMPLER" 2>/dev/null' EXIT

# ab cannot reach "localhost" on macOS (IPv6 first): hit 127.0.0.1 and keep the original Host header.
AB_BASE="${BASE/localhost/127.0.0.1}"
AB_HOST="${BASE#*//}"

curl_() { /usr/bin/curl -s -H "Origin: $BASE" "$@"; }

# A B2C session holding a cart (quick-order plugin) and a logged-in B2B session (Claire), as cookie strings for ab.
cookie_of() { awk '$6=="PHPSESSID"{print $7}' "$1" | tail -1; }
JB="$TMP/cart.jar"; JA="$TMP/acct.jar"
curl_ -c "$JB" -b "$JB" -b demo_channel=B2C "$BASE/en_US/quick-order" -o "$TMP/qo.html"
tok="$(grep -o 'name="_csrf_token" value="[^"]*"' "$TMP/qo.html" | head -1 | sed 's/.*value="//;s/"//')"
curl_ -c "$JB" -b "$JB" -b demo_channel=B2C -X POST "$BASE/en_US/quick-order/preview" --data-urlencode "_csrf_token=$tok" \
    --data-urlencode "lines=EMBA-001;2
EMBA-002;1" -d mode=partial -o "$TMP/pv.html"
tok="$(grep -o 'name="_csrf_token" value="[^"]*"' "$TMP/pv.html" | head -1 | sed 's/.*value="//;s/"//')"
prev="$(grep -o 'name="preview" value="[^"]*"' "$TMP/pv.html" | sed 's/.*value="//;s/"//')"
curl_ -c "$JB" -b "$JB" -b demo_channel=B2C -X POST "$BASE/en_US/quick-order/add" --data-urlencode "_csrf_token=$tok" -d "preview=$prev" -d mode=partial -o /dev/null
curl_ -c "$JA" -b "$JA" "$BASE/en_US/login" -o /dev/null
curl_ -c "$JA" -b "$JA" -X POST "$BASE/en_US/login-check" -d _username=claire@acme.example --data-urlencode "_password=$PASSWORD" \
    -d _csrf_shop_security_token=csrf-token -o /dev/null

if [ -n "$CONTAINER" ]; then
    (while true; do
        cg="$(docker exec "$CONTAINER" cat /sys/fs/cgroup/memory.current 2>/dev/null)"
        rss="$(docker exec "$CONTAINER" sh -c 'cat /proc/[0-9]*/status 2>/dev/null' | awk '/^VmRSS/{s+=$2} END {print s*1024}')"
        echo "$cg $rss" >> "$TMP/mem.log"
        sleep 1
    done) &
    SAMPLER=$!
fi

printf '| Page | p50 (ms) | p95 (ms) | req/s | non-2xx |\n|---|---|---|---|---|\n'
bench() { # label path [cookie ...]
    local label="$1" path="$2"; shift 2
    local cookies=()
    for c in "$@"; do cookies+=(-C "$c"); done
    ab -q -n "$WARMUP" -c "$CONCURRENCY" -H "Host: $AB_HOST" "${cookies[@]}" "$AB_BASE$path" >/dev/null 2>&1
    ab -q -n "$REQUESTS" -c "$CONCURRENCY" -H "Host: $AB_HOST" "${cookies[@]}" "$AB_BASE$path" > "$TMP/ab.out" 2>&1
    local p50 p95 rps non2xx
    p50="$(awk '$1=="50%"{print $2}' "$TMP/ab.out")"
    p95="$(awk '$1=="95%"{print $2}' "$TMP/ab.out")"
    rps="$(awk '/Requests per second/{print $4}' "$TMP/ab.out")"
    non2xx="$(awk '/Non-2xx/{print $3}' "$TMP/ab.out")"
    printf '| %s | %s | %s | %s | %s |\n' "$label" "$p50" "$p95" "$rps" "${non2xx:-0}"
}

bench "Home B2B (anonymous)" "/en_US/"
bench "Category" "$CATEGORY"
bench "Product page" "$PRODUCT"
bench "Cart (B2C, 2 lines)" "/en_US/cart/" "PHPSESSID=$(cookie_of "$JB")" "demo_channel=B2C"
bench "Account (Claire, B2B)" "/en_US/account/dashboard" "PHPSESSID=$(cookie_of "$JA")"
bench "API route (tier prices, 401)" "/api/v2/shop/product-variants/EMBA-001/tier-prices"

if [ -n "$CONTAINER" ]; then
    kill "$SAMPLER" 2>/dev/null; SAMPLER=""
    echo
    awk '{ if ($1>cg) cg=$1; if ($2>rss) rss=$2; n++ } END { printf "Container memory over %d samples: cgroup peak %.0f MiB, process RSS sum peak %.0f MiB\n", n, cg/1048576, rss/1048576 }' "$TMP/mem.log"
    echo "Largest processes at the end of the run (RSS includes shared pages such as opcache):"
    docker exec "$CONTAINER" sh -c 'cat /proc/[0-9]*/status 2>/dev/null' \
        | awk '/^Name/{n=$2} /^VmRSS/{printf "%d %s\n", $2, n}' | sort -rn | head -6 | awk '{printf "  %s: %.0f MiB\n", $2, $1/1024}'
fi
