// Railway infrastructure of the demo (US-032, ADR-0002), applied with `railway config plan` / `apply`.
// Secrets are never written here: they are set in Railway (`railway variables --set`) and kept by preserve().
// Mailpit has no public domain: the web service reaches it on the private network only (US-028).
import { defineRailway, github, image, mysql, preserve, project, service } from "railway/iac";

const REGION = "europe-west4";

// US-050: the one switch between the private demo (HTTP gate on, registrations open) and the public demo (gate off,
// registrations closed, search engines asked to stay away). Stays false until the explicit GO of the owner; the steps
// are in docs/ouverture-publique.md. The preflight of the container refuses a public demo with open registrations.
const OPEN_TO_PUBLIC = false;

// US-066: canonical host of the demo for absolute URLs built outside a request (e-mails sent by the queue, fixtures).
// Stays false while the Railway domain is the reference; true once the custom host (DEMO_PUBLIC_HOST) answers with a
// valid certificate, at the switch (docs/sous-domaine-demo.md). Both hosts are accepted either way.
const CANONICAL_IS_PUBLIC_HOST = false;

// Fail at plan time rather than deploy a DEFAULT_URI of `https://` with nothing behind it.
if (CANONICAL_IS_PUBLIC_HOST && !process.env.DEMO_PUBLIC_HOST) {
  throw new Error("CANONICAL_IS_PUBLIC_HOST is true: export DEMO_PUBLIC_HOST (the value set in Railway) before `railway config plan` / `apply`.");
}

export default defineRailway(() => {
  // Pinned: the helper defaults to mysql:9; the demo (compose, CI, DATABASE_URL serverVersion) runs MySQL 8.4.
  const db = mysql("mysql", { image: "mysql:8.4" });

  const mailpit = service("mailpit", {
    source: image("axllent/mailpit:v1.27"),
    replicas: { [REGION]: 1 },
    env: {
      // Listen on the private network (IPv6 and IPv4).
      MP_SMTP_BIND_ADDR: "[::]:1025",
      MP_UI_BIND_ADDR: "[::]:8025",
      MP_MAX_MESSAGES: "500",
    },
  });

  const web = service("web", {
    source: github("thibmonier/sylius-b2b-demo", { branch: "main" }),
    replicas: { [REGION]: 1 },
    // The entrypoint installs the database on the first start (fixtures): leave it time.
    preDeploy: "php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration",
    healthcheck: "/healthz",
    healthcheckTimeout: 600,
    env: {
      RAILWAY_DOCKERFILE_PATH: "docker/Dockerfile",
      APP_SECRET: preserve(),
      DATABASE_URL: "${{mysql.MYSQL_URL}}?serverVersion=8.4&charset=utf8mb4",
      DEFAULT_URI: CANONICAL_IS_PUBLIC_HOST ? "https://${{DEMO_PUBLIC_HOST}}" : "https://${{RAILWAY_PUBLIC_DOMAIN}}",
      DEMO_MODE: "1",
      // Private gate until the user's GO (US-032): credentials are Railway secrets, the hash is Argon2id.
      DEMO_BASIC_AUTH: OPEN_TO_PUBLIC ? "0" : "1",
      DEMO_REGISTRATION_OPEN: OPEN_TO_PUBLIC ? "0" : "1",
      DEMO_PUNCHOUT_PUBLIC_ENDPOINT: "0",
      DEMO_BASIC_AUTH_USER: preserve(),
      DEMO_BASIC_AUTH_PASSWORD_HASH: preserve(),
      // The Railway healthcheck calls with Host healthcheck.railway.app.
      TRUSTED_HOSTS: "^(${{RAILWAY_PUBLIC_DOMAIN}}|healthcheck\\.railway\\.app)$",
      // US-066: custom host (subdomain of the owner's site), set by the owner in Railway (`railway variables --set`),
      // never written here. Accepted next to the Railway domain; empty or absent: the Railway domain alone.
      DEMO_PUBLIC_HOST: preserve(),
      MAILER_DSN: "smtp://${{mailpit.RAILWAY_PRIVATE_DOMAIN}}:1025",
      DEMO_MAILPIT_API_URL: "http://${{mailpit.RAILWAY_PRIVATE_DOMAIN}}:8025",
      DEMO_MAILPIT_URL: "",
    },
  });

  return project("sylius-b2b-demo", {
    resources: [db, mailpit, web],
  });
});
