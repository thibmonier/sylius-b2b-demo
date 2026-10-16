<?php

declare(strict_types=1);

namespace App\Demo\Mail;

/**
 * Decides whether a mail must stay out of the public demo mailbox (US-028).
 *
 * A mail is sensitive (hence hidden and refused by id) when ANY of these holds:
 *  1. its subject names a password, account verification/activation, an e-mail/account confirmation,
 *     an invitation, a sign-in link/code or a token (FR and EN, accent-insensitive);
 *  2. its text or HTML contains a URL that carries a secret: a query parameter named like a
 *     token/hash/signature/code/key, a path segment named like an action (reset, verify, confirm,
 *     activate, invite, token, unsubscribe) followed by more path, or an opaque path segment
 *     (>= 20 chars of mixed upper/lower case and digits, or >= 24 hex chars).
 * The rule is a deny-list by design: a doubt hides the mail.
 */
final class SensitiveMailFilter
{
    private const SUBJECT_PATTERN = '/(password|passwd|mot de passe|mdp|reset|reinitialis|verif|activat|invit|token|jeton|magic|sign.?in|log.?in|connexion|one.?time|otp|secret|credential|identifiant|confirm\\w* (?:your |votre |de votre |ton )?(?:e-?mail|account|compte|adresse|inscription|registration)|security code|code de securite)/u';

    private const SECRET_PARAM_PATTERN = '/[?&;](?:amp;)?(?:[a-z_]*(?:token|hash|signature|sig|code|key|secret|otp|auth|nonce)[a-z_]*|t|k|h)=/i';

    private const ACTION_PATH_PATTERN = '~/(?:reset|verify|verification|confirm|confirmation|activate|activation|invite|invitation|token|unsubscribe|magic)[a-z_-]*/[^\s/?#]+~i';

    private const URL_PATTERN = '#(?:https?://[^\s"\'<>)]+|(?<=["\'])/[^\s"\'<>)]+)#i';

    public function isSensitive(MailpitMessage $message): bool
    {
        return $this->hasSensitiveSubject($message->subject) ||
            $this->hasSecretUrl($message->text . "\n" . $message->html);
    }

    private function hasSensitiveSubject(string $subject): bool
    {
        $normalized = mb_strtolower(self::stripAccents($subject));

        return 1 === preg_match(self::SUBJECT_PATTERN, $normalized);
    }

    private function hasSecretUrl(string $body): bool
    {
        $body = html_entity_decode($body, \ENT_QUOTES | \ENT_HTML5, 'UTF-8');
        preg_match_all(self::URL_PATTERN, $body, $matches);

        foreach ($matches[0] as $url) {
            if (1 === preg_match(self::SECRET_PARAM_PATTERN, $url) ||
                1 === preg_match(self::ACTION_PATH_PATTERN, $url) ||
                $this->hasOpaqueSegment($url)) {
                return true;
            }
        }

        return false;
    }

    private function hasOpaqueSegment(string $url): bool
    {
        $path = (string) parse_url($url, \PHP_URL_PATH);
        foreach (explode('/', $path) as $segment) {
            if (1 === preg_match('/^[A-Fa-f0-9]{24,}$/', $segment) || $this->isMixedCaseToken($segment)) {
                return true;
            }
        }

        return false;
    }

    private function isMixedCaseToken(string $segment): bool
    {
        return 1 === preg_match('/^[A-Za-z0-9_-]{20,}$/', $segment) &&
            1 === preg_match('/[A-Z]/', $segment) &&
            1 === preg_match('/[a-z]/', $segment) &&
            1 === preg_match('/\d/', $segment);
    }

    private static function stripAccents(string $value): string
    {
        $converted = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')?->transliterate($value);

        return \is_string($converted) ? $converted : $value;
    }
}
