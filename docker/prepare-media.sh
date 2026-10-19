#!/bin/sh
# Prepares the media directory, which is a Railway volume in production (US-145).
# A fresh volume is empty and owned by root: create the sub-directories and hand them to www-data.
# Fails loudly when the directory cannot be written, instead of leaving broken product images behind.
set -eu

media="${1:?usage: prepare-media.sh <media-directory>}"

fail() {
    echo "The media directory $media is not writable by user $(id -un): check the volume mount and its owner." >&2
    exit 1
}

mkdir -p "$media/image" "$media/cache" 2>/dev/null || fail

# The directories just created by root (or a volume mounted with another owner) belong to root: hand them over.
# Recursive only when needed, so a large image cache is not walked at every start.
if [ "$(id -u)" = "0" ]; then
    for directory in "$media" "$media/image" "$media/cache"; do
        if [ "$(stat -c %U "$directory")" != "www-data" ]; then
            chown -R www-data:www-data "$directory" || fail
        fi
    done
fi

if [ ! -w "$media" ] || [ ! -w "$media/image" ] || [ ! -w "$media/cache" ]; then
    fail
fi
