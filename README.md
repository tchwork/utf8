Patchwork UTF-8
===============

The `Patchwork\Utf8` class implements the quasi complete set of string functions
that need UTF-8 grapheme clusters awareness:

strlen, substr, strpos, stripos, strrpos, strripos, strstr, stristr, strrchr,
strrichr, strtolower, strtoupper, htmlentities, htmlspecialchars, wordwrap, chr,
count_chars, ltrim, ord, rtrim, trim, html_entity_decode,
get_html_translation_table, str_ireplace, str_pad, str_shuffle, str_split,
str_word_count, strcmp, strnatcmp, strcasecmp, strnatcasecmp, strncasecmp,
strncmp, strcspn, strpbrk, strrev, strspn, strtr, substr_compare, substr_count,
substr_replace, ucfirst, lcfirst, ucwords.
Missing are printf-family functions and number_format.

Some more functions are also provided to help handling UTF-8 strings:

- isUtf8: checks if a string contains well formed UTF-8
- toASCII: generic UTF-8 to ASCII transliteration
- bestFit: UTF-8 to Code Page conversion using best fit mappings
- strtocasefold: unicode transformation for caseless matching
- strtonatfold: generic case sensitive transformation for collation matching
- getGraphemeClusters: splits a string to an array of grapheme clusters

These functions are all static methods of the `Patchwork\Utf8` class. The best
way to use them is to add a `use Patchwork\Utf8 as u;` at the beginning of your
files, then when UTF-8 awareness is required, prefix by `u::` when calling them:
`echo strlen("déjà");` may become `echo u::strlen("déjà");` eg.

Portability
-----------

`Patchwork\Utf8` relies on the `mbstring`, `iconv` and `intl` PHP extensions.

When one or all of these extensions are missing, partial PHP fallback
implementations are provided:

- `mbstring`: mb_convert_encoding, mb_decode_mimeheader, mb_encode_mimeheader,
  mb_convert_case, mb_internal_encoding, mb_list_encodings, mb_strlen,
  mb_strpos, mb_strrpos, mb_strtolower, mb_strtoupper, mb_substitute_character,
  mb_substr, mb_stripos, mb_stristr, mb_strrchr, mb_strrichr, mb_strripos,
  mb_strstr.
- `iconv`: iconv, iconv_mime_decode, iconv_mime_decode_headers,
  iconv_get_encoding, iconv_set_encoding, iconv_mime_encode, ob_iconv_handler,
  iconv_strlen, iconv_strpos, iconv_strrpos, iconv_substr.
- `intl`: Normalizer, grapheme_extract, grapheme_stripos, grapheme_stristr,
  grapheme_strlen, grapheme_strpos, grapheme_strripos, grapheme_strrpos,
  grapheme_strstr, grapheme_substr.

No bootstrapper is currently provided to enable these fallbacks when required.
This is left as an exercise for the reader :)
