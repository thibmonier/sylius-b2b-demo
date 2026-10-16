// Railway infrastructure of the demo (US-032, ADR-0002), applied with `railway config plan` / `apply`.
// Secrets are never written here: they are set in Railway (`railway variables --set`) and kept by preserve().
// Mailpit has no public domain: the web service reaches it on the private network only (US-028).
import { defineRailway, github, image, mysql, preserve, project, service } from "railway/iac";

const REGION = "europe-west4";

export default defineRailway(() => {
  const db = mysql("mysql");

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
      DEFAULT_URI: "https://${{RAILWAY_PUBLIC_DOMAIN}}",
      DEMO_MODE: "1",
      // Private gate until the user's GO (US-032): credentials are Railway secrets, the hash is Argon2id.
      DEMO_BASIC_AUTH: "1",
      DEMO_BASIC_AUTH_USER: preserve(),
      DEMO_BASIC_AUTH_PASSWORD_HASH: preserve(),
      // The Railway healthcheck calls with Host healthcheck.railway.app.
      TRUSTED_HOSTS: "^(${{RAILWAY_PUBLIC_DOMAIN}}|healthcheck\\.railway\\.app)$",
      MAILER_DSN: "smtp://${{mailpit.RAILWAY_PRIVATE_DOMAIN}}:1025",
      DEMO_MAILPIT_API_URL: "http://${{mailpit.RAILWAY_PRIVATE_DOMAIN}}:8025",
      DEMO_MAILPIT_URL: "",
    },
  });

  return project("sylius-b2b-demo", {
    resources: [db, mailpit, web],
  });
});
