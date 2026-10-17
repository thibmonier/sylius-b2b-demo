#!/usr/bin/env bash
# State-leak test for the FrankenPHP worker mode (ADR-0013, US-006 / T-006-02).
#
# Replays alternating visitors against ONE running instance and fails when anything of a visitor
# (channel, cart, customer, locale, badge preference, session, demo gate) shows up for the next one.
# Start the instance with a single worker so every request lands on the same PHP process:
#   FRANKENPHP_WORKER_MODE=1 FRANKENPHP_WORKERS=1 FRANKENPHP_NUM_THREADS=4 FRANKENPHP_MAX_THREADS=4
# and with the demo gate off, or on with GATE_USER / GATE_PASS exported (extra gate checks).
#
# Usage: BASE=http://localhost:8080 [GATE_USER=u GATE_PASS=p] [ROUNDS=15] docker/bench/leak-test.sh
# Exit code: 0 = no leak, 1 = at least one leak (each is printed as "LEAK: ..."), 2 = target unreachable.
set -u

BASE="${BASE:-http://localhost:8080}"
ROUNDS="${ROUNDS:-15}"
PRODUCT="${PRODUCT:-/en_US/products/epi-chau-006-en}"
SKUS="${SKUS:-EMBA-001;2
EMBA-002;1}"
PASSWORD="${DEMO_PASSWORD:-Demo1234!}"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT
FAILURES=0
CHECKS=0

# curl with the cookie jar $1 and, when the gate is on, the basic-auth credentials
req() {
    local jar="$1"; shift
    local auth=()
    [ -n "${GATE_USER:-}" ] && [ "${NOAUTH:-0}" != 1 ] && auth=(-u "${GATE_USER}:${GATE_PASS}")
    /usr/bin/curl -s -b "$jar" -c "$jar" -H "Origin: $BASE" "${auth[@]}" "$@"
}

ok() { CHECKS=$((CHECKS + 1)); }
leak() { CHECKS=$((CHECKS + 1)); FAILURES=$((FAILURES + 1)); echo "LEAK: $*"; }
# expect_has FILE PATTERN LABEL / expect_lacks FILE PATTERN LABEL
expect_has() { if grep -qE -- "$2" "$1"; then ok; else leak "$3 (missing: $2)"; fi; }
expect_lacks() { if grep -qE -- "$2" "$1"; then leak "$3 (found: $2)"; else ok; fi; }

login() { # jar email [channel]
    local channel="${3:-B2B}"
    req "$1" -b "demo_channel=$channel" "$BASE/en_US/login" -o /dev/null
    req "$1" -b "demo_channel=$channel" -X POST "$BASE/en_US/login-check" -d "_username=$2" --data-urlencode "_password=$PASSWORD" \
        -d _csrf_shop_security_token=csrf-token -o /dev/null
}

fill_cart() { # jar : B2C channel, two SKUs through the quick-order plugin
    local jar="$1" token preview
    req "$jar" -b demo_channel=B2C "$BASE/en_US/quick-order" -o "$TMP/qo.html"
    token="$(grep -o 'name="_csrf_token" value="[^"]*"' "$TMP/qo.html" | head -1 | sed 's/.*value="//;s/"//')"
    req "$jar" -b demo_channel=B2C -X POST "$BASE/en_US/quick-order/preview" --data-urlencode "_csrf_token=$token" \
        --data-urlencode "lines=$SKUS" -d mode=partial -o "$TMP/pv.html"
    token="$(grep -o 'name="_csrf_token" value="[^"]*"' "$TMP/pv.html" | head -1 | sed 's/.*value="//;s/"//')"
    preview="$(grep -o 'name="preview" value="[^"]*"' "$TMP/pv.html" | sed 's/.*value="//;s/"//')"
    local code
    code="$(req "$jar" -b demo_channel=B2C -X POST "$BASE/en_US/quick-order/add" --data-urlencode "_csrf_token=$token" \
        -d "preview=$preview" -d mode=partial -o /dev/null -w '%{http_code}')"
    # A failed add is a defect of the mode under test (ADR-0013: 500 in worker mode), not a silent pass.
    case "$code" in
        200|302|303) ;;
        *) echo "FAIL quick-order/add answered $code"; FAILURES=$((FAILURES + 1)) ;;
    esac
}

session_of() { grep -o 'PHPSESSID[[:space:]]*[0-9a-f]*' "$1" | awk '{print $2}' | tail -1; }

echo "Target: $BASE (rounds: $ROUNDS, gate: $([ -n "${GATE_USER:-}" ] && echo on || echo off))"

# A dead target would look like one leak per check: stop before.
if [ "$(req "$TMP/probe.jar" "$BASE/healthz" -o /dev/null -w '%{http_code}')" != 200 ]; then
    echo "Target $BASE is not answering on /healthz: no test run." >&2
    exit 2
fi

# --- Visitor A: B2C, cart filled, badges off, French ---------------------------------------
JA="$TMP/a.jar"; JB="$TMP/b.jar"; JC="$TMP/c.jar"; JD="$TMP/d.jar"
: > "$JA"; : > "$JB"; : > "$JC"; : > "$JD"
fill_cart "$JA"
req "$JA" -b "demo_channel=B2C; demo_badges=off" "$BASE/fr_FR/cart/" -o "$TMP/a_cart.html"
expect_has "$TMP/a_cart.html" 'EMBA-001' "setup: visitor A has no cart"
SESSION_A="$(session_of "$JA")"

for round in $(seq 1 "$ROUNDS"); do
    # A (B2C, badges off, fr_FR) then B (anonymous, no cookie) on the same worker
    req "$JA" -b "demo_channel=B2C; demo_badges=off" "$BASE$PRODUCT" -o "$TMP/a_prod.html"
    expect_has "$TMP/a_prod.html" 'sylius_shop_add_to_cart' "round $round: A lost the B2C channel"
    expect_lacks "$TMP/a_prod.html" 'data-demo-plugin-badge' "round $round: A badges off ignored"

    req "$JA" -b "demo_channel=B2C; demo_badges=off" "$BASE/fr_FR/" -o "$TMP/a_fr.html"
    expect_has "$TMP/a_fr.html" '<html lang="fr"' "round $round: A page not in French"

    req "$JB" "$BASE$PRODUCT" -o "$TMP/b_prod.html"
    expect_has "$TMP/b_prod.html" 'data-test-tmonier-prices-hidden' "round $round: B inherited the channel of A (B2B expected)"
    expect_lacks "$TMP/b_prod.html" 'sylius_shop_add_to_cart' "round $round: B sees the B2C buy form"
    expect_has "$TMP/b_prod.html" '<html lang="en"' "round $round: B inherited the locale of A"
    expect_has "$TMP/b_prod.html" 'data-demo-plugin-badge' "round $round: B inherited the badge preference of A"

    req "$JB" "$BASE/en_US/cart/" -o "$TMP/b_cart.html"
    expect_lacks "$TMP/b_cart.html" 'EMBA-00' "round $round: B sees the cart of A"
    [ "$(session_of "$JB")" != "$SESSION_A" ] && ok || leak "round $round: B got the session of A"
done

# --- Customer then anonymous: B2B account, then a visitor without a session ---------------------
login "$JC" claire@acme.example
req "$JC" "$BASE/en_US/account/dashboard" -o "$TMP/c_dash.html" -w '%{http_code}' > "$TMP/c_code"
[ "$(cat "$TMP/c_code")" = 200 ] && ok || leak "setup: Claire could not log in ($(cat "$TMP/c_code"))"
expect_has "$TMP/c_dash.html" 'Claire Dupont</strong>' "setup: dashboard of Claire"

for round in $(seq 1 "$ROUNDS"); do
    req "$JC" "$BASE/en_US/account/dashboard" -o /dev/null
    : > "$JD"
    code="$(req "$JD" "$BASE/en_US/account/dashboard" -o "$TMP/d_dash.html" -w '%{http_code}')"
    [ "$code" = 302 ] && ok || leak "round $round: anonymous visitor reached the account page after Claire (HTTP $code)"
    req "$JD" "$BASE/en_US/" -o "$TMP/d_home.html"
    expect_lacks "$TMP/d_home.html" 'Claire Dupont</strong>|<div>claire@acme.example</div>' "round $round: anonymous visitor sees the identity of Claire"
done

# --- Customer B2B then B2C customer: identity, channel and cart switch cleanly --------------------
: > "$JD"
login "$JD" lea@demo.example B2C
req "$JD" -b demo_channel=B2C "$BASE/en_US/account/dashboard" -o "$TMP/lea.html"
expect_has "$TMP/lea.html" 'Léa Petit</strong>' "setup: Léa dashboard"
expect_lacks "$TMP/lea.html" 'Claire Dupont</strong>' "Léa sees Claire's identity"

# --- Locale: fr_FR then en_US then the bare path, on the same worker -----------------------------------
req "$JB" "$BASE/fr_FR/" -o "$TMP/fr.html"
expect_has "$TMP/fr.html" '<html lang="fr"' "fr_FR page not in French"
req "$JC" "$BASE/en_US/" -o "$TMP/en.html"
expect_has "$TMP/en.html" '<html lang="en"' "en_US page after fr_FR not in English"
: > "$JB"
redirect="$(req "$JB" "$BASE/" -o /dev/null -w '%{redirect_url}')"
[[ "$redirect" == */fr_FR/ ]] && ok || leak "bare path redirects to '$redirect' instead of the default locale"

# --- Demo gate: authorised visitor then unauthenticated and wrong credentials --------------------------
if [ -n "${GATE_USER:-}" ]; then
    [ "$(req "$TMP/g.jar" "$BASE/en_US/" -o /dev/null -w '%{http_code}')" = 200 ] && ok || leak "setup: gate refuses valid credentials"
    for round in $(seq 1 "$ROUNDS"); do
        req "$TMP/g.jar" "$BASE/en_US/" -o /dev/null
        code="$(NOAUTH=1 req "$TMP/g2.jar" "$BASE/en_US/" -o /dev/null -w '%{http_code}')"
        [ "$code" = 401 ] && ok || leak "round $round: gate open for an unauthenticated visitor after an authorised one (HTTP $code)"
    done
    code="$(/usr/bin/curl -s -u "$GATE_USER:wrong-$RANDOM" -o /dev/null -w '%{http_code}' "$BASE/en_US/")"
    [ "$code" = 401 ] && ok || leak "gate accepts a wrong password after valid ones (HTTP $code)"
fi

echo "Checks: $CHECKS, leaks: $FAILURES"
[ "$FAILURES" -eq 0 ]
